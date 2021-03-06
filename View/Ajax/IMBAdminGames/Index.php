<?php

session_start();

require_once 'Model/ImbaUser.php';
require_once 'ImbaConstants.php';
require_once 'Controller/ImbaManagerUser.php';
require_once 'Controller/ImbaManagerUserRole.php';
require_once 'Shared/ImbaUserContext.php';
require_once 'Shared/ImbaSharedFunctions.php';

/**
 * are we logged in?
 */
if (ImbaUserContext::getLoggedIn()) {
    /**
     * create a new smarty object
     */
    $smarty = ImbaSharedFunctions::newSmarty();

    /**
     * Load the database
     */
    $managerUser = ImbaManagerUser::getInstance();


    $contentNav = new ImbaContentManager();

    switch ($_POST["request"]) {

        case "index":
            $navOptions = array();
            if ($handle = opendir('Ajax/IMBAdminGames/')) {
                $identifiers = array();
                while (false !== ($file = readdir($handle))) {
                    if (strrpos($file, ".Navigation.php") > 0) {
                        include 'Ajax/IMBAdminGames/' . $file;
                        if (ImbaUserContext::getUserRole() >= $Navigation->getMinUserRole()) {
                            $showMe = false;
                            if (ImbaUserContext::getLoggedIn() && $Navigation->getShowLoggedIn()) {
                                $showMe = true;
                            } elseif ((!ImbaUserContext::getLoggedIn()) && $Navigation->getShowLoggedOff()) {
                                $showMe = true;
                            }

                            if ($showMe) {
                                $modIdentifier = trim(str_replace(".Navigation.php", "", $file));

                                $modNavs = array();
                                foreach ($Navigation->getElements() as $element) {
                                    //print_r($element);
                                    array_push($modNavs, array(
                                        "game" => $modIdentifier,
                                        "identifier" => $element,
                                        "name" => $Navigation->getElementName($element),
                                        "comment" => $Navigation->getElementComment($element),
                                    ));
                                }

                                array_push($navOptions, array(
                                    "identifier" => $modIdentifier,
                                    "name" => $Navigation->getName($nav),
                                    "comment" => $Navigation->getComment($nav),
                                    "options" => $modNavs
                                ));
                                $Navigation = null;
                            }
                        }
                    }
                }
                closedir($handle);
            }
            $smarty->assign('topnavs', $navOptions);
            $smarty->display('IMBAdminGames/WelcomeIndex.tpl');
            break;

        default:
            $myself = $managerUser->selectMyself();
            $smarty->assign('nickname', $myself->getNickname());
            $navOptions = array();
            if ($handle = opendir('Ajax/IMBAdminGames/')) {
                $identifiers = array();
                while (false !== ($file = readdir($handle))) {
                    if (strrpos($file, ".Navigation.php") > 0) {
                        include 'Ajax/IMBAdminGames/' . $file;
                        if (ImbaUserContext::getUserRole() >= $Navigation->getMinUserRole()) {
                            $showMe = false;
                            if (ImbaUserContext::getLoggedIn() && $Navigation->getShowLoggedIn()) {
                                $showMe = true;
                            } elseif ((!ImbaUserContext::getLoggedIn()) && $Navigation->getShowLoggedOff()) {
                                $showMe = true;
                            }

                            if ($showMe) {
                                $modIdentifier = trim(str_replace(".Navigation.php", "", $file));
                                array_push($navOptions, array("identifier" => $modIdentifier,
                                    "name" => $Navigation->getName($nav),
                                    "comment" => $Navigation->getComment($nav)
                                ));
                                $Navigation = null;
                            }
                        }
                    }
                }
                closedir($handle);
            }
            $smarty->assign('navs', $navOptions);
            $smarty->display('IMBAdminGames/WelcomeOverview.tpl');
            break;
    }
} else {
    echo "Not logged in";
}
?>
