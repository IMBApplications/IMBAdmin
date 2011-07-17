<script type="text/javascript">
    $(document).ready(function() {
        $("#ImbaAjaxUsersViewprofilePasswordFormSubmit").button();
        $("#ImbaAjaxUsersViewprofileUserprofileFormSubmit").button();

        // User submits the ImbaAjaxUsersViewprofileUserprofileForm
        $("#ImbaAjaxUsersViewprofileUserprofileForm").validate({
            submitHandler: function(form) {
                // submit the change
                $.post(ajaxEntry, {
                    secSession: phpSessionID,
                    module: "AjaxUser",
                    submodule: "IMBAdminModules",
                    ajaxmethod: "updateMyProfile",
                    params: JSON.stringify({
                        "motto" : $("#myProfileMotto").val(),
                        "usertitle" :$("#myProfileUsertitle").val(),
                        "avatar" : $("#myProfileAvatar").val(),
                        "website" : $("#myProfileWebsite").val(),
                        "nickname" : $("#myProfileNickname").val(),
                        "email" : $("#myProfileEmail").val(),
                        "skye" : $("#myProfileSkype").val(),
                        "icq" : $("#myProfileIcq").val(),
                        "msn" : $("#myProfileMsn").val(),
                        "signature" : $("#myProfileSignature").val()
                    })
                }, function(response){
                    if (response != "Ok"){
                        $.jGrowl(response, { header: 'Error' });
                    } else {
                        $.jGrowl('Daten wurden gespeichert!', { header: 'Erfolg' });
                    }
                });
                return false;
            }
        });
        
        // User submits the ImbaAjaxUsersViewprofileUserprofileForm
        $("#ImbaAjaxUsersViewprofilePasswordFormSubmit").click(function(){
            // submit the change
            $.post(ajaxEntry, {
                secSession: phpSessionID,
                module: "AjaxUser",
                submodule: "IMBAdminModules",
                ajaxmethod: "updateMyPassword",
                params: JSON.stringify({
                    "oldPassword" : $("#myOldPassword").val(),
                    "newPassword1" : $("#myNewPassword1").val(),
                    "newPassword2" : $("#myNewPassword2").val(),
                })
            }, function(response){
                if (response != "Ok"){
                    $.jGrowl(response, { header: 'Error' });
                } else {
                    $.jGrowl('Passwort wurde ge&auml;ndert!', { header: 'Erfolg' });
                }
            });
            return false;
        });
    } );   
</script>
<h2>Profil Informationen</h2>
<form id="ImbaAjaxUsersViewprofileUserprofileForm" action="" method="post" class='cmxform'>
    <table class="ImbaAjaxBlindTable" style="cellspacing: 1px;">
        <tbody>
            <tr>
                <td>
        <nobr>Aktuelles Motto:</nobr>
        </td>
        <td>
            <input id="myProfileMotto" type="text" name="motto" value="{$motto}" />
        </td>
        <td rowspan="10" style="vertical-align: top;">
            <ul style="width: 200px;">
                <li><i>Um deinen Namen das Geschlecht oder dein Geburtstag zu &auml;ndern, kontaktiere bitte einen Administrator.</i></li>
                <li><i>Die Emailadresse wird ausschliesslich gebraucht um mit dir Kontakt aufzunehmen.</i></li>
                <li><i>Dein Nachname wird f&uuml;r alle anderen auf einen Buchstaben gek&uuml;rzt ({$firstname} {$shortlastname}).</i></li>
            </ul>
        </td>
        </tr>

        <tr>
            <td>Titel:</td>
            <td><input id="myProfileUsertitle" type="text" name="usertitle" value="{$usertitle}" /></td>
        </tr>
        <tr>
            <td>Avatar URL:</td>
            <td><input id="myProfileAvatar" type="text" name="avatar" value="{$avatar}" /></td>
        </tr>
        <tr>
            <td>Webseite:</td>
            <td><input id="myProfileWebsite" type="text" name="website" value="{$website}" /></td>
        </tr>
        <tr>
            <td>Nickname:</td>
            <td><input id="myProfileNickname" type="text" name="nickname" value="{$nickname}" title=" <<<" class="required" minlength="3" /></td>
        </tr>
        <tr>
            <td>Email:</td>
            <td><input id="myProfileEmail" type="text" name="email" value="{$email}"  title=" <<<" class="email" /></td>
        </tr>
        <tr>
            <td>Skype:</td>
            <td><input id="myProfileSkype" type="text" name="skype" value="{$skype}" /></td>
        </tr>
        <tr>
            <td>ICQ:</td>
            <td><input id="myProfileIcq" type="text" name="icq" value="{$icq}" /></td>
        </tr>
        <tr>
            <td>MSN:</td>
            <td><input id="myProfileMsn" type="text" name="msn" value="{$msn}" /></td>
        </tr>
        <tr>
            <td>Signatur:</td>
            <td><textarea id="myProfileSignature" name="signature" rows="4" cols="50">{$signature}</textarea></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan="2"><input id="ImbaAjaxUsersViewprofileUserprofileFormSubmit" type="submit" value="Speichern" /></td>
        </tr>
        </tbody>
    </table>
</form>
<h2>Passwort &auml;ndern</h2>
<form id="ImbaAjaxUsersViewprofilePasswordForm" action="post">
    <table class="ImbaAjaxBlindTable" style="cellspacing: 1px;">
        <tbody>
            <tr>
                <td>Altes Passwort:</td>
                <td><input id="myOldPassword" type="password" name="oldPassword" value="" /></td>
            </tr>
            <tr>
                <td>Neues Passwort:</td>
                <td><input id="myNewPassword1" type="password" name="newPassword1" value="" /></td>
            </tr>
            <tr>
                <td>Wiederholen:</td>
                <td><input id="myNewPassword2" type="password" name="newPassword1" value="" /></td>
            </tr>
            <tr>
                <td>&nbsp;</td>
                <td colspan="2"><input id="ImbaAjaxUsersViewprofilePasswordFormSubmit" type="submit" value="&Auml;ndern" /></td>
            </tr>
        </tbody>
    </table>
</form>
