<?php

/**
 * Description of ImbaManagerMessage
 */
class ImbaManagerMessage extends ImbaManagerBase {

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
        return new ImbaManagerMessage();
    }

    /**
     * Tries to send an insert command to the database.
     * Inserts a message into the Database if successfully.
     */
    public function insert(ImbaMessage $message) {
        if ($message->getMessage() == null || trim($message->getMessage()) == "") {
            throw new Exception("No Message!");
        }
        if ($message->getSender() == null) {
            throw new Exception("No Sender!");
        }

        if ($message->getReceiver() == null) {
            throw new Exception("No Reciever!");
        }

        $query = "INSERT INTO %s ";
        $query .= "(sender, receiver, timestamp, subject, message, new, xmpp) VALUES ";
        $query .= "('%s', '%s', '%s', '%s', '%s', '%s','%s')";

        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_USR_MESSAGES,
            $message->getSender()->getId(),
            $message->getReceiver()->getId(),
            $message->getTimestamp(),
            $message->getSubject(),
            htmlspecialchars($message->getMessage()),
            $message->getNew(),
            $message->getXmpp()
        ));

        return $this->database->getLastInsertedId();
    }

    /**
     * Delets a message by Id
     */
    public function delete($id) {
        $query = "DELETE FROM  %s Where id = '%s';";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_USR_MESSAGES,
            $id
        ));
    }

    /**
     * Get num of messages
     */
    public function returnNumberOfMessages() {
        $query = "SELECT * FROM  %s Where 1;";

        $this->database->query($query, array(ImbaConstants::$DATABASE_TABLES_USR_MESSAGES));
        return $this->database->count();
    }

    /**
     * Selects a complete Conversation between me and an Opponent
     * $since == +1 => all new
     * $since == 0 => all Messages
     * $since == -1 => give me the last 10 Messages
     */
    public function selectAllByOpponentId($idOpponent, $since = 0) {
        // cachen all users
        $managerUser = ImbaManagerUser::getInstance();
        $managerUser->selectAll();

        if ($since == 0) {
            $query = "SELECT * FROM %s WHERE (sender = '%s' and receiver = '%s') or (sender = '%s' and receiver = '%s') ORDER BY id ASC;";
            $this->database->query($query, array(
                ImbaConstants::$DATABASE_TABLES_USR_MESSAGES,
                ImbaUserContext::getUserId(),
                $idOpponent,
                $idOpponent,
                ImbaUserContext::getUserId()
            ));
        } else if ($since == -1) {
            $query = "SELECT * FROM %s WHERE (sender = '%s' and receiver = '%s') or (sender = '%s' and receiver = '%s') ORDER BY id ASC LIMIT 0, 10;";
            $this->database->query($query, array(
                ImbaConstants::$DATABASE_TABLES_USR_MESSAGES,
                ImbaUserContext::getUserId(),
                $idOpponent,
                $idOpponent,
                ImbaUserContext::getUserId()
            ));
        } else {
            $query = "SELECT * FROM %s WHERE sender = '%s' and receiver = '%s' and new = '1'  ORDER BY id ASC;";
            $this->database->query($query, array(
                ImbaConstants::$DATABASE_TABLES_USR_MESSAGES,
                $idOpponent,
                ImbaUserContext::getUserId()
            ));
        }

        $result = new ArrayObject();
        while ($row = $this->database->fetchRow()) {
            $message = new ImbaMessage();
            $message->setId($row["id"]);
            $message->setSender($managerUser->selectById($row["sender"]));
            $message->setReceiver($managerUser->selectById($row["receiver"]));
            $message->setTimestamp($row["timestamp"]);
            $message->setSubject($row["subject"]);
            $message->setMessage($row["message"]);
            $message->setNew($row["new"]);
            $message->setXmpp($row["xmpp"]);
            $result->append($message);
        }

        return $result;
    }

    /**
     * Selects the count of lines Conversation between me and a userid
     */
    public function selectMessagesCount($idOpponent) {
        // TODO: Nur die zählen, die in den letzten monaten geschlumpft wurden
        $query = "SELECT count(*) MsgCount FROM %s Where (sender = '%s' and receiver = '%s') or (sender = '%s' and receiver = '%s') AND timestamp > (" . (time() - 4838400) . ");";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_USR_MESSAGES,
            ImbaUserContext::getUserId(),
            $idOpponent,
            $idOpponent,
            ImbaUserContext::getUserId()
        ));

        $row = $this->database->fetchRow();
        return $row["MsgCount"];
    }

    /**
     * Selects the timestamp of the last Message in conversation between me and $idOpponent
     */
    public function selectLastMessageTimestamp($idOpponent) {
        $return = 0;
        $query = "SELECT timestamp FROM %s Where (receiver = '%s' and sender = '%s') OR (receiver = '%s' and sender = '%s') ORDER BY timestamp DESC LIMIT 0,1;";

        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_USR_MESSAGES,
            ImbaUserContext::getUserId(),
            $idOpponent,
            $idOpponent,
            ImbaUserContext::getUserId()));

        while ($row = $this->database->fetchRow()) {
            $return = $row['timestamp'];
        }

        return $return;
    }

    /**
     * Selects all new Messages for a user
     */
    public function selectMyNewMessages() {
        // cache all users
        $managerUser = ImbaManagerUser::getInstance();
        $managerUser->selectAll();

        $query = "SELECT DISTINCT sender FROM %s Where `receiver` = '%s' and new = 1;";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_USR_MESSAGES,
            ImbaUserContext::getUserId()
        ));

        $result = array();
        while ($row = $this->database->fetchRow()) {
            $user = $managerUser->selectById($row["sender"]);
            array_push($result, array("name" => $user->getNickname(), "id" => $user->getId()));
        }

        return $result;
    }

    /**
     * Mark a message as read
     */
    public function setMessageRead($idOpponent) {
        $query = "UPDATE %s SET new = 0 where sender = '%s' and receiver = '%s';";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_USR_MESSAGES,
            $idOpponent,
            ImbaUserContext::getUserId()
        ));
    }

}

?>
