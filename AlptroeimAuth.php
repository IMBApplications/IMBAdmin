<?php

session_start();

$postvars = "secSession=" . session_id() . "&facility=me";
session_write_close();
$username = file_get_contents("http://localhost/IMBAdmin/ImbaProxy.php?" . $postvars);

include("/../wordpress/wp-load.php");
wp_setcookie($username);
echo "done";
?>