<?php

/**
 * Sandbox for various testings
 */
chdir("../");

require_once ("Model/Include.php");
require_once ("Controller/Include.php");
require_once ("Shared/Include.php");
require_once ("View/Ajax/IMBAdminModules/Include.php");


session_start();
ImbaUserContext::setLoggedIn(true);
ImbaUserContext::setOpenIdUrl("http://openid-provider.appspot.com/Steffen.So@googlemail.com");
ImbaUserContext::setUserId(4);
ImbaUserContext::setUserRole(3);

?>