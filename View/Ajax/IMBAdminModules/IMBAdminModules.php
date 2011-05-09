<?php

/**
 * Middleman for the Module
 */
class IMBAdminModules {

    /**
     * Gets all the Navigations for under
     */
    public function getNavigations() {
        $result = array();

        $tmp = new AjaxAdministration();
        array_push($result, $tmp->getNavigation());

        $tmp = new AjaxMaintenance();
        array_push($result, $tmp->getNavigation());

        $tmp = new AjaxMessenger();
        array_push($result, $tmp->getNavigation());

        $tmp = new AjaxRegistration();
        array_push($result, $tmp->getNavigation());

        $tmp = new AjaxUser();
        array_push($result, $tmp->getNavigation());

        $tmp = new AjaxWelcome();
        array_push($result, $tmp->getNavigation());

        return $result;
    }

    /**
     * Returns the Default Module
     */
    public function returnDefaultModule() {
        if (ImbaUserContext::getLoggedIn()) {
            echo ImbaConstants::$WEB_DEFAULT_LOGGED_IN_MODULE;
        } else {
            echo ImbaConstants::$WEB_DEFAULT_LOGGED_OUT_MODULE;
        }
    }

}

?>
