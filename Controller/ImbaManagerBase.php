<?php

/**
 * Base class for all Managers
 */
class ImbaManagerBase {

    /**
     * ImbaManagerDatabase
     */
    protected $database = null;
    /**
     * Cache
     */
    protected $managerCache = null;

    /**
     * Ctor
     */
    public function __construct() {
        $this->database = ImbaManagerDatabase::getInstance();
    }

    public function getManagerCache() {
        return $this->managerCache;
    }

    public function setManagerCache($managerCache) {
        $this->managerCache = $managerCache;
    }

}

?>
