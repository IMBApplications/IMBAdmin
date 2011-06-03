<?php
chdir("../");
require_once ("Libs/lightopenid/LightOpenID.php");

$lightOpenId = new LightOpenID();
$lightOpenId->verify_peer = false;
$lightOpenId->returnUrl = "http://dev.alptroeim.ch/IMBAdmin/Test/openid.php?step=2";
$lightOpenId->realm = "http://dev.alptroeim.ch";

if ($_GET["step"] == 1) {
    if (!$lightOpenId->mode) {
        $lightOpenId->identity = $_GET["openid"];
        //$lightOpenId->identity = "https://oom.ch/openid/identity/sampit";
        //$lightOpenId->identity = "http://openid-provider.appspot.com/Steffen.So@googlemail.com";
        header('Location: ' . $lightOpenId->authUrl());
    }
} else if ($_GET["step"] == 2) {
    try {
        echo "GET: <br>\n";
        foreach ($_GET as $key => $value) {
            echo $key . " = " . rawurldecode($value) . "<br>\n";
        }

        echo $lightOpenId->validate() ? 'Logged in.' : 'Failed';
    } catch (Exception $ex) {
        echo "Fehler: <br>";
        echo $ex->getMessage();
    }
} else {
    ?>
    <form method="get">
        Sign in with your OpenID: <br/>
        <input type="text" name="openid" size="30" />
        <input type="hidden" name="step" value="1" />
        <br />
        <input type="submit" name="submit" value="Log In" />
    </form>
    <?php
}
?>
