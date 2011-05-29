<?php

/**
 * Description of ImbaManagerLog
 */
class ImbaManagerLog extends ImbaManagerBase {

    protected $logsCached = null;
    protected $logsCachedTimestamp = null;

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
        return new ImbaManagerLog();
    }

    /**
     * Create new log entry
     */
    public function getNew() {
        $log = new ImbaLog();
        $log->setTimestamp(time());
        $log->setIp(ImbaSharedFunctions::getIP());
        $log->setSession(session_id());

        // If not logged in, set User with id 1
        if (ImbaUserContext::getLoggedIn()) {
            $log->setUser(ImbaUserContext::getUserId());
        } else {
            $log->setUser(1);
        }

        return $log;
    }

    /*
     * Inserts a Systemmessage / Log
     */

    public function insert(ImbaLog $log) {
        $query = "INSERT INTO %s ";
        $query .= "(timestamp, user, ip, module, session, msg, lvl) VALUES ";
        $query .= "('%s', '%s', '%s', '%s', '%s', '%s', '%s')";

        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_SYSTEMMESSAGES,
            $log->getTimestamp(),
            $log->getUser(),
            $log->getIp(),
            $log->getModule(),
            $log->getSession(),
            $log->getMessage(),
            $log->getLevel()
        ));
    }

    public function selectAll() {
        if ($this->logsCached == null) {
            $query = "SELECT * FROM %s WHERE 1 ORDER BY id DESC;";
            $this->database->query($query, array(ImbaConstants::$DATABASE_TABLES_SYS_SYSTEMMESSAGES));

            $messages = array();
            while ($row = $this->database->fetchRow()) {
                $log = new ImbaLog();
                $log->setId($row["id"]);
                $log->setTimestamp($row["timestamp"]);
                $log->setUser($row["user"]);
                $log->setIp($row["ip"]);
                $log->setModule($row["module"]);
                $log->setSession($row["session"]);
                $log->setMessage($row["msg"]);
                $log->setLevel($row["lvl"]);

                array_push($messages, $log);
                unset($log);
            }
            $this->logsCachedTimestamp = time();
            $this->logsCached = $messages;
        }

        return $this->logsCached;
    }

    /**
     * Clear all system messages
     */
    public function clearAll() {
        $query = "DELETE FROM %s;";
        $this->database->query($query, array(ImbaConstants::$DATABASE_TABLES_SYS_SYSTEMMESSAGES));

        $log = $this->getNew();
        $log->setModule("Admin");
        $log->setMessage("Logs cleared");
        $log->setLevel(0);
        $this->insert($log);
    }

    public function selectId($id) {
        $message = null;
        foreach ($this->selectAll()as $tmpMessage) {
            if ($id == $tmpMessage->getId())
                $message = $tmpMessage;
        }
        return $message;
    }

    public function selectSession($session) {
        $messages = array();
        foreach ($this->selectAll()as $message) {
            if ($session == $message->getSession())
                array_push($messages, $message);
        }
        return $messages;
    }

    public function selectUserSessions() {
        $messages = array();
        foreach ($this->selectAll()as $message) {
            if ("Logged in" == $message->getMessage())
                array_push($messages, $message);
        }
        return $messages;
    }

}

?>
