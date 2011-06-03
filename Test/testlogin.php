<?php

echo "Immernoch nicht einverstanden? 1.6 <hr>";

chdir("../Libs/");
require_once ("Zend/OpenID.php");
require_once ("Zend/OpenId/Consumer.php");
require_once ("Zend/Controller/Exception.php");

$consumer = new Zend_OpenId_Consumer();

if ($consumer->login("http://openid-provider.appspot.com/Steffen.So@googlemail.com")) {
    echo "Logged in";
} else {
    echo "OpenID login failed. <br>";
    echo $consumer->getError();

}

echo "<hr>Und fertig."
?>