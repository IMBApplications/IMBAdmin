<?php

/**
 * Handling the ajax Callbacks for the ImbaAdmin module Registration
 */
class AjaxRegistration extends AjaxBase {

    public function __construct() {
        parent::__construct();
    }

    public function getContentManager() {
        /**
         * Define Navigation
         */
        $navigation = new ImbaContentManager();

        /**
         * Set module name
         */
        $navigation->setName("Registrieren");
        $navigation->setClassname(get_class($this));
        $navigation->setComment("In diesem Module registrieren sich neue Benutzer.");

        /**
         * Set when the module should be displayed (logged in 1/0)
         */
        $navigation->setShowLoggedIn(false);
        $navigation->setShowLoggedOff(true);

        /**
         * Set the minimal user role needed to display the module
         */
        $navigation->setMinUserRole(0);

        /**
         * Set tabs
         */
        $navigation->addElement("viewRegisterForm", "Registration", "Hier kannst du dich registrieren.");
        $navigation->addElement("viewResetPassword", "Passwort zur&uuml;cksetzen", "Hier kannst du dein Passwort zur&uuml;cksetzen.");
        $navigation->addElement("viewAbout", "&Uuml;ber uns | About", "Wer sind wir und was ist das hier.");
        return $navigation;
    }

    /**
     * Checks the submitted password
     * @param type $password 
     */
    public function checkPassword($params) {
        $return = ImbaSharedFunctions::checkPassword($params->password);
        if ($return === true) {
            echo "Ok";
        } else {
            echo $return;
        }
    }

    /**
     * views the About page
     */
    public function viewAbout() {
        ImbaConstants::loadSettings();

        $this->smarty->display('IMBAdminModules/RegisterAbout.tpl');
    }

    /**
     * views the reset password page
     */
    public function viewResetPassword() {
        ImbaConstants::loadSettings();
        $this->smarty->assign('email', ImbaConstants::$SETTINGS["ADMIN_EMAIL"]);

        $this->smarty->display('IMBAdminModules/RegisterResetPassword.tpl');
    }

    /**
     * Checks the user input and resets the password if correct
     */
    public function resetPassword($params) {
        ImbaConstants::loadSettings();

        $bd = explode(".", $params->date);
        $users = $this->managerUser->selectAllUser();
        $userFound = false;

        // search for the user with the birthday
        foreach ($users as $user) {
            if (($user->getBirthday() == $bd[0]) &&
                    ($user->getBirthmonth() == $bd[1]) &&
                    ($user->getBirthyear() == $bd[2]) &&
                    (strtolower($user->getFirstname()) == strtolower(trim($params->name1))) &&
                    (strtolower($user->getLastname()) == strtolower(trim($params->name2)))) {
                // user found
                $userFound = $user->getId();
            }
        }

        if ($userFound) {
            $newPw = ImbaSharedFunctions::getRandomString(8);
            $myUser = $this->managerUser->selectById($userFound);

            $myUser->setPassword(md5($newPw));
            $this->managerUser->update($myUser);

            ImbaSharedFunctions::sendEmail(
                    $myUser->getEmail(), $_SERVER[HTTP_HOST] . " Passwort Reset", "Hallo " . $myUser->getNickname() . "\r\n\r\n" .
                    "Irgendjemand hat dein Passwort zurueckgesetzt.\r\n" .
                    "Dein neues Passwort ist: " . $newPw . "\r\n\r\n" .
                    "Freundliche Gruesse\r\n" . ImbaConstants::$SETTINGS["ADMIN_EMAIL_NAME"] . "\r\n"
            );
            
            echo "Ok";
        } else {
            echo "No user found.";
        }
    }

