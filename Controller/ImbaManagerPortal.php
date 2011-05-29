<?php

/**
 *  Controller / Manager for Portal Sites
 *  - insert, update, delete Portal
 */

/**
 * MySql Setup
  CREATE TABLE IF NOT EXISTS `oom_openid_portals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `aliases` text NOT NULL,
  `navitems` text NOT NULL,
  `icon` varchar(200) NOT NULL,
  `comment` text NOT NULL,
  `portal_auth` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

  CREATE TABLE IF NOT EXISTS `oom_openid_portals_modules` (
  `portal_id` int(11) NOT NULL,
  `module` varchar(40) NOT NULL,
  UNIQUE KEY `portal_id` (`portal_id`,`module`)
  ) ENGINE=InnoDB DEFAULT CHARSET=latin1;

  ALTER TABLE `oom_openid_portals_modules`
  ADD CONSTRAINT `oom_openid_portals_modules_ibfk_1` FOREIGN KEY (`portal_id`) REFERENCES `oom_openid_portals` (`id`);
 */
class ImbaManagerPortal extends ImbaManagerBase {

    /**
     * Property
     */
    protected $portalsCached = null;

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
        return new ImbaManagerPortal();
    }

    /**
     * Inserts a portal into the Database
     */
    public function insert(ImbaPortal $portal) {
        $query = "INSERT INTO %s (icon, name, comment) VALUES ('%s', '%s', '%s');";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_PORTALS,
            $portal->getIcon(),
            $portal->getName(),
            $portal->getComment()
        ));

        $query = "SELECT LAST_INSERT_ID() as LastId;";
        $this->database->query($query, array());
        $row = $this->database->fetchRow();

        $this->portalsCached = null;

        return $row["LastId"];
    }

    /**
     * Updates a portal into the Database
     */
    public function update(ImbaPortal $portal) {
        if ($portal->getId() == null)
            throw new Exception("No Portal Id given");

        // update the portal itself
        $query = "UPDATE %s SET ";
        $query .= "name = '%s', icon = '%s', comment = '%s', portal_auth = '%s' ";
        $query .= "WHERE id = '%s';";

        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_PORTALS,
            $portal->getName(),
            $portal->getIcon(),
            $portal->getComment(),
            $portal->getPortalAuth(),
            $portal->getId()
        ));

        // add the portal modules
        $query = "DELETE FROM %s WHERE portal_id = '%s';";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_MODULES,
            $portal->getId()
        ));

        foreach ($portal->getPortalModules() as $module) {
            $query = "INSERT INTO %s (portal_id, module) VALUES (%s, '%s');";

            $this->database->query($query, array(
                ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_MODULES,
                $portal->getId(),
                $module
            ));
        }

        // add the portal entries
        $query = "DELETE FROM %s WHERE portal_id = '%s';";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_INTERCEPT_PORTALS_PORTALENTRIES,
            $portal->getId()
        ));

        foreach ($portal->getPortalEntries() as $portalentry) {
            $query = "INSERT INTO %s (portal_id, portalentry_id) VALUES (%s, %s);";

            $this->database->query($query, array(
                ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_INTERCEPT_PORTALS_PORTALENTRIES,
                $portal->getId(),
                $portalentry->getId()
            ));
        }

        // add the aliases
        $query = "DELETE FROM %s WHERE portal_id = '%s';";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_ALIAS,
            $portal->getId()
        ));

        foreach ($portal->getAliases() as $alias) {
            $query = "INSERT INTO %s (name, portal_id) VALUES('%s', '%s');";
            $this->database->query($query, array(
                ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_ALIAS,
                $alias,
                $portal->getId()
            ));
        }


        $this->portalsCached = null;
    }

    /**
     * Delets a portal by Id
     */
    public function delete($id) {
        $query = "DELETE FROM %s Where id = '%s';";
        $this->database->query($query, array(ImbaConstants::$DATABASE_TABLES_SYS_PORTALS, $id));

        $this->portalsCached = null;
    }

    /**
     * Adds a portal alias
     */
    public function addAlias($portalid, $alias) {
        if ($alias == "") {
            throw new Exception("No Alias given");
        }

        $query = "INSERT INTO %s (portal_id, name) VALUES ('%s', '%s')";
        $this->database->query($query, array(ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_ALIAS, $portalid, $alias));

        $this->portalsCached = null;
    }

    /**
     * Delets a portal alias
     */
    public function deleteAlias($portalid, $alias) {
        if ($alias == "") {
            throw new Exception("No Alias given");
        }

        $query = "DELETE FROM %s Where portal_id = '%s' AND name = '%s';";
        $this->database->query($query, array(ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_ALIAS, $portalid, $alias));

        $this->portalsCached = null;
    }

    /**
     * Select all Portals
     */
    public function selectAll() {
        if ($this->portalsCached == null) {
            $managerPortalEntries = ImbaManagerPortalEntry::getInstance();
            $result = array();

            /**
             * Get the modules of the portals
             */
            $query = "SELECT * FROM %s WHERE 1;";
            $this->database->query($query, array(ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_MODULES));
            $modules = array();
            while ($row = $this->database->fetchRow()) {
                array_push($modules, array("portal_id" => $row['portal_id'], "module" => $row['module']));
            }

            /**
             * Get the aliases of the portals
             */
            $query = "SELECT * FROM %s WHERE 1;";
            $this->database->query($query, array(ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_ALIAS));
            $aliases = array();
            while ($row = $this->database->fetchRow()) {
                array_push($aliases, array("portal_id" => $row['portal_id'], "name" => $row['name']));
            }

            /**
             * Get the portal entries of the portals
             */
            $portalentries = $managerPortalEntries->selectAll();

            /**
             * Get the portal <-> entries intersect data
             */
            $query = "SELECT * FROM %s WHERE 1;";
            $this->database->query($query, array(ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_INTERCEPT_PORTALS_PORTALENTRIES));
            $portalentries_intersect = array();
            while ($row = $this->database->fetchRow()) {
                array_push($portalentries_intersect, array(
                    "portal_id" => $row['portal_id'],
                    "portalentry_id" => $row['portalentry_id']
                ));
            }

            /**
             * Get the portals data and put it all together
             */
            $query = "SELECT * FROM %s WHERE 1 ORDER BY name ASC;";
            $this->database->query($query, array(ImbaConstants::$DATABASE_TABLES_SYS_PORTALS));
            while ($row = $this->database->fetchRow()) {
                $portal = new ImbaPortal();
                $portal->setId($row["id"]);
                $portal->setName($row["name"]);
                $portal->setComment($row["comment"]);
                $portal->setPortalAuth($row["portal_auth"]);
                $portal->setIcon($row["icon"]);

                /**
                 * Fill the modules
                 */
                $tmpModule = array();
                foreach ($modules as $module) {
                    if ($module['portal_id'] == $portal->getId()) {
                        array_push($tmpModule, $module['module']);
                    }
                }
                $portal->setPortalModules($tmpModule);

                /**
                 * Fill the aliases
                 */
                $tmpAliases = array();
                foreach ($aliases as $alias) {
                    if ($alias['portal_id'] == $portal->getId()) {
                        array_push($tmpAliases, $alias['name']);
                    }
                }
                $portal->setAliases($tmpAliases);

                /**
                 * Fill the portal entries
                 */
                $tmpEntries = array();
                foreach ($portalentries_intersect as $intersect) {
                    if ($intersect['portal_id'] == $portal->getId()) {
                        array_push($tmpEntries, $managerPortalEntries->selectById($intersect['portalentry_id']));
                    }
                }
                $portal->setPortalEntries($tmpEntries);

                array_push($result, $portal);
            }

            $this->portalsCached = $result;
        }
        return $this->portalsCached;
    }

    /**
     * Get a new Portal
     */
    public function getNew() {
        $portal = new ImbaManagerPortal();
        return $portal;
    }

    /**
     * Select one Portal by Id
     */
    public function selectById($id) {
        foreach ($this->selectAll() as $portal) {
            if ($portal->getId() == $id)
                return $portal;
        }
        return null;
    }

    /**
     * Returns all aliases from all portals
     */
    public function getAllAliases() {
        $query = "SELECT * FROM %s WHERE 1;";
        $this->database->query($query, array(ImbaConstants::$DATABASE_TABLES_SYS_PORTALS_ALIAS));
        $aliases = array();
        while ($row = $this->database->fetchRow()) {
            array_push($aliases, $row['name']);
        }

        return array_unique($aliases);
    }

}

?>
