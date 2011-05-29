<?php

/**
 * Description of ImbaPortal
 */
class ImbaPortal extends ImbaBase {

    protected $name = null;
    protected $icon = null;
    protected $aliases = array();
    protected $portalEntries = array();
    protected $portalModules = array();
    protected $comment = null;
    protected $portalAuth = null;

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getIcon() {
        if ($this->icon == null || $this->icon == "") {
            return "Images/noicon.png";
        } else {
            return $this->icon;
        }
    }

    public function setIcon($icon) {
        $this->icon = $icon;
    }

    public function getAliases() {
        return $this->aliases;
    }

    public function setAliases($aliases) {
        $this->aliases = $aliases;
    }

    public function addAlias($alias) {
        array_push($this->aliases, $alias);
    }

    public function getPortalEntries() {
        return $this->portalEntries;
    }

    public function setPortalEntries($portalEntries) {
        $this->portalEntries = $portalEntries;
    }

    public function addEntry($entry) {
        array_push($this->portalEntries, $entry);
    }

    public function getPortalModules() {
        return $this->portalModules;
    }

    public function setPortalModules($portalModules) {
        $this->portalModules = $portalModules;
    }

    public function addModule($module) {
        array_push($this->portalModules, $module);
    }

    public function getComment() {
        return $this->comment;
    }

    public function setComment($comment) {
        $this->comment = $comment;
    }

    public function getPortalAuth() {
        return $this->portalAuth;
    }

    public function setPortalAuth($portalAuth) {
        $this->portalAuth = $portalAuth;
    }

}

?>
