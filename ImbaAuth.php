<?php

// Load Dependencies
require_once 'ImbaConstants.php';
require_once 'Shared/Include.php';
require_once 'Model/Include.php';
require_once 'Controller/Include.php';

header('Access-Control-Allow-Origin: *');

// Start the php session
session_set_cookie_params(3600);
session_start();

// Load Auth Manager
$managerOpenId = new ImbaManagerOpenID();

// Initiate the manager
$managerUser = ImbaManagerUser::getInstance();
$managerAuthRequest = ImbaManagerAuthRequest::getInstance();

/**
 * Private function to write a log
 */
function writeAuthLog($message, $level = 3) {
    // Load the Log manager
    $managerLog = ImbaManagerLog::getInstance();

    $log = $managerLog->getNew();
    $log->setModule("ImbaAuth");
    $log->setMessage($message);
    $log->setLevel($level);
    $managerLog->insert($log);
    ImbaUserContext::setImbaErrorMessage($message);
    return $message;
}

/**
 * Kill our session and go to URL, function
 */
function killAndRedirect($targetUrl) {
    setcookie(session_id(), "", time() - 3600);
    session_destroy();
    session_write_close();
    header("Location: " . $targetUrl);
}

/**
 * Redirect with domain magic
 */
function redirectTo($line, $url, $message = "") {
    $myDomain = ImbaSharedFunctions::getDomain($url);

    // Discover if we need to do the html redirect and make it so
    if (headers_sent() || true) {
        $smarty = ImbaSharedFunctions::newSmarty();

        $smarty->assign("redirectUrl", rawurldecode($url));
        $smarty->assign("redirectDomain", $myDomain);
        $smarty->assign("internalCode", $line);
        $smarty->assign("internalMessage", $message);
        $smarty->assign("phpsession", session_id());
        $smarty->display("ImbaAuthRedirect.tpl");
        exit;
    } else {
        header("Location: " . $url);
        exit;
    }
}

// OpenID auth logic
if ($_GET["logout"] == true || $_POST["logout"] == true) {
    // We want to log out
    writeAuthLog("Logging out (Redirecting)", 2);

    if (empty($_POST['imbaSsoOpenIdLogoutReferer'])) {
        $targetUrl = ImbaSharedFunctions::getTrustRoot();
    } else {
        $targetUrl = $_POST['imbaSsoOpenIdLogoutReferer'];
    }

    killAndRedirect($targetUrl);
    exit;
} elseif (!ImbaUserContext::getLoggedIn()) {
    // We are NOT logged in
    if (empty($_SESSION["IUC_WaitingForVerify"])) {
        // Save our referer to session if there is none safed till now
        if ($_POST['imbaSsoOpenIdLoginReferer'] != "") {
            ImbaUserContext::setRedirectUrl($_POST['imbaSsoOpenIdLoginReferer']);
        } else {
            if ($_SESSION["IUC_redirectUrl"] == "") {
                ImbaUserContext::setRedirectUrl($_SERVER['HTTP_REFERER']);
            }
        }

        // Determine Authentication method (we also don't have to be verified)
        if (!(empty($_POST['openid']) && (empty($_GET['openid'])))) {
            // OpenID Authentification
            $authMethod = "openid";
        } else {
            // Send the User to the registration page
            if (empty($_SERVER['HTTP_REFERER'])) {
                $tmpUrl = ImbaSharedFunctions::getTrustRoot();
            } else {
                $tmpUrl = $_SERVER['HTTP_REFERER'];
            }
            $tmpMsg = writeAuthLog("Authentificationmethod not found");
            redirectTo(__LINE__, $tmpUrl, $tmpMsg);
            exit;
        }

        // Do the Authentication
        // -> determine the authentification method
        switch ($authMethod) {
            case "openid":
                // We got the openid via get? => write it into post
                if (empty($_POST["openid"]) && (!empty($_GET["openid"]))) {
                    $_POST["openid"] = $_GET["openid"];
                }

                // double check the existence of the openid
                if (!empty($_POST["openid"])) {
                    $_POST["openid"] = trim($_POST["openid"]);
                    $redirectUrl = null;

                    // Get all users
                    $allUsers = $managerUser->selectAllUser();

                    // Check if this is a openid (which looks like a URL) or possibly the nickname of the user
                    if (ImbaSharedFunctions::isValidURL($_POST["openid"])) {
                        $openid = $_POST["openid"];
                    } else {
                        // Try to lookup the nickname and only take a unique user
                        $securityCounter = 0;
                        $tmpOpenid = null;
                        foreach ($allUsers as $user) {
                            if (strtolower($user->getNickname()) == strtolower($_POST["openid"])) {
                                $securityCounter++;
                                $tmpOpenid = $user->getOpenId();
                            }
                        }

                        if (($securityCounter == 1) && (!empty($tmpOpenid))) {
                            $openid = $tmpOpenid;
                        } else {
                            throw new Exception(ImbaConstants::$ERROR_OPENID_Auth_OpenID_INVALID_URI);
                        }
                    }

                    // Select our user
                    $myUser = $managerUser->selectByOpenId($openid);

                    // Creating our authrequest into the database
                    $authRequest = $managerAuthRequest->getNew();
                    $authRequest->setUserId($myUser->getId());
                    $authRequest->setHash(ImbaSharedFunctions::getRandomString());
                    $authRequest->setRealm(ImbaSharedFunctions::getTrustRoot());
                    $authRequest->setReturnTo(ImbaSharedFunctions::getReturnTo($authRequest->getHash()));
                    $authRequest->setType($authMethod);
                    $authRequest->setDomain($_POST['imbaSsoOpenIdLoginReferer']);
                    $authRequest->setPhpsession(session_id());
                    $authRequest->setIp(ImbaSharedFunctions::getIP());
                    $managerAuthRequest->insert($authRequest);

                    // Try to do the first step of the openid authentication steps
                    writeAuthLog("Determing Auth style for #" . $openid . "#");

                    try {
                        $redirectUrl = $managerOpenId->openidAuth($openid, $authRequest->getHash(), $authRequest->getRealm(), $authRequest->getReturnTo());

                        if (!empty($redirectUrl)) {
                            // we got a redirection url as answer. go there now!
                            $tmpMsg = writeAuthLog("OpenIdAuth Redirection, Domain: #" . ImbaSharedFunctions::getDomain($redirectUrl) . "# URL: #" . $redirectUrl . "#");

                            // If this is set, the user will be sent to verification next time
                            ImbaUserContext::setWaitingForVerify(ImbaUserContext::getRedirectUrl());

                            redirectTo(__LINE__, $redirectUrl, $tmpMsg);
                            exit;
                        } else {
                            // something went wrong. display error end exit
                            $tmpMsg = writeAuthLog("Special Error: Ehhrmm keine URL, weil ehhrmm", 0);
                            redirectTo(__LINE__, ImbaUserContext::getRedirectUrl(), $tmpMsg);
                            exit;
                        }
                    } catch (Exception $ex) {
                        $tmpMsg = writeAuthLog("Authentification ERROR: " . $ex->getMessage() . " (" . $openid . ")", 1);
                        redirectTo(__LINE__, ImbaUserContext::getRedirectUrl(), $tmpMsg);
                        exit;
                    }
                } else {
                    $tmpMsg = writeAuthLog("No OpenId submitted", 2);
                    redirectTo(__LINE__, ImbaUserContext::getRedirectUrl(), $tmpMsg);
                    exit;
                }
                break;

            default:
                $tmpMsg = writeAuthLog("No Authtype submitted");
                true;
        }

        redirectTo(__LINE__, $_SERVER['HTTP_REFERER'], $tmpMsg);
        exit;
    } else {
        /**
         * First step is completed. Do the verification and actual login,
         * we shall go to the saved realm in the database after
         * we are finished here.
         */
        // Convert imbaHash from possible GET and POST to local var (proxy...)
        if (!empty($_GET['imbaHash'])) {
            $imbaHash = $_GET['imbaHash'];
            unset($_GET['imbaHash']);
        } else if (!empty($_POST['imbaHash'])) {
            $imbaHash = $_POST['imbaHash'];
            unset($_POST['imbaHash']);
        } else {
            // We have no imbaHash, this is not good! kill yourself and go back where you came from
            $tmpMsg = writeAuthLog("Morpheus, help! Forwarding to: " . ImbaSharedFunctions::getTrustRoot());
            ImbaUserContext::setWaitingForVerify("");
            redirectTo(__LINE__, ImbaSharedFunctions::getTrustRoot(), $tmpMsg);
            exit;
        }

        // Get the stored data for the current authrequest from the database
        $authRequest = $managerAuthRequest->select($imbaHash);

        writeAuthLog("Verification starting", 2);

        // Remove  everything after &imbaHash in the return URL
        if (strpos($authRequest->getReturnTo(), "&imbaHash")) {
            $authRequest->setReturnTo(substr($authRequest->getReturnTo(), 0, strpos($authRequest->getReturnTo(), "&imbaHash")));
        }

        // Do the second Step
        try {
            $esc_identity = $managerOpenId->openidVerify($authRequest->getRealm(), $authRequest->getReturnTo());

            // If we got no openid, something went wrong with auth
            if ($esc_identity === false) {
                throw new Exception("OpenId verification failed! No OpenId recieved from the OpenId Manager. Realm:" . $authRequest->getRealm() . " Ret:" . $authRequest->getReturnTo());
            }
            writeAuthLog("OpenID Verification sucessful", 2);

            // Select our user
            $currentUser = $managerUser->selectByOpenId($esc_identity);

            // Check the status of the user
            if (empty($currentUser)) {
                // This is a new user. let him register
                writeAuthLog("Registering new user", 2);

                if (!empty($esc_identity)) {
                    ImbaUserContext::setNeedToRegister(true);
                    ImbaUserContext::setOpenIdUrl($esc_identity);
                }
            } elseif ($currentUser->getRole() == 0) {
                // This user is banned
                writeAuthLog($currentUser->getName() . " is banned but tried to login", 1);
                throw new Exception("You are Banned!");
            } elseif ($currentUser->getRole() != null) {
                // This user is allowed to log in
                $tmpMsg = writeAuthLog($currentUser->getNickname() . " logged in", 1);

                // Setup all login stuff
                ImbaUserContext::setLoggedIn(true);
                ImbaUserContext::setOpenIdUrl($esc_identity);
                ImbaUserContext::setUserRole($currentUser->getRole());
                ImbaUserContext::setUserId($currentUser->getId());

                setcookie("ImbaSsoLastLoginName", "", (time() - 3600));
                setcookie("ImbaSsoLastLoginName", $currentUser->getNickname(), (time() + (60 * 60 * 24 * 30)));


                $managerUser->setMeOnline();
                ImbaUserContext::setImbaErrorMessage("Du bist angemeldet als " . $currentUser->getNickname());
            }

            // redirect to where we come from
            $myDomain = $authRequest->getDomain();
            if (!empty($myDomain)) {
                $managerAuthRequest->delete($imbaHash);
                redirectTo(__LINE__, $myDomain, $tmpMsg);
                exit;
            } else {
                $tmpUrl = ImbaUserContext::getWaitingForVerify();
                ImbaUserContext::setWaitingForVerify("");
                redirectTo(__LINE__, $tmpUrl, $tmpMsg);
                exit;
            }
        } catch (Exception $ex) {
            $esc_identity = $managerOpenId->getOpenId();

            $tmpUrl = ImbaUserContext::getWaitingForVerify();
            ImbaUserContext::setWaitingForVerify("");

            if ($ex->getMessage() == "id_res_not_set") {
                $tmpMsg = writeAuthLog("Aktuelle OpenID Anfrage ausgelaufen. Bitte nocheinmal von neuen probieren. (Hash: " . $imbaHash . ")");
            } else {
                $tmpMsg = writeAuthLog("Unnamed OpenID Verification ERROR (Hash: " . $imbaHash . "): " . $ex->getMessage(), 1);
            }
            $myDomain = $authRequest->getDomain();
            if (!empty($myDomain)) {
                //$managerAuthRequest->delete($imbaHash);
                redirectTo(__LINE__, $myDomain, $tmpMsg);
                exit;
            } else {
                $tmpUrl = ImbaUserContext::getWaitingForVerify();
                ImbaUserContext::setWaitingForVerify("");
                //$managerAuthRequest->delete($imbaHash);
                redirectTo(__LINE__, $tmpUrl, $tmpMsg);
                exit;
            }
        }
    }
    exit;
} else {
    ImbaUserContext::setWaitingForVerify("");
    /**
     * we are logged in! everithing is ok, we have a running session
     * and we have a party here
     * - set cookie with logged in openid for autofill login box
     * - redirect back to page
     */
    writeAuthLog("Already logged in with: " . ImbaUserContext::getOpenIdUrl() . ")", 1);
    $tmpUrl = ImbaUserContext::getWaitingForVerify();
    $tmpMsg = ImbaUserContext::setWaitingForVerify("");
    redirectTo(__LINE__, $tmpUrl, $tmpMsg);
    exit;
}

redirectTo(__LINE__, ImbaUserContext::getRedirectUrl(), "We should never have gone so far...");
exit;
?>