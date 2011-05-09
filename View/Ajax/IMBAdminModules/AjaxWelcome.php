<?php

/**
 * Handling the ajax Callbacks for the ImbaAdmin module Welcome
 */
class AjaxWelcome extends AjaxBase {

    public function __construct() {
        parent::__construct();
    }

    public function getNavigation() {
        /**
         * Define Navigation
         */
        $navigation = new ImbaContentNavigation();

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
