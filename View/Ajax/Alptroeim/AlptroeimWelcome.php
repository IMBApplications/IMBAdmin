<?php

/**
 * Handling the ajax Callbacks for the Alptroeim module Welcome
 */
class AlptroeimWelcome extends AjaxBase {

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
        /*$navigation->addElement("viewWelcomeIndexed", "Indexierte &Uuml;bersicht", "Hier siehst du eine komplette &Uml;bersicht der Module.");*/
        return $navigation;
    }

    public function viewWelcome() {        
        $this->smarty->display('Alptroeim/Welcome.tpl');
    }
}

?>
