<?php
chdir("../Libs/");
require_once ("Zend/OpenID.php");
require_once ("Zend/OpenId/Consumer.php");
require_once ("Zend/Controller/Exception.php");

$consumer = new Zend_OpenId_Consumer();
if ($consumer->login("http://openid-provider.appspot.com/Steffen.So@googlemail.com") === false)
{
    echo "failed";
}
?>
