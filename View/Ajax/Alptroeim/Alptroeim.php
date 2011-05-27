<?php

/**
 * Middleman for the Module
 */
class Alptroeim {

    /**
     * Gets all the Navigations for under
     */
    public function getNavigations() {
        $result = array();

        $tmp = new AlptroeimWelcome();
        array_push($result, $tmp->getContentManager());

        return $result;
    }

    /**
     * Returns the Default Module
     */
    public function returnDefaultModule() {
        return "AlptroeimWelcome";
    }

}

?>
