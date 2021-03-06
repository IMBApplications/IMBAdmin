<?php

/**
 * Controller / Manager Database
 * Handling:
 * - Connection
 * - Fetch
 * - Query
 */
class ImbaManagerDatabase {

    private $connection = null;
    /**
     * Recent response from sql server.
     */
    private $result = null;
    /**
     * Number of rows afflicted by the recent successful query send.
     */
    private $counter = null;
    /**
     * Singleton implementation.
     *
     * @var type singleton object.
     */
    private static $instance = null;

    /**
     * ctor
     */
    private function __construct() {
        // Setting the local Timezone
        setlocale(ImbaConstants::$CONTEXT_LOCALE[0], ImbaConstants::$CONTEXT_LOCALE[1], ImbaConstants::$CONTEXT_LOCALE[2], ImbaConstants::$CONTEXT_LOCALE[3], ImbaConstants::$CONTEXT_LOCALE[4]);

        $this->connection = mysql_pconnect(ImbaConfig::$DATABASE_HOST, ImbaConfig::$DATABASE_USER, ImbaConfig::$DATABASE_PASS, TRUE);
        mysql_query('set character set utf8;');
        mysql_set_charset('UTF8', $this->connection);

        if (!mysql_select_db(ImbaConfig::$DATABASE_DB, $this->connection)) {
            throw new Exception("Database Connection not working!");
        }
    }

    public static function getInstance() {
        if (self::$instance === null)
            self::$instance = new self();
        return self::$instance;
    }

    public function disconnect() {
        if (is_resource($this->connection))
            mysql_close($this->connection);
    }

    public function query($queryStr, array $args = array()) {
        foreach ($args as $key => $value) {
            $args[$key] = mysql_real_escape_string(stripslashes($value));
        }
        $query = vsprintf($queryStr, $args);

        //echo $query . "\n";

        $this->result = mysql_query($query, $this->connection);

        if (!$this->result) {
            $q = $this->getQuery($queryStr, $args);
            throw new Exception("Database Query not working! ( '$q' )");
        }

        $this->counter = null;
    }

    public function getQuery($queryStr, array $args = array()) {
        foreach ($args as $key => $value) {
            $args[$key] = mysql_real_escape_string(stripslashes($value));
        }
        $query = vsprintf($queryStr, $args);

        return $query;
    }

    public function fetchRow() {
        return mysql_fetch_assoc($this->result);
    }

    public function fetchArray() {
        $result = array();
        while ($row = $this->fetchRow()) {
            array_push($result, $row);
        }

        return $result;
    }

    public function count() {
        if ($this->counter == null && is_resource($this->result)) {
            $this->counter = mysql_num_rows($this->result);
        }

        return $this->counter;
    }

    public function getLastInsertedId() {
        return mysql_insert_id();
    }

}

?>