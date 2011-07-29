<?php

require_once 'ImbaConstants.php';
require_once 'Shared/Include.php';
require_once 'Model/Include.php';
require_once 'Controller/Include.php';

header('Access-Control-Allow-Origin: *');

session_set_cookie_params(3600);
session_start();

$smarty = ImbaSharedFunctions::newSmarty();
$smarty->assign("thrustRoot", ImbaSharedFunctions::getTrustRoot());
$smarty->assign("phpSessionID", session_id());

switch ($_GET["load"]) {
    case "js":
        header('Content-Type: application/javascript');
        ImbaUserContext::setRedirectUrl(ImbaSharedFunctions::getTrustRoot());

        $managerNavigation = ImbaManagerNavigation::getInstance();

        $useProxy = false;
        if (ImbaConstants::$WEB_FORCE_PROXY == 1) {
            $useProxy = true;
        } else if (ImbaConstants::$WEB_FORCE_PROXY == 0) {
            if (($_SERVER['HTTP_REFERER'] != ImbaSharedFunctions::getTrustRoot())) {
                //echo "/* " . $_SERVER['HTTP_REFERER'] . " -- " . ImbaSharedFunctions::getTrustRoot() . " */";
                $useProxy = true;
            }
        }

        if (!$useProxy) {
            $smarty->assign("authPath", ImbaConstants::$WEB_AUTH_MAIN_PATH);
            $smarty->assign("ajaxPath", ImbaSharedFunctions::fixWebPath(ImbaConstants::$WEB_AJAX_MAIN_PATH));
        } else {
            $smarty->assign("authPath", ImbaConstants::$WEB_AUTH_PROXY_PATH);
            $smarty->assign("ajaxPath", ImbaSharedFunctions::fixWebPath(ImbaConstants::$WEB_AJAX_PROXY_PATH));
        }
        /**
         * Use no proxy for auth ("magic")
         */
        $smarty->assign("authPath", ImbaConstants::$WEB_AUTH_MAIN_PATH);

        // This is now all part of loadImbaPortal() in ImbaLogin.js
        //$smarty->assign("PortalNavigation", $managerNavigation->displayLoaderPortalNavigation());
        //$smarty->assign("ImbaAdminNavigation", $managerNavigation->renderImbaAdminNavigation());
        //$smarty->assign("ImbaGameNavigation", $managerNavigation->renderImbaGameNavigation());
        //$smarty->assign("PortalChooser", $managerNavigation->renderPortalChooser());

        /**
         * Set Auth referer for automatic redirection
         */
        $tmpAuthReferer = ImbaUserContext::getAuthReferer();
        ImbaUserContext::setAuthReferer("");
        $smarty->assign("imbaAuthReferer", $tmpAuthReferer);

        /**
         * Show Session Error Message
         */
        $tmpImbaErrorMsgs = ImbaUserContext::getImbaErrorMessage();
        ImbaUserContext::setImbaErrorMessage("");
        $smarty->assign("imbaErrorMessage", $tmpImbaErrorMsgs);

        /**
         * Set Savascript Debug
         */
        ImbaConstants::loadSettings();
        if (!empty($_SESSION["IUC_Debug"])) {
            $tmpJsDebug = ImbaUserContext::getDebug();
        } elseif (empty(ImbaConstants::$SETTINGS['ENABLE_JS_DEBUG'])) {
            $tmpJsDebug = "false";
        } else {
            $tmpJsDebug = ImbaConstants::$SETTINGS['ENABLE_JS_DEBUG'];
        }
        $smarty->assign("jsDebug", $tmpJsDebug);
        $smarty->assign("imbaModuleName", $_REQUEST["ImbaModuleName"]);
        $smarty->assign("imbaModuleFunction", $_REQUEST["ImbaModuleFunction"]);

        $smarty->display('ImbaLoaderJs.tpl');
        break;

    case "css":
        header('Content-type: text/css');
        $smarty->display('ImbaLoaderCss.tpl');
        break;

    default:
        header('Content-type: text/html');
        $smarty->display('ImbaLoader.tpl');
}
?>