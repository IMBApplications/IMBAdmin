<form action="" method="post">
    module: <input name="module" type="text" value="<?= $_POST['module'] ?>" /> <br />
    submodule: <input name="submodule" type="text" value="<?= $_POST['submodule'] ?>" /> <br />
    ajaxmethod: <input name="ajaxmethod" type="text"  value="<?= $_POST['ajaxmethod'] ?>" /> <br />
    params: <input name="params" type="text"  /> <br />

    <input type="submit" name="submit" value="Test" />
</form>

<?php
if ($_POST["submit"] == "Test") {
    
    var_dump($_POST);
    echo "<hr>";
    
    chdir("../");
    include ("ImbaAjaxFacade.php");   
    
}
?>
