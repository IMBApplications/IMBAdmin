<?php

/**
 * If a request comes from any of our portals, tell them the current logged in
 * username
 */
session_start();

require_once 'Controller/Include.php';
require_once 'Shared/Include.php';

$ref = ImbaSharedFunctions::getDomain($_SERVER["HTTP_REFERER"]);
$ref = str_replace("http://", "", $ref);
$ref = str_replace("https://", "", $ref);

$portalAliases = ImbaManagerPortal::getInstance()->getAllAliases();

if (in_array($ref, $portalAliases) || $_SERVER["HTTP_HOST"] = "localhost") {
    if (ImbaUserContext::getLoggedIn()) {
        $username = ImbaManagerUser::getInstance()->selectById(ImbaUserContext::getUserId())->getNickname();
        echo $username;
    } else {
        echo "Not logged in.";
    }
} else {
    echo "Portal unknown, or no referer.";
}
?>
