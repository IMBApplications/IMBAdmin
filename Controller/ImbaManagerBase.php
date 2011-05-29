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
    protected $managerCacheExtended = null;

    /**
     * Ctor
     */
    public function __construct() {
        $this->database = ImbaManagerDatabase::getInstance();
    }

    public function getManagerCache() {
        $superType = get_class($this);

        if (session_id() != "") {
            if ($_SESSION[$superType . "time"] + 30 < time()) {
                return null;
            } else {
                return $_SESSION[$superType . "cache"];
            }
        } else {
            throw new Exception("No Session for caching.");
        }
    }

    public function setManagerCache($managerCache) {
        $superType = get_class($this);

        if (session_id() != "") {
            $_SESSION[$superType . "cache"] = $managerCache;
            $_SESSION[$superType . "time"] = time();
        } else {
            throw new Exception("No Session for caching.");
        }
    }

    public function getManagerCacheExtended() {
        return $this->managerCacheExtended;
    }

    public function setManagerCacheExtended($managerCacheExtended) {
        $this->managerCacheExtended = $managerCacheExtended;
    }

}

?>
