<?php

/**
 * Description of ImbaAuthPassword
 *
 * @author oxi
 */
class ImbaAuthPassword extends ImbaAuthBase {

    /**
     * Ctor
     */
    public function __construct() {
        parent::__construct();
    }

    public function process() {
        if ((!empty($_REQUEST["nickname"])) && (!empty($_REQUEST["password"]))) {
            $this->login();
        } else {
            $tmpMsg = $this->writeAuthLog("Nickname (" . $_REQUEST["nickname"] . ") or Password not recieved for Password auth.");
        }

        $this->redirectTo(__LINE__, ImbaUserContext::getRedirectUrl(), $tmpMsg);
        exit;
    }

    private function login() {
        // Password auth
        // Check if all needed things are here

        $tmpMsg = $this->writeAuthLog("Password authentification detected.");

        // Get all users to search for username
        $myUser = $this->managerUser->selectByNickname($_REQUEST["nickname"]);

        if ($myUser !== null) {
            // Found Nickname, check the password

            if (md5($_REQUEST["password"]) == $myUser->getPassword()) {
                // Password is OK
                $tmpMsg = $this->writeAuthLog("Password authentification successful for " . $_REQUEST["nickname"]);
                $this->checkAndLogin($myUser);
            } else {
                $tmpMsg = $this->writeAuthLog("Password authentification successful for " . $_REQUEST["nickname"] . ", NOT!");
            }
        } else {
            $tmpMsg = $this->writeAuthLog("No such user found: " . $_REQUEST["nickname"]);
        }
    }

}

?>
