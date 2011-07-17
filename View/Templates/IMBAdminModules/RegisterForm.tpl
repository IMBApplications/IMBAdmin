<script type="text/javascript" src="http://www.google.com/recaptcha/api/js/recaptcha_ajax.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // set the datepicker
        $("#regBirthday").datepicker({ 
            dateFormat: 'dd.mm.yy', 
            changeMonth: true,
            changeYear: true });
        
        $("#ImbaAjaxCancleRegistration").button();
        $("#ImbaAjaxCheckCaptcha").button();
        $("#ImbaAjaxCancleCaptchaShowhelp").button();
        $("#ImbaAjaxStep2").button();
        $("#imbaSsoRegisterForm").validate({
            submitHandler: function(form) {
                step2();
            }
        });
        
    });
    
    function createCaptcha () {
        Recaptcha.create("{$publicKey}", "ImbaReCaptcha", {
            theme: "blackglass",
            callback: Recaptcha.focus_response_field
        });
    }
  
    function cancleRegistration(){
        window.location.replace('{$authPath}?logout=true');
    };

    function step2 () {
        /**
         * Check if all needed forms are filled out
         * -->> http://docs.jquery.com/Plugins/validation
         */
        
        /**
         * check the Passwords
         */
        if ($("#regPassword1").val() == "") {
            $.jGrowl('Leeres Passwort angegeben!', { header: 'Error' });
            return false;
        }
        if ($("#regPassword1").val() != $("#regPassword2").val()) {
            $.jGrowl('Die beiden Passw&ouml;rter sind nicht identisch!', { header: 'Error' });
            return false;
        }
        $.post(ajaxEntry, {
            secSession: phpSessionID,
            module: "AjaxRegistration",
            submodule: "IMBAdminModules",
            ajaxmethod: "checkPassword",
            params: JSON.stringify({
                "password" : $("#regPassword1").val()
            })
        }, function(response){
                
            if (response != "Ok"){
                // $.jGrowl('Deine Eingabe war nicht richtig!', { header: 'Error' });
                $.jGrowl(response, { header: 'Error' });
                return false;
            } else {
                $("#ImbaReCaptchaContainer").show();
                $("#ImbaRegisterForm").hide();
                createCaptcha();
                return false;
            }   
        });

        /**
         * Hide the form and show the captcha div
         */

    };

    function checkCaptcha () {
        $.post(ajaxEntry, {
            secSession: phpSessionID,
            module: "AjaxRegistration",
            submodule: "IMBAdminModules",
            ajaxmethod: "checkCaptchaForRegistration",
            params: JSON.stringify({
                "challenge" : $("#recaptcha_challenge_field").val(),
                "answer" : $("#recaptcha_response_field").val(),
                "firstname" : $("#regFirstname").val(),
                "lastname" : $("#regLastname").val(),
                "birthday" : $("#regBirthday").val(),
                "sex" : $("input[name='regSex']:checked").val(), 
                "nickname" : $("#regNickname").val(),
                "email" : $("#regEmail").val(),
                "password" : $("#regPassword1").val()
            })
        }, function(response){
                
            if (response != "Ok"){
                // $.jGrowl('Deine Eingabe war nicht richtig!', { header: 'Error' });
                $.jGrowl(response, { header: 'Error' });
                Recaptcha.destroy();
                createCaptcha();
            } else {
                $.jGrowl('Deine Registrierung wurden gespeichert. Checke deine Emails!', { header: 'Erfolg' });
    
                var data = {
                    module: "Register",
                    request: "registerme",
                    secSession: phpSessionID
                };
                loadImbaAdminTabContent(data);
            }   
        });
    };
    
</script>
<table class="ImbaAjaxBlindTable" style="width: 100%; overflow: auto;">
    <tr>
        <td><h2>Registrierung eines neuen Mitgliedes</h2></td>
        <td style="text-align: right;"><a id="ImbaAjaxCancleRegistration" href="javascript:void(0)" onclick="javascript: cancleRegistration();">Abbrechen</a></td>
    </tr>
