<?php

/**
 *  Controller / Manager for Categories
 *  - insert, update, delete Categories
 */
class ImbaManagerGameCategory extends ImbaManagerBase {

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
       return new ImbaManagerGameCategory();
    }

    /**
     * Inserts a category into the Database
     */
    public function insert(ImbaGameCategory $category) {
        $query = "INSERT INTO %s ";
        $query .= "(name) VALUES ";
        $query .= "('%s')";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_MULTIGAMING_CATEGORIES,
            $category->getName(),
        ));

        $this->setManagerCache(null);
    }

    /**
     * Updates a category into the Database
     */
    public function update(ImbaGameCategory $category) {
        $query = "UPDATE %s SET ";
        $query .= "name = '%s' ";
        $query .= "WHERE id = '%s';";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_MULTIGAMING_CATEGORIES,
            $category->getName(),
            $category->getId()
        ));

        $this->setManagerCache(null);
    }

    /**
     * Delets a category by Id
     */
    public function delete($id) {
        $query = "DELETE FROM %s Where id = '%s';";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_MULTIGAMING_CATEGORIES,
            $id
        ));

        $this->setManagerCache(null);
    }

    /**
     * Select all categories
     */
    public function selectAll() {
        if ($this->getManagerCache() == null) {
            $result = array();

            $query = "SELECT * FROM %s WHERE 1 ORDER BY name ASC;";

            $this->database->query($query, array(ImbaConstants::$DATABASE_TABLES_SYS_MULTIGAMING_CATEGORIES));
            while ($row = $this->database->fetchRow()) {
                $category = new ImbaGameCategory();
                $category->setId($row["id"]);
                $category->setName($row["name"]);

                array_push($result, $category);
            }

            $this->setManagerCache($result);
        }

        return $this->getManagerCache();
    }

    /**
     * Get a new Game category
     */
    public function getNew() {
        $category = new ImbaGameCategory();
        return $category;
    }

    /**
     * Select one Game by Id
     */
    public function selectById($id) {
        foreach ($this->selectAll() as $category) {
            if ($category->getId() == $id)
                return $category;
        }
        return null;
    }

}

?>
