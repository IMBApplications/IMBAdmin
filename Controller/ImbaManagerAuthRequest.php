<?php


/**
 * Description of ImbaManagerAuthRequest
 */
class ImbaManagerAuthRequest extends ImbaManagerBase {

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
        return new ImbaManagerAuthRequest();
    }

    public function getNew() {
        return new ImbaAuthRequest();
    }

    public function insert(ImbaAuthRequest $authRequest) {
        $query = "INSERT INTO %s ";
        $query .= "(hash, userid, phpsession, realm, timestamp, returnto, type, domain, ip) VALUES ";
        $query .= "('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')";

        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_AUTH_REQUEST,
            $authRequest->getHash(),
            $authRequest->getUserId(),
            $authRequest->getPhpsession(),
            $authRequest->getRealm(),
            time(),
            $authRequest->getReturnTo(),
            $authRequest->getType(),
            $authRequest->getDomain(),
            $authRequest->getIp()
        ));
    }

    public function select($hash) {
        $query = "SELECT * FROM %s WHERE hash='%s';";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_AUTH_REQUEST,
            $hash
        ));

        $authRequest = $this->getNew();
        while ($row = $this->database->fetchRow()) {
            $authRequest->setHash($hash);
            $authRequest->setPhpsession($row["phpsession"]);
            $authRequest->setRealm($row["realm"]);
            $authRequest->setTimestamp($row["timestamp"]);
            $authRequest->setUserId($row["userid"]);
            $authRequest->setReturnTo($row["returnto"]);
            $authRequest->setType($row["type"]);
            $authRequest->setDomain($row["domain"]);
            $authRequest->setIp($row["ip"]);
        }
        return $authRequest;
    }

    public function delete($hash) {
        $query = "DELETE FROM %s WHERE hash='%s';";
        $this->database->query($query, array(
            ImbaConstants::$DATABASE_TABLES_SYS_AUTH_REQUEST,
            $hash
        ));

        return true;
    }

}

?>
