{*
    Basic div construct which will be injected into the website
    
    Attention! You can NOT use " without escaping them with \"
    use ' instead.

*}{strip}
    <div id='imbaMenu' class='ui-widget-content ui-corner-all'></div>
    <div id='imbaSsoLoginBorder' class='ui-widget-content ui-corner-all'>
    <div id='imbaSsoLogin'>
        <img id='imbaSsoLogoImage' src='{$thrustRoot}Images/noicon.png' alt='Guild Logo' title='Show/Hide Menu' />
        <div id='imbaSsoLoginInner'>
            <form id='imbaSsoLoginForm' action='{$thrustRoot}{$authPath}' method='post'>
                <input id='imbaSsoOpenId' name='openid' type='hidden' />
                <input name='authMethod' type='hidden' value='password' />
                <input id='imbaSsoNickname' name='nickname' type='text' />
                <input id='imbaSsoPassword' name='password' type='password' style='display: none;' />
                <input id='imbaSsoOpenIdLoginReferer' name='imbaSsoOpenIdLoginReferer' value='' type='hidden' />
                <br />
                <span id='imbaSsoOpenIdSubmit'>Login / Registrieren</span>
                <!-- <input id='imbaSsoOpenIdSubmit' type='submit' value='Login / Registrieren' /> -->
            </form>
            <form id='imbaSsoLogoutForm' action='{$thrustRoot}{$authPath}' method='post'>
                <span id='imbaSsoShowNickname' onclick='loadImbaAdminModule(\"AjaxUser\", \"viewEditMyProfile\");'></span>
                <input id='imbaSsoOpenIdLogoutReferer' name='imbaSsoOpenIdLogoutReferer' value='' type='hidden' />
                <input name='logout' value='true' type='hidden' />
                <br />
                <span id='imbaSsoOpenIdSubmitLogout'>Logout</span>
                <!-- <input id='imbaSsoOpenIdSubmitLogout' type='submit' value='Logout' /> -->
            </form>
        </div>
        <div style='clear: both;' ></div>
        <div id='imbaUsersOnline' ></div>
    </div>
</div>
<div id='imbaMessagesDialog' title='IMBA Messaging' style='padding: 3px;'>
    <div id='imbaMessages'>
        <ul></ul>
        <form action='' method='post'>
            <div id='imbaChatConversation' style='float: left; width: 80%; overflow: auto;'></div>
            <div id='imbaChatConversationUserlist' style='float: right; width: 19%; overflow: auto;'></div>
            <div style='clear: both;' ></div>
            <div id='imbaChatConversationMessage'>
                Message: <input id='imbaMessageText' type='text' autocomplete='off' style='width: 70%;' /> &nbsp; <input id='imbaMessageTextSubmit' type='submit' value='Send'/>
            </div>
        </form>
    </div>
</div>
<div id='imbaContentDialog' title='IMBAdmin' style='padding: 3px;'>
    <div id='imbaContentNav' style='height: 98%; overflow: auto;'>
        <ul></ul>
    </div>
</div>
<div id='imbaPasswordPrompt' title='Passwort eingeben:' />
    <input id='imbaPasswordPromptInput' type='password' name='imbaPasswordPromptInput' />
</div>
{/strip}