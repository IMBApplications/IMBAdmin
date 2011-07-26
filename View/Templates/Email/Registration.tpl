<html>
    <body>
        <h2>Hallo {$nickname}</h2>
        Du hast dich auf <a href="{$url}">{$url}</a> registriert. Um die Registrierung<br />
        abzuschliessen, rufe bitte folgende Website auf:<br />
        <br />
        <a href="{$url}/IMBAdmin/ImbaAuth.php?unlock=true?key={$lockKey}">{$url}/IMBAdmin/ImbaAuth.php?unlock=true?key={$lockKey}</a><br />
        <br />
        Freundliche Gruesse<br />
        <i>{$adminemailname}</i>
    </body>
</html>