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

<br />
ImbaLoader.php:1Uncaught SyntaxError: Unexpected token <
<b>Fatal error</b>:  ImbaManagerNavigation::__construct() [&lt;a href='imbamanagernavigation.--construct'&gt;imbamanagernavigation.--construct&lt;/a&gt;]: The script tried to execute a method or access a property of an incomplete object. Please ensure that the class definition &amp;quot;ImbaPortal&amp;quot; of the object you are trying to operate on was loaded _before_ unserialize() gets called or provide a __autoload() function to load the class definition  in <b>E:\Programme 2\xampp\htdocs\IMBAdmin\Controller\ImbaManagerNavigation.php</b> on line <b>26</b><br />
