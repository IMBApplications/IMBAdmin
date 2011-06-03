<?php

chdir("../");
require_once ("Test/SimpleOpenID.php");


$openid = new SimpleOpenID;
$openid->SetIdentity('https://oom.ch/openid/identity/sampit');
$openid->SetApprovedURL('http://dev.alptroeim.ch/IMBAdmin/Test/openid.php');
$openid->SetTrustRoot('http://dev.alptroeim.ch/');
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
