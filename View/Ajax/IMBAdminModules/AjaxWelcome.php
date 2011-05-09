<?php

/**
 * Handling the ajax Callbacks for the ImbaAdmin module Welcome
 */
class AjaxWelcome extends AjaxBase {

    public function __construct() {
        parent::__construct();
    }

    public function getContentManager() {
        /**
         * Define Navigation
         */
        $navigation = new ImbaContentManager();

        /**
         * Set module name
         */
        $navigation->setName("Willkommen");
        $navigation->setClassname(get_class($this));
        $navigation->setComment("Dies ist die Willkommens Site.");

        /**
         * Set when the module should be displayed (logged in 1/0)
         */
        $navigation->setShowLoggedIn(false);
        $navigation->setShowLoggedOff(false);

        /**
         * Set the minimal user role needed to display the module
         */
        $navigation->setMinUserRole(1);

        /**
         * Set tabs
         */
        $navigation->addElement("viewWelcome", "&Uuml;bersicht der Module", "Hier siehst du eine einfache &Uml;bersicht der Module.");
        $navigation->addElement("viewWelcomeIndexed", "Indexierte &Uuml;bersicht", "Hier siehst du eine komplette &Uml;bersicht der Module.");
        return $navigation;
    }

    public function viewWelcome() {
        $myself = $this->managerUser->selectMyself();
        $allUsers = $this->managerUser->selectAllUser();
        $this->smarty->assign('nickname', $myself->getNickname());
        $this->smarty->assign("today", date("d") . "." . date("m") . " " . date("Y"));
        $this->smarty->assign("thrustRoot", urlencode(ImbaSharedFunctions::getSiteDomainUrl()));
        /*
         * ToDo:
         * $events
         * $todo
         * $today
         * $myName
         * 
         */
        /*
         * Fill Navigation $navs
         */

        $this->smarty->assign('nickname', $myself->getNickname());
        $topNavigation = array();
        $navigations = AjaxBase::getModulesNavigation("IMBAdminModules");

        foreach ($navigations as $navigation) {
            if (ImbaUserContext::getUserRole() >= $navigation->getMinUserRole()) {
                $showMe = false;
                if (ImbaUserContext::getLoggedIn() && $navigation->getShowLoggedIn()) {
                    $showMe = true;
                } elseif ((!ImbaUserContext::getLoggedIn()) && $navigation->getShowLoggedOff()) {
                    $showMe = true;
                }

                if ($showMe) {
                    array_push($topNavigation, array(
                        "module" => $navigation->getClassname(),
                        "name" => $navigation->getName(),
                        "comment" => $navigation->getComment()
                    ));
                }
            }
        }
        $this->smarty->assign('navs', $topNavigation);

        /**
         * Fill $birthdays
         */
        $return = "";
        $birthdays = array();
        $todayMagicNumber = (date("n") * 31) + date("j");
        foreach ($allUsers as $user) {
            $magicNumber = ($user->getBirthmonth() * 31) + $user->getBirthday();
            $birthdayStr = $user->getNickname() . ": " . $user->getBirthday() . "." . $user->getBirthmonth() . " (" . (date("Y") - $user->getBirthyear()) . ")<br />";
            if ($magicNumber > 0) {
                $birthdays[$magicNumber] .= $birthdayStr;
            }
        }
        $count = 0;
        ksort($birthdays);
        foreach ($birthdays as $birthday => $string) {
            if ($birthday >= $todayMagicNumber) {
                if ($todayMagicNumber == $birthday) {
                    $return .= "<b>" . $string . "</b>";
                } else {
                    $return .= $string;
                }
                $count++;
                if ($count > 2) {
                    break;
                }
            }
        }
        $this->smarty->assign("birthdays", $return);

        /**
         * Fill $newMembers
         */
        $return = "";
        $newUsers = array();
        foreach ($allUsers as $user) {
            $newUsers[$user->getId()] = $user->getNickname();
        }
        krsort($newUsers);
        $count = 0;
        foreach ($newUsers as $id => $nickName) {
            if ($count > 2) {
                break;
            } else {
                $return .= $nickName . "<br />";
                $count++;
            }
        }
        $this->smarty->assign("newMembers", $return);

        /**
         * Display the site
         */
        $this->smarty->display('IMBAdminModules/WelcomeOverview.tpl');
    }

    /**
     * views the Welcome indexed
     */
    public function viewWelcomeIndexed() {
        $navigations = AjaxBase::getModulesNavigation("IMBAdminModules");
        $topNavigation = array();

        foreach ($navigations as $navigation) {
            if (ImbaUserContext::getUserRole() >= $navigation->getMinUserRole()) {
                $showMe = false;
                if (ImbaUserContext::getLoggedIn() && $navigation->getShowLoggedIn()) {
                    $showMe = true;
                } elseif ((!ImbaUserContext::getLoggedIn()) && $navigation->getShowLoggedOff()) {
                    $showMe = true;
                }

                if ($showMe) {
                    $subNavigation = array();
                    foreach ($navigation->getOptions() as $subnav) {

                        array_push($subNavigation, array(
                            "module" => $navigation->getClassname(),
                            "ajaxmethod" => $subnav->getIdentifier(),
                            "name" => $subnav->getName(),
                            "comment" => $subnav->getComment()
                        ));
                    }

                    array_push($topNavigation, array(
                        "module" => $navigation->getClassname(),
                        "name" => $navigation->getName(),
                        "comment" => $navigation->getComment(),
                        "subnavs" => $subNavigation
                    ));
                }
            }
        }

        $this->smarty->assign('topnavs', $topNavigation);
        $this->smarty->display('IMBAdminModules/WelcomeIndex.tpl');
    }

}

?>
