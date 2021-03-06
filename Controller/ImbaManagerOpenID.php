<?php

$tmpPath = getcwd();
chdir("Libs/");
require_once "lightopenid/LightOpenID.php";
require_once "Zend/Oauth/Consumer.php";
chdir($tmpPath);

/**
 * Description of ImbaManagerOpenID
 * - Handles OpneID verification, authentifications
 */
class ImbaManagerOpenID {

    private static $lightOpenid = null;

    /**
     * OpenID Auth
     */
    public function openidAuth($openid, $hash, $realm, $returnTo) {
        $this->lightOpenid = new LightOpenID();
        $this->lightOpenid->verify_peer = ImbaConstants::$WEB_AUTH_SSL_CHECK;
        $this->lightOpenid->returnUrl = $returnTo;
        $this->lightOpenid->realm = $realm;

        if (!$this->lightOpenid->mode) {
            if (isset($openid)) {
                $this->lightOpenid->identity = $openid;
                return $this->lightOpenid->authUrl();
                //return $this->lightOpenid->authUrl(true);
                // with true, openid check_immediate is used
            }
        }
    }

    /**
     * OpenID verify
     */
    public function openidVerify($realm, $returnTo) {
        $this->lightOpenid = new LightOpenID();
        $this->lightOpenid->verify_peer = ImbaConstants::$WEB_AUTH_SSL_CHECK;
        $this->lightOpenid->returnUrl = $returnTo;
        $this->lightOpenid->realm = $realm;

        if ($this->lightOpenid->mode == 'cancel') {
            throw new Exception(ImbaConstants::$ERROR_OPENID_Auth_OpenID_CANCEL);
        } elseif ($this->lightOpenid->validate()) {
            return $this->lightOpenid->identity;
        } else {
            return false;
        }
    }

    /**
     * Return thrust root for redirection
     */
    public function getTrustRoot() {
        return $this->lightOpenid->trustRoot;
    }

    /**
     * Return openid of the actual auth process
     */
    public function getOpenId() {
        return $this->lightOpenid->identity;
    }

}

?>