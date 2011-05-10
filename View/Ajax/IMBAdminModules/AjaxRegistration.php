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
        $navigation->addElement("viewRegisterStartPage", "Registration", "Hier kannst du dich registrieren.");
        return $navigation;
    }

    /**
     * views the Registration start page
     */
    public function viewRegisterStartPage() {
        if (ImbaUserContext::getNeedToRegister()) {
            // Registration step 1 done
            ImbaConstants::loadSettings();
            $this->smarty->assign('openid', ImbaUserContext::getOpenIdUrl());


            $_SESSION["IUC_captchaState"] = "unchecked";

            $this->smarty->assign('authPath', ImbaConstants::$WEB_AUTH_PROXY_PATH);
            $this->smarty->assign('indexPath', ImbaConstants::$WEB_ENTRY_INDEX_FILE);
            $this->smarty->assign('publicKey', ImbaConstants::$SETTINGS["CAPTCHA_PUBLIC_KEY"]);
            $this->smarty->display('IMBAdminModules/RegisterForm.tpl');
        } else {
            // User gets the welcome screen with the openid input field
            $this->smarty->assign('registerurl', ImbaConstants::$WEB_AUTH_PROXY_PATH);
            $this->smarty->display('IMBAdminModules/RegisterWelcome.tpl');
        }
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
                if (
                        (!empty($params->birthday)) &&
                        (!empty($tmpOpenid)) &&
                        (!empty($params->firstname)) &&
                        (!empty($params->lastname)) &&
                        (!empty($params->sex)) &&
                        (!empty($params->nickname)) &&
                        (!empty($params->email))) {

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
                    header("location: " . ImbaConstants::$WEB_AUTH_PROXY_PATH . "?logout=true");
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
            header("location: " . ImbaConstants::$WEB_AUTH_PROXY_PATH . "?logout=true");
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