    /**
     * views the Registration start page
     */
    public function viewRegisterForm() {
        ImbaConstants::loadSettings();
        ImbaUserContext::setNeedToRegister(true);

        $_SESSION["IUC_captchaState"] = "unchecked";

        $this->smarty->assign('authPath', ImbaConstants::$WEB_AUTH_MAIN_PATH);
        $this->smarty->assign('indexPath', ImbaConstants::$WEB_ENTRY_INDEX_FILE);
        $this->smarty->assign('publicKey', ImbaConstants::$SETTINGS["CAPTCHA_PUBLIC_KEY"]);
        $this->smarty->display('IMBAdminModules/RegisterForm.tpl');
    }

    /**
     * checks the Captcha for registration
     * @param type $param ({"challenge":"abc", "answer":"abc", 
     * "birthday":"11.11.1111", "firstname":"abc", "lastname":"abc", "sex":"abc", 
     * "nickname":"abc", "email":"abc", "sex":"abc"})
     */
    public function checkCaptchaForRegistration($params) {
        //Do some basic tests
        if (ImbaUserContext::getNeedToRegister()) {
            ImbaConstants::loadSettings();
            require_once 'Libs/reCaptcha/recaptchalib.php';
            $resp = null;
            $error = null;

            // Check if the recaptcha is ok
            $resp = recaptcha_check_answer(
                    ImbaConstants::$SETTINGS["CAPTCHA_PRIVATE_KEY"], ImbaSharedFunctions::getIP(), $params->challenge, $params->answer);

            $tmpOpenid = ImbaUserContext::getOpenIdUrl();
            if ($resp->is_valid) {
                // Check if all fields have content
                $pwCheck = ImbaSharedFunctions::checkPassword($params->password);

                if (
                        (!empty($params->birthday)) &&
                        (!empty($params->firstname)) &&
                        (!empty($params->lastname)) &&
                        (!empty($params->sex)) &&
                        (!empty($params->nickname)) &&
                        (!empty($params->password)) &&
                        (!empty($params->email))) {

                    if ($pwCheck !== true) {
                        echo $pwCheck;
                        exit();
                    }

                    $birthdate = explode(".", $params->birthday);

                    /**
                     * Set the new user
                     */
                    $newUser = $this->managerUser->getNew();
                    $newUser->setFirstname(trim($params->firstname));
                    $newUser->setLastname(trim($params->lastname));
                    $newUser->setSex(trim($params->sex));
                    $newUser->setNickname(trim($params->nickname));
                    $newUser->setEmail(trim($params->email));
                    $newUser->setPassword(trim(md5($params->password)));
                    $newUser->setBirthday($birthdate[0]);
                    $newUser->setBirthmonth($birthdate[1]);
                    $newUser->setBirthyear($birthdate[2]);
                    $newUser->setOpenId($tmpOpenid);
                    $newUser->setRole(ImbaConstants::$CONTEXT_NEW_USER_ROLE);

                    // Save the new user
                    $this->managerUser->insert($newUser);
                    echo "Ok";
                } else {
                    // Something strange happend. Try to kick the user out of all sessions
                    echo "You did not fill out all the needed fields!";
                }
            } else {
                # set the error code so that we can display it
                $error = $resp->error;
                if ($error == "incorrect-captcha-sol") {
                    echo "Deine Eingabe war nicht korrekt!";
                } else {
                    echo $error;
                }
            }
            /**
             * Check fucking everything here! NEVER THRUST A USER
             * - query http://www.google.com/recaptcha/api/verify
             */
            // Then save the user
            $_SESSION["IUC_captchaState"] = "ok";
        } else {
            // Something strange happend. Try to kick the user out of all sessions
            echo "Strange Error: ImbaUserContext::getNeedToRegister() is not set, but it should be.";
        }
    }

    /**
     * registration done
     */
    public function registrationDone() {
        // Show success an let him click a button which sends him trough the normal login procedure
        if ($_SESSION["IUC_captchaState"] == "ok") {
            $_SESSION["IUC_captchaState"] = "";
            ImbaUserContext::getNeedToRegister(false);
            $this->smarty->display('IMBAdminModules/RegisterSuccess.tpl');
        } else {
            header("location: " . ImbaConstants::$WEB_AUTH_PROXY_PATH . "?logout=true");
        }
    }

}

?>
