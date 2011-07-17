<script type="text/javascript">
    $(document).ready(function() {
        $("#ImbaAdminViewEditUserBack").button();
        
        // set the datepicker
        $("#myProfileBirthday").datepicker({ 
            dateFormat: 'dd.mm.yy', 
            changeMonth: true,
            changeYear: true });
        
        // User submits the ImbaAjaxUsersViewprofileUserprofileForm
        $("#ImbaAjaxUsersViewprofileUserprofileFormSubmit").button();
        $("#ImbaAjaxUsersViewprofileUserprofileFormSubmit").click(function(){
            // submit the change
            // hier fehlt noch einiges
            $.post(ajaxEntry, {
                secSession: phpSessionID,
                module: "AjaxAdministration",
                submodule: "IMBAdminModules",
                ajaxmethod: "updateUser",
                params: JSON.stringify({
                    "motto" : $("#myProfileMotto").val(),
                    "role" : $("#myProfileRole").val(),
                    "birthday" : $("#myProfileBirthday").val(),
                    "lastname" : $("#myProfileLastname").val(),
                    "firstname" : $("#myProfileFirstname").val(),
                    "usertitle" : $("#myProfileUsertitle").val(),
                    "avatar" : $("#myProfileAvatar").val(),
                    "website" : $("#myProfileWebsite").val(),
                    "nickname" : $("#myProfileNickname").val(),
                    "email" : $("#myProfileEmail").val(),
                    "skype" : $("#myProfileSkype").val(),
                    "icq" : $("#myProfileIcq").val(),
                    "msn" : $("#myProfileMsn").val(),
                    "signature" : $("#myProfileSignature").val(),
                    "id": "{$userid}"
                })
            }, function(response){
                if (response != "Ok"){
                    $.jGrowl(response, { header: 'Error' });
                } else {
                    $.jGrowl('Daten wurden gespeichert!', { header: 'Erfolg' });
                }
            });
            return false;
        });
    } );   
        
    function backToUserOverview(){
        var data = {
            secSession: phpSessionID,
            module: "AjaxAdministration",
            submodule: "IMBAdminModules",
            ajaxmethod: "viewUsers"
        };
        loadImbaAdminTabContent(data);
    }
</script>
<form id="ImbaAjaxUsersViewprofileUserprofileForm" action="post">
    <table class="ImbaAjaxBlindTable" style="cellspacing: 1px;">
        <tbody>
            <tr>
                <td>OpenID:</td>
                <td colspan="2">{$openid}</td>
            </tr>
            <tr>
                <td>Firstname:</td>
                <td><input id="myProfileFirstname" type="text" name="firstname" value="{$firstname}" /></td>
                <td rowspan="13">&nbsp;</td>
            </tr>
            <tr>
                <td>Lastname:</td>
                <td><input id="myProfileLastname" type="text" name="lastname" value="{$lastname}" /></td>
            </tr>
            <tr>
                <td><nobr>Aktuelles Motto:</nobr></td>
        <td><input id="myProfileMotto" type="text" name="motto" value="{$motto}" /></td>
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
            <td><input id="myProfileNickname" type="text" name="nickname" value="{$nickname}" /></td>
        </tr>
        <tr>
            <td>Birthday</td>
            <td><input id="myProfileBirthday" type="text" name="birthday" value="{$birthday}" /></td>
        </tr>
        <tr>
            <td>Email:</td>
            <td><input id="myProfileEmail" type="text" name="email" value="{$email}" /></td>
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
            <td colspan="2"><textarea id="myProfileSignature" name="signature" rows="4" cols="50">{$signature}</textarea></td>
        </tr>
        <tr>
            <td>Rolle</td>
            <td colspan="2">
                <select id="myProfileRole">
                    {foreach $allroles as $current_role}
                    <option value="{$current_role.role}" {if $current_role.role == $role}selected{/if}>{$current_role.name}</option>
                    {/foreach}
                </select>
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan="2"><input id="ImbaAjaxUsersViewprofileUserprofileFormSubmit" type="submit" value="Speichern" /></td>
        </tr>
        </tbody>
    </table>
</form>
<br />
<a id="ImbaAdminViewEditUserBack" href="javascript:void(0)" onclick="javascript: backToUserOverview();">Back to User Overview</a>