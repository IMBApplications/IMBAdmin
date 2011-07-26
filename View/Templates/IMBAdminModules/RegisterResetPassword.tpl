<script type="text/javascript">
    $(document).ready(function() {
        $("#pwResetDate").datepicker({ 
            dateFormat: 'dd.mm.yy', 
            changeMonth: true,
            changeYear: true
        });
        
        $("#pwResetButton").button();
        $("#pwResetButton").click(function() {
            if (($("#pwResetDate").val() != "") && 
                ($("#pwResetName1").val() != "") && 
                ($("#pwResetName2").val() != "")) {
                $.post(ajaxEntry, {
                    secSession: phpSessionID,
                    module: "AjaxRegistration",
                    submodule: "IMBAdminModules",
                    ajaxmethod: "resetPassword",
                    params: JSON.stringify({
                        "date" : $("#pwResetDate").val(),
                        "name1" : $("#pwResetName1").val(),
                        "name2" : $("#pwResetName2").val()
                    })
                }, function(response){
                    if (response != "Ok"){
                        $.jGrowl(response, { header: 'Error' });
                        return false;
                    } else {
                        $.jGrowl('Das neue Passwort wurde verschickt', { header: 'Erfolg' });
                        return false;
                    }   
                });
            } else {
                $.jGrowl('Nicht alle Felder ausgef&uuml;llt!', { header: 'Error' });
            }
        });
    });
</script>
<h2>Reset Password</h2>
Falls du die folgenden Felder richtig ausf&uuml;llst,<br />
erh&auml;lst du auf deine Emailadresse ein neues Kennwort zugeschickt.<br />
<br />
<table>
    <tr>
        <td>
            Vornahme
        </td>
        <td>
            <input type="text" id="pwResetName1" />
        </td>
    </tr>
    <tr>
        <td>
            Nachnahme
        </td>
        <td>
            <input type="text" id="pwResetName2" />
        </td>
    </tr>
    <tr>
        <td>
            Geburtsdatum
        </td>
        <td>
            <input type="text" id="pwResetDate" />
        </td>
    </tr>
    <tr>
        <td>
            &nbsp;
        </td>
        <td>
            <input id="pwResetButton" type="submit" value="zur&uuml;cksetzen" />
        </td>
    </tr>
</table>
<br />
<br />
<br />
<h2>Wenn alle Stricke reissen</h2>
Kontaktiere bitte die Administratoren in einem Game mit einem eingetragenen<br />
Account oder sende eine Email an <a href="mailto:{$email}?subject=Passwort_Reset">{$email}</a>.