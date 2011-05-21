{* 

passing down our javascript vars 

*}
var ajaxEntry = '{$ajaxPath}';
var phpSessionID = '{$phpSessionID}';
var imbaJsDebug = '{$jsDebug}';
var imbaErrorMessage = '{$imbaErrorMessage}';
var imbaAuthReferer = '{$imbaAuthReferer}';
{* 

include library javascript files

*}
{fetch file='Libs/json2/json2.js'}
{fetch file='Libs/jQuery/js/jquery-1.5.2.min.js'}
{fetch file='Libs/jQuery/js/jquery-ui-1.8.10.custom.min.js'}
{fetch file='Libs/DataTables/media/js/jquery.dataTables.min.js'}
{fetch file='Libs/jquery_jeditable/jquery.jeditable.js'}
{fetch file='Libs/jgrowl/jquery.jgrowl_compressed.js'}
{fetch file='Libs/jquery_jqclock/format_date.js'}
{fetch file='Libs/jquery_jqclock/jqclock_2.2.0.js'}
{fetch file='Libs/fg.menu/Js/fg.menu.js'}
{* 

include imba javascript files

*}
{fetch file='View/Js/ImbaBaseMethods.js'}
{fetch file='View/Js/ImbaLogin.js'}
{fetch file='View/Js/ImbaAdmin.js'}
{fetch file='View/Js/ImbaGame.js'}
{fetch file='View/Js/ImbaMessaging.js'}
{* 

fill our imbaAdminContainerWorld container with ImbaIndex.tpl

*}
imbaHtmlContent = "<div id='imbaAdminContainerWorld'><div id='imbaMenu'><ul class='topnav'> \
            {strip}
            {$PortalNavigation}
            {$ImbaAdminNavigation}
            {$ImbaGameNavigation}
            {$PortalChooser}
            {/strip}</ul> \
    </div>{include file="ImbaLoaderDivConstruct.tpl"}</div>";
{* 

and inject it into the page

*}
document.write(imbaHtmlContent);