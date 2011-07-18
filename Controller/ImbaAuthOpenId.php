<?php

/**
 * Description of ImbaAuthPassword
 *
 * @author oxi
 */
class ImbaAuthOpenId extends ImbaAuthBase {

    /**
     * Ctor
     */
    public function __construct() {
        parent::__construct();
    }

    public function process() {
        exit();
        //check if we need to verify
        if (empty($_SESSION["IUC_WaitingForVerify"])) {
            // double check the existence of the openid
            if (!empty($_REQUEST["openid"])) {
                $_REQUEST["openid"] = trim($_REQUEST["openid"]);
                $redirectUrl = null;

                // Get all users
                $allUsers = $managerUser->selectAllUser();

                // Check if this is a openid (which looks like a URL) or possibly the nickname of the user
                if (ImbaSharedFunctions::isValidURL($_REQUEST["openid"])) {
                    $openid = $_REQUEST["openid"];
                } else {
                    // Try to lookup the nickname and only take a unique user
                    $securityCounter = 0;
                    $tmpOpenid = null;
                    foreach ($allUsers as $user) {
                        if (strtolower($user->getNickname()) == strtolower($_REQUEST["openid"])) {
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

                // Check and if sucessful, log the user in
                checkAndLogin($currentUser, $esc_identity);

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

        return false;
    }

    private function login() {
        return false;
    }

}

?>
