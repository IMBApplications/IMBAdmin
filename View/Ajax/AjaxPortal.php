<?php

/**
 * Handling the ajax Callbacks for Portals
 */
class AjaxPortal extends AjaxBase {

    public function __construct() {
        parent::__construct();
    }

    public function getContentManager() {
        throw new Exception("AjaxPortal got no Navigation.");
    }

    /**
     * Gets a portal by id
     * @param type $param ({"id":"1"})
     */
    public function getPortal($params) {
        // ImbaLogin.js asks us to set the users actual selected portal
        // reset current portal to default
        if ($params->id == -1) {
            unset($_SESSION["IUC_PortalContext"]);
            unset($params->id);
        }

        // Load default portal
        if (empty($params->id)) {
            $tmpContext = null;

            foreach ($this->managerPortal->selectAll() as $tmpPortal) {
                if (count($tmpPortal->getAliases())) {
                    foreach ($tmpPortal->getAliases() as $tmpAlias) {
                        $tmpHost = str_replace("http://", "", ImbaSharedFunctions::getDomain($_SERVER['HTTP_REFERER']));
                        $tmpHost = str_replace("https://", "", $tmpHost);
                        if ($tmpHost == $tmpAlias) {
                            $tmpContext = $tmpPortal->getId();
                        }
                    }
                }
            }
            if ($tmpContext == null) {
                $tmpContext = ImbaUserContext::getPortalContext();
            }

            $portal = $this->managerPortal->selectById($tmpContext);
        }
        // set currently portal to $params->id
        elseif (!empty($params->id)) {
            ImbaUserContext::setPortalContext($params->id);

            $portal = $this->managerPortal->selectById(ImbaUserContext::getPortalContext());
        }

        //get currently active portal and send the data back
        echo json_encode(
                array(
                    "name" => $portal->getName(),
                    "icon" => ImbaSharedFunctions::fixWebPath($portal->getIcon()),
                    "navigation" => $this->managerNavigation->getNavigationForPortal($portal),
                    "portalauth" => $portal->getPortalAuth()
                )
        );
    }

}

?>
