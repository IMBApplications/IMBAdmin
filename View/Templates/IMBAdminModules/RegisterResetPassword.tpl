<script type="text/javascript">
    $(document).ready(function() {
        $("#pwResetDate").datepicker({ 
            dateFormat: 'dd.mm.yy', 
            changeMonth: true,
            changeYear: true
        });
        
        $("#pwResetButton").button();
        $("#pwResetButton").click(function() {
            if (($("#pwResetDate").val() != "") && ($("#pwResetUserToken").val() != "")) {
                $.post(ajaxEntry, {
                    secSession: phpSessionID,
                    module: "AjaxRegistration",
                    submodule: "IMBAdminModules",
                    ajaxmethod: "resetPassword",
                    params: JSON.stringify({
                        "userDate" : $("#pwResetDate").val(),
                        "userToken" : $("#pwResetToken").val()
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
<table>
    <tr>
        <td>
            Nickname oder Email
        </td>
        <td>
            <input type="text" id="pwResetToken" />
        </td>
    </tr>
    <tr>
        <td>
            Geburtsdatum
        </td>
        <td>
            <div id="pwResetDate"></div>
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