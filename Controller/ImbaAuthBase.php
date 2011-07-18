<?php

/**
 * Description of ImbaAuthBase
 *
 * @author oxi
 */
abstract class ImbaAuthBase {

    // Initiate the manager
    protected $managerUser = null;

    /**
     * Ctor
     */
    public function __construct() {
        $this->managerUser = ImbaManagerUser::getInstance();
    }

    /**
     * Private function to write a log
     */
    protected function writeAuthLog($message, $level = 3) {
        // Load the Log manager
        $managerLog = ImbaManagerLog::getInstance();

        $log = $managerLog->getNew();
        $log->setModule("ImbaAuth");
        $log->setMessage($message);
        $log->setLevel($level);
        $managerLog->insert($log);
        ImbaUserContext::setImbaErrorMessage($message);
        return $message;
    }

    /**
     * Kill our session and go to URL, function
     */
    public static function killAndRedirect($targetUrl) {
        setcookie(session_id(), "", time() - 3600);
        session_destroy();
        session_write_close();
        header("Location: " . $targetUrl);
    }

    /**
     * Redirect with domain magic
     */
    protected function redirectTo($line, $url, $message = "") {
        $myDomain = ImbaSharedFunctions::getDomain($url);

        // Discover if we need to do the html redirect and make it so
        if (headers_sent()) {
            $smarty = ImbaSharedFunctions::newSmarty();

            $smarty->assign("redirectUrl", rawurldecode($url));
            $smarty->assign("redirectDomain", $myDomain);
            $smarty->assign("internalCode", $line);
            $smarty->assign("internalMessage", $message);
            $smarty->assign("phpsession", session_id());
            $smarty->display("ImbaAuthRedirect.tpl");
            exit;
        } else {
            header("Location: " . $url);
            exit;
        }
    }

    /**
     * This function actually and eventually logs the user in!
     */
    protected function checkAndLogin(ImbaUser $currentUser, $openid = "") {

        // Check the status of the user
        if (empty($currentUser)) {
            // This is a new user. let him register
            $this->writeAuthLog("Registering new user", 2);

            if (!empty($openid)) {
                ImbaUserContext::setNeedToRegister(true);
                ImbaUserContext::setOpenIdUrl($openid);
            }
        } elseif ($currentUser->getRole() == 0) {
            // This user is banned
            $this->writeAuthLog($currentUser->getName() . " is banned but tried to login", 1);
            throw new Exception("You are Banned!");
        } elseif ($currentUser->getRole() != null) {
            // This user is allowed to log in
            $tmpMsg = $this->writeAuthLog($currentUser->getNickname() . " logged in", 1);

            // Setup all login stuff
            ImbaUserContext::setLoggedIn(true);
            ImbaUserContext::setOpenIdUrl($openid);
            ImbaUserContext::setUserRole($currentUser->getRole());
            ImbaUserContext::setUserId($currentUser->getId());

            setcookie("ImbaSsoLastLoginName", "", (time() - 3600));
            setcookie("ImbaSsoLastLoginName", $currentUser->getNickname(), (time() + (60 * 60 * 24 * 30)));

            $this->managerUser->setMeOnline();
            ImbaUserContext::setImbaErrorMessage($currentUser->getNickname() . " logged in.");
        }
    }
    
    /**
     * Eventually and actually loggs the user out! kkthxbb
     */
    public static function logout() {
        // We want to log out
        //FIXME: ImbaAuthBase::writeAuthLog("Logging out (Redirecting)", 2);

        if (empty($_POST['imbaSsoOpenIdLogoutReferer'])) {
            $targetUrl = ImbaSharedFunctions::getTrustRoot();
        } else {
            $targetUrl = $_POST['imbaSsoOpenIdLogoutReferer'];
        }

        ImbaAuthBase::killAndRedirect($targetUrl);
        exit;
    }

    /**
     * The actual auth process
     */
    abstract public function process();
}

?>
