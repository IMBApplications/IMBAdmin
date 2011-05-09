<?php

/**
 *  Controller / Manager for Properties
 *  - insert, update, delete Properties
 */
class ImbaManagerGameProperty extends ImbaManagerBase {

    /**
     * Fields
     */
    protected $propertiesCached = null;
    /**
     * Singleton implementation
     */
    private static $instance = null;

    /**
     * Ctor
     */
    protected function __construct() {
        //parent::__construct();
        $this->database = ImbaManagerDatabase::getInstance();
    }

    /*
     * Singleton init
     */

    public static function getInstance() {
        if (self::$instance === null)
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * Inserts a property into the Database
     */
    public function insert(ImbaGameProperty $property) {
        if ($property->getProperty() == null || $property->getProperty() == ""){
            throw new Exception("Bitte Property angeben!");
        }
        $query = "INSERT INTO %s (game_id, property) VALUES ('%s', '%s');";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_MULTIGAMING_GAMES_PROPERTIES,
            $property->getGameId(),
            $property->getProperty()
        ));
    }

    /**
     * Updates a property into the Database
     */
    public function update(ImbaGameProperty $property) {
        $query = "UPDATE %s SET game_id = '%s', property = '%s' WHERE id='%s';";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_MULTIGAMING_GAMES_PROPERTIES,
            $property->getGameId(),
            $property->getProperty(),
            $property->getId()
        ));
    }

    /**
     * Delets a property by Id
     */
    public function delete($id) {
        $query = "DELETE FROM %s Where id = '%s';";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_MULTIGAMING_GAMES_PROPERTIES,
            $id
        ));
    }

    /**
     * Select all properties
     */
    public function selectAll() {
        if ($this->propertiesCached == null) {
            $result = array();

            $query = "SELECT * FROM %s order by property ASC;";

            $this->database->query($query, array(ImbaConstants::$DATABASE_TABLES_SYS_MULTIGAMING_GAMES_PROPERTIES));
            while ($row = $this->database->fetchRow()) {
                $property = new ImbaGameProperty();
                $property->setId($row["id"]);
                $property->setGameId($row["game_id"]);
                $property->setProperty($row["property"]);

                array_push($result, $property);
            }
            $this->propertiesCached = $result;
        }

        return $this->propertiesCached;
    }

    /**
     * Select all properties of a game
     */
    public function selectAllByGameId($gameId) {
        $result = array();        
        foreach ($this->selectAll() as $property) {
            if ($property->getGameId() == $gameId) {
                array_push($result, $property);
            }
        }
        return $result;
    }

    /**
     * Get a new Game Property
     */
    public function getNew() {
        $property = new ImbaGameProperty();
        return $property;
    }

    /**
     * Select one Game Property by Id
     */
    public function selectById($id) {
        foreach ($this->selectAll() as $property) {
            if ($property->getId() == $id)
                return $property;
        }
        return null;
    }

}

?>
