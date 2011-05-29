<?php

/**
 * Creating the Managers
 */
abstract class AjaxBase {

    protected $managerUser;
    protected $managerMessage;
    protected $managerChatChannel;
    protected $managerChatMessage;
    protected $managerRole;
    protected $managerGame;
    protected $managerGameCategory;
    protected $managerGameProperty;
    protected $managerPortal;
    protected $managerPortalEntry;
    protected $managerLog;
    protected $managerNavigation;
    protected $smarty;

    public function __construct() {
        $this->smarty = ImbaSharedFunctions::newSmarty();
        $this->managerUser = ImbaManagerUser::getInstance();
        $this->managerMessage = ImbaManagerMessage::getInstance();
        $this->managerChatChannel = ImbaManagerChatChannel::getInstance();
        $this->managerChatMessage = ImbaManagerChatMessage::getInstance();
        $this->managerRole = ImbaManagerUserRole::getInstance();
        $this->managerGame = ImbaManagerGame::getInstance();
        $this->managerGameCategory = ImbaManagerGameCategory::getInstance();
        $this->managerGameProperty = ImbaManagerGameProperty::getInstance();
        $this->managerPortal = ImbaManagerPortal::getInstance();
        $this->managerPortalEntry = ImbaManagerPortalEntry::getInstance();
        $this->managerLog = ImbaManagerLog::getInstance();
        $this->managerNavigation = ImbaManagerNavigation::getInstance();
    }

    abstract public function getContentManager();

    /**
     * Gets Data from the Navigation
     * @param type $params ({"datarequest":"abc"})
     */
    public function getNavigationData($params) {
        $nav = $this->getContentManager();

        switch ($params->datarequest) {
            case "nav":
                $result = array();
                foreach ($nav->getElements() as $navEntry) {
                    array_push($result, array("id" => $navEntry, "name" => $nav->getElementName($navEntry)));
                }
                echo json_encode($result);
                break;
            case "name":
                echo $nav->getName();
                break;
            case "comment":
                echo $nav->getComment();
                break;
            default:
                echo "Welcome";
                break;
        }
    }

    /**
     * Gets all Navigation for existing modules
     */
    public static function getModulesNavigation($module) {
        require_once ("View/Ajax/" . $module . "/Include.php");
        $tmpNavigation = new $module();
        return $tmpNavigation->getNavigations();
    }

}

?>