</table>
<div id="ImbaRegisterForm">
    <form id='imbaSsoRegisterForm' action='' class='cmxform' method='post'>

        <!--    <table class="ImbaAjaxBlindTable" style="cellspacing: 1px;"> -->

        <b>Bitte f&uuml;lle folgende Felder wahrheitsgetreu aus.</b><br />
        Deine Pers&ouml;hnlichen Informationen werden nicht f&uuml;r unlautere Zwecke verwendet oder weitergegeben. Lediglich dein Nickname, Vorname und der erste Buchstabe des Nachnahmens sind für andere Benutzer sichtbar. Aus Hans Muster wird Hans M.<br />
        <table class="ImbaAjaxBlindTable" style="width: 100%; overflow: auto;">
            <tr>
                <td>Vorname:</td>
                <td><input id="regFirstname" type="text" name="forename" title="* Muss ausgef&uuml;llt werden" class="required" minlength="3" /></td>
            </tr>
            <tr>
                <td>Nachname:</td>
                <td><input id="regLastname" type="text" name="surname" title="* Muss ausgef&uuml;llt werden"  class="required" minlength="3" /></td>
            </tr>
            <tr>
                <td>Geburtsdatum:</td>
                <td><input id="regBirthday" class="regField" type="text" title="* Muss ausgef&uuml;llt werden" name="birthdate" /></td>
            </tr>
            <tr>
                <td>Geschlecht</td>
                <td><img src="Images/female.png" title="Weiblich"><input class="regField" style="width:16px;" type="radio" name="regSex" value="F">
                    <img src="Images/male.png" title="M&auml;nnlich"><input class="regField" type="radio" style="width:16px;" name="regSex" value="M"></td>
            </tr>
            <tr>
                <td>Nickname:</td>
                <td><input id="regNickname" type="text" name="nickname" title="* Muss ausgef&uuml;llt werden" class="required" minlength="3"></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td><input id="regEmail"  class="required email" type="text" name="email" title="* Muss ausgef&uuml;llt werden"></td>
            </tr>
            <tr>
                <td>Passwort:</td>
                <td><input id="regPassword1" type="password" name="password1"  title="* Muss ausgef&uuml;llt werden" class="required" minlength="8"></td>
            </tr>
            <tr>
                <td>Nocheinmal:</td>
                <td><input id="regPassword2" type="password" name="password2"  title="* Muss ausgef&uuml;llt werden" class="required" minlength="8"></td>
            </tr>
        </table>
        <br />
        <b>Die Community Regeln:</b><br />
        <textarea id="regRules" class="regRules" name="regRules" readonly="readonly" style="border:0px; width: 100%; overflow: auto;" rows="9">{fetch file='View/Templates/IMBAdminModules/RegisterCommunityRules.tpl'}</textarea>
        <table class="ImbaAjaxBlindTable" style="width: 100%; overflow: auto;">
            <tr>
                <td><input id="regCheckrules" onClick="javascript:$('regRules').style.border = '0px';" class="regField" type="checkbox" class="required" name="rulesaccepted" style="width:16px;" title="*"> Ich habe die allgemeinen Community Regeln gelesen und werde mich an sie halten.</td>
                <td style="text-align: right;"><input id="ImbaAjaxStep2" type="submit" value="Weiter" /></td>
            </tr>
        </table>
    </form>
</div>
<div id="ImbaReCaptchaContainer" style="display: none;">
    <b>Bitte beweise, dass du aus Fleisch und Blut bist.</b><br />
    Mit dem Abschreiben der Zeichen unten auf dem Bild zeigst du uns, dass du wirklich ein Mensch bist und kein Computerprogramm.<br />
    Zus&auml;tzlich hilfst du so, alte Dokumente und B&uuml;cher f&uuml;r die Nachwelt zu digitalisieren.
    <br />
    <br />
    <div id="ImbaReCaptcha"></div>
    <br />
    <a id="ImbaAjaxCheckCaptcha" href="javascript:void(0)" onclick="javascript: checkCaptcha();">Eingabe &Uuml;berpr&uuml;fen</a>
    <a id="ImbaAjaxCancleCaptchaShowhelp" href="javascript:void(0)" onclick="javascript: Recaptcha.showhelp();">Hilfe! was ist das?</a>
</div>