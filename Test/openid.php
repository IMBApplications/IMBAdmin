<?php

chdir("../");
require_once ("Libs/lightopenid/LightOpenID.php");

$lightOpenId = new LightOpenID();
$lightOpenId->verify_peer = false;
$lightOpenId->returnUrl = "http://locahost/IMBAdmin/Test/openid.php?step=2";
$lightOpenId->realm = "http://locahost/";

if (empty($_GET["step"])) {
    if (!$lightOpenId->mode) {
        $lightOpenId->identity = "http://openid-provider.appspot.com/Steffen.So@googlemail.com";
        header('Location: ' . $lightOpenId->authUrl());
    }
} else if ($_GET["step"] == 2) {
    echo $lightOpenId->validate() ? 'Logged in.' : 'Failed';
}
?>
