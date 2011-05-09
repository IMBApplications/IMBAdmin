<?php

/**
 * Single point of Ajax entry
 *
 */
header('Access-Control-Allow-Origin: *');
session_start();

require_once ("Controller/Include.php");
require_once ("Shared/Include.php");
require_once ("View/Ajax/AjaxBase.php");

try {
    // Check if module and ajaxmethod is set
    if (empty($_POST["module"])) {
        throw new Exception("No Module");
    }
    if (empty($_POST["ajaxmethod"])) {
        throw new Exception("No Ajax Method");
    }

    // setting variables
    $module = $_POST["module"];
    $submodule = (!empty($_POST["submodule"])) ? $_POST["submodule"] . "/" : "";
    $method = $_POST["ajaxmethod"];

    if (!empty($_POST["params"]) && $_POST["params"] != "" && is_string($_POST["params"])) {
        $params = json_decode($_POST["params"]);
        if ($params == null) {
            throw new Exception("Wrong JSON input");
        }
    }

    // create a callback object
    require_once ("View/Ajax/" . $submodule . $module . ".php");
    $ajaxCallback = new $module();

    // execute the call
    echo $ajaxCallback->$method($params);
} catch (Exception $ex) {
    // Logging
    echo $ex->getMessage();
    echo "\n\n";
    echo "POST Data:";
    echo "\n\n";
    var_dump($_POST);
}
?>