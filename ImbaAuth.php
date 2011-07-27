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

// Logout has highest prio
if ($_REQUEST["logout"] == true) {
    ImbaAuthBase::logout();
} elseif (!empty($_REQUEST["unlock"])) {
    /*
    $this->manageruser

    if ($_REQUEST["key"]) {
        
    }
    */
    $msg = "Account unlock with key: " . $_REQUEST["key"];
    $msg2 = $this->writeAuthLog($msg);
    $this->redirectTo(__LINE__, ImbaSharedFunctions::getTrustRoot(), $msg2);
    exit();
} elseif (!ImbaUserContext::getLoggedIn()) {
    // We are NOT logged in
    // Save our referer to session if there is none safed till now
    if ($_REQUEST['imbaSsoOpenIdLoginReferer'] != "") {
        ImbaUserContext::setRedirectUrl($_REQUEST['imbaSsoOpenIdLoginReferer']);
    } else {
        if (ImbaUserContext::getRedirectUrl() == "") {
            ImbaUserContext::setRedirectUrl($_SERVER['HTTP_REFERER']);
        }
    }

    // Determine Authentication method
    switch ($_REQUEST["authMethod"]) {
        case "openId":
            // OpenID Authentification
            $this->redirectTo(__LINE__, $tmpUrl, $this->writeAuthLog("OpenId not supported at the moment!"));
            break;

        case "password":
            // Password Authentification
            $tmp = new ImbaAuthPassword();
            $tmp->process();
            break;

        default:
            // Send the User to the registration page
            if (empty($_SERVER['HTTP_REFERER'])) {
                $tmpUrl = ImbaSharedFunctions::getTrustRoot();
            } else {
                $tmpUrl = $_SERVER['HTTP_REFERER'];
            }
            $this->redirectTo(__LINE__, $tmpUrl, $this->writeAuthLog("Authentificationmethod not found"));
            exit;
    }

    $this->redirectTo(__LINE__, ImbaUserContext::getRedirectUrl(), $tmpMsg);
    exit;
} else {
    /**
     * we are already logged in! everithing is ok, we have a
     * running session and we are going to have a party here!
     * - set cookie with logged in openid for autofill login box
     * - redirect back to page
     */
    ImbaUserContext::setWaitingForVerify("");
    $this->writeAuthLog("Already logged in with id: " . ImbaUserContext::getUserId() . ")", 1);
    $this->redirectTo(__LINE__, ImbaUserContext::getWaitingForVerify(), ImbaUserContext::setWaitingForVerify(""));
    exit;
}

$this->redirectTo(__LINE__, ImbaUserContext::getRedirectUrl(), "We should never have gone so far...");
exit;
?>