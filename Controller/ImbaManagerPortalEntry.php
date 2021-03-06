<?php

/**
 *  Controller / Manager for Portal Entries
 *  - insert, update, delete Portal Entry
 */
class ImbaManagerPortalEntry extends ImbaManagerBase {

    /**
     * Ctor
     */
    public function __construct() {
        parent::__construct();
    }

    /*
     * Singleton init
     */
    public static function getInstance() {
        return new ImbaManagerPortalEntry();
    }

    /**
     * Inserts a PortalEntry into the Database
     */
    public function insert(ImbaPortalEntry $portalEntry) {
        $query = "INSERT INTO %s (handle, name, target, url, comment, loggedin, role) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s');";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_PORTALENTRIES,
            $portalEntry->getHandle(),
            $portalEntry->getName(),
            $portalEntry->getTarget(),
            $portalEntry->getUrl(),
            $portalEntry->getComment(),
            $portalEntry->getLoggedin(),
            $portalEntry->getRole()
        ));

        $query = "SELECT LAST_INSERT_ID() as LastId;";
        $this->database->query($query, array());
        $row = $this->database->fetchRow();

        $this->setManagerCache(null);

        return $row["LastId"];
    }

    /**
     * Updates a game into the Database
     */
    public function update(ImbaPortalEntry $portalEntry) {
        if ($portalEntry->getId() == null)
            throw new Exception("No Portal Entry Id given");

        $query = "UPDATE %s SET handle= '%s', name= '%s', target= '%s', url= '%s', comment= '%s', loggedin= '%s', role= '%s' WHERE id = '%s';";

        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_PORTALENTRIES,
            $portalEntry->getHandle(),
            $portalEntry->getName(),
            $portalEntry->getTarget(),
            $portalEntry->getUrl(),
            $portalEntry->getComment(),
            $portalEntry->getLoggedin(),
            $portalEntry->getRole(),
            $portalEntry->getId()
        ));

        $this->setManagerCache(null);
    }

    /**
     * Delets a PortalEntry by Id
     */
    public function delete($id) {
        $query = "DELETE FROM %s Where id = '%s';";
        $this->database->query($query, array(ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_PORTALENTRIES, $id));

        $this->setManagerCache(null);
    }

    /**
     * Select all PortalEntries
     */
    public function selectAll() {
        if ($this->getManagerCache() == null) {
            $result = array();

            $query = "SELECT * FROM %s WHERE 1 ORDER BY name ASC;";

            $this->database->query($query, array(ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_PORTALENTRIES));
            while ($row = $this->database->fetchRow()) {
                $entry = new ImbaPortalEntry();
                $entry->setId($row["id"]);
                $entry->setHandle($row["handle"]);
                $entry->setComment($row["comment"]);
                $entry->setLoggedin($row["loggedin"]);
                $entry->setName($row["name"]);
                $entry->setRole($row["role"]);
                $entry->setTarget($row["target"]);
                $entry->setUrl($row["url"]);

                array_push($result, $entry);
            }
            $this->setManagerCache($result);
        }
        return $this->getManagerCache();
    }

    /**
     * Get a new PortalEntry
     */
    public function getNew() {
        $portalEntry = new ImbaPortalEntry();
        return $portalEntry;
    }

    /**
     * Select one Portal Entry by Id
     */
    public function selectById($id) {
        foreach ($this->selectAll() as $portalEntry) {
            if ($portalEntry->getId() == $id)
                return $portalEntry;
        }
        return null;
    }

}

?>
