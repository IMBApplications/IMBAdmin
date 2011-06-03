<?php
chdir("../Libs/");
require_once ("Zend/OpenID.php");
require_once ("Zend/OpenId/Consumer.php");
require_once ("Zend/Controller/Exception.php");

$status = "Start";

try {
    if (!isset($_GET['openid_mode'])) {
        $consumer = new Zend_OpenId_Consumer();
        if (!$consumer->login("http://openid-provider.appspot.com/Steffen.So@googlemail.com")) {
            $status = "OpenID login failed.";
        }
    } else if (isset($_GET['openid_mode'])) {
        if ($_GET['openid_mode'] == "id_res") {
            $consumer = new Zend_OpenId_Consumer();
            if ($consumer->verify($_GET, $id)) {
                $status = "VALID " . htmlspecialchars($id);
            } else {
                $status = "INVALID " . htmlspecialchars($id);
            }
        } else if ($_GET['openid_mode'] == "cancel") {
            $status = "CANCELLED";
        }
    }
} catch (Exception $ex) {
    echo "Exception: " . $ex->getMessage();
}

echo $status;
?>