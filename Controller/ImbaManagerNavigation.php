<?php

/**
 *  Controller / Manager for Top Navigation
 *  - insert, update, delete navigation entries
 *
 */
class ImbaManagerNavigation extends ImbaManagerBase {

    /**
     * our portal context
     */
    private $loadPortalContext = null;
    private $managerPortal = null;

    /**
     * Ctor
     */
    public function __construct() {
        parent::__construct();

        $this->loadPortalContext = ImbaConstants::$SETTINGS['DEFAULT_PORTAL_ID'];
        $this->managerPortal = ImbaManagerPortal::getInstance();

        foreach ($this->managerPortal->selectAll() as $tmpPortal) {
            if (count($tmpPortal->getAliases())) {
                foreach ($tmpPortal->getAliases() as $tmpAlias) {
                    if ($_SERVER[HTTP_HOST] == $tmpAlias) {
                        $this->loadPortalContext = $tmpPortal->getId();
                    }
                }
            }
        }
        if ($this->loadPortalContext == null) {
            $this->loadPortalContext = ImbaUserContext::getPortalContext();
        }
    }

    /*
     * Singleton init
     */

    public static function getInstance() {
        return new ImbaManagerNavigation();
    }

    /**
     * Creates the Navigation for a portal
     */
    public function getNavigationForPortal(ImbaPortal $portal) {
        $return = "<ul id='imbaMenuUl' class='topnav'>";
        $return .= $this->renderPortalNavigation($portal);
        $return .= $this->renderImbaAdminNavigation();
        $return .= $this->renderPortalChooser();
        $return .= "<li><div id='imbaMessagingControl' style='width: 50px;'>" .
                    "<div id='imbaOpenMessaging' class='ui-icon ui-icon-comment' title='Open Messaging' style='float:right; margin-top:6px; margin-right: 3px;'></div>" .
                    "<div id='imbaGotMessage' class='ui-icon ui-icon-mail-closed' title='Open New Messae'style='float:right; margin-top:6px; margin-right: 3px;'></div>" .
                    "</div></li>";
        $return .= "</ul>";
        return $return;
    }

    /**
     * Display Portal Navigation
     */
    protected function displayLoaderPortalNavigation() {
        return "<div id='imbaNavigationPortal'></div>";
    }

    /**
     * Render Portal Navigation
     */
    protected function renderPortalNavigation(ImbaPortal $portal) {
        $return = "";

        if ($portal == null) {
            throw new Exception("Portal not found");
        }

        foreach ($portal->getPortalEntries() as $portalEntry) {
            $showMe = false;
            if (ImbaUserContext::getUserRole() >= $portalEntry->getRole()) {
                $showMe = false;
                if ((ImbaUserContext::getLoggedIn() && $portalEntry->getLoggedin()) || ($portalEntry->getLoggedin() == 0)) {
                    $showMe = true;
                }
            }
            if ($showMe) {
                $return .= "<li><a href='javascript:void(0)' onclick='javascript: location.href=\"" . $portalEntry->getURL() . "\";' title='" . $portalEntry->getComment() . "'>" . $portalEntry->getName() . "</li></a>";
            }
        }
        return $return;
    }

    /**
     * Render the navigation for the IMBAdmin
     */
    protected function renderImbaAdminNavigation() {
        $moduleName = "IMBAdminModules";

        require_once ("View/Ajax/AjaxBase.php");
        $navigations = AjaxBase::getModulesNavigation($moduleName);

        $return = "<li>";
        $return .= "<a id='imbaMenuImbAdmin' href='javascript:void(0)' onclick='javascript: loadImbaAdminDefaultModule();' title='";
        $return .= ImbaConstants::$WEB_IMBADMIN_BUTTON_COMMENT . "'>" . ImbaConstants::$WEB_IMBADMIN_BUTTON_NAME . "</a>";
        $return .= "<ul class='subnav'>";
        $contentNav = new ImbaContentManager();

        foreach ($navigations as $navigation) {
            if (ImbaUserContext::getUserRole() >= $navigation->getMinUserRole()) {
                $showMe = false;
                if (ImbaUserContext::getLoggedIn() && $navigation->getShowLoggedIn()) {
                    $showMe = true;
                } elseif ((!ImbaUserContext::getLoggedIn()) && $navigation->getShowLoggedOff()) {
                    $showMe = true;
                }

                if ($showMe) {
                    $return .= "<li><a href='javascript:void(0)' onclick='javascript: loadImbaAdminModule(\"" . $navigation->getClassname() . "\");' title='" . $navigation->getComment($nav) . "'>" . $navigation->getName($nav) . "</a></li>";
                }
            }
        }
        $return .= "</ul>";
        $return .= "</li>";
        return $return;
    }

    protected function renderImbaModuleNavigation($portal) {

    }

    /**
     * Render the Portal Chooser Dropdown
     */
    protected function renderPortalChooser() {
        $managerPortal = ImbaManagerPortal::getInstance();

        $return = "<li>";
        $return .= "<a id='imbaMenuImbaPortal' href='javascript:void(0)' onclick='javascript: loadImbaPortal(-1);' title='Portal Zur&uuml;cksetzen'>Portal</a>";
        $return .= "<ul class='subnav'>";
        foreach ($managerPortal->selectAll() as $portal) {
            $return .= "<li style='vertical-align: middle;'><a href='javascript:void(0)' onclick='javascript: loadImbaPortal(" . $portal->getId() . ");' title='" . $portal->getComment() . "'>";
            $return .= "<img src='" . ImbaSharedFunctions::fixWebPath($portal->getIcon()) . "' width='24px' height='24px' /> " . $portal->getName();
            $return .= "</a></li>";
        }
        $return .= "</ul>";
        $return .= "</li>";

        return $return;
    }

}

?>
