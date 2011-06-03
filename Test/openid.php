<?php

chdir("../");
require_once ("Test/SimpleOpenID.php");


$openid = new SimpleOpenID;
$openid->SetIdentity('openid-provider.appspot.com/steffen.so@googlemail.com');
$openid->SetApprovedURL('http://localhost/IMBAdmin/Test/openid.php');
$openid->SetTrustRoot('http://localhost/');
$server_url = $openid->GetOpenIDServer();
if ($server_url !== false) {
    $openid->SetOpenIDServer($server_url);
}

if ($openid->ValidateWithServer()) {
    echo "Logged in";
} else {
    echo "Fehler: <br /><pre>";
    print_r($openid->GetError());
    echo "</pre>";
}
?>
