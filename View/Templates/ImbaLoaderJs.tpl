{strip}
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
{*
    https://www.google.com/jsapi?key=ABQIAAAAoQTlyGjpddJMRStim8sqRhQxz0QRO-TlIxZa0SvHmJFa3dfcpRRN2udTElyK7Do69DwsRVUv_NxrjA
    http://maps.googleapis.com/maps/api/js?sensor=true&language=de&region=CH
    google.load("jquery", "1.6.2");google.load("jqueryui", "1.8.14");

document.write('<script type="text/javascript" src="https://www.google.com/jsapi?key=ABQIAAAAoQTlyGjpddJMRStim8sqRhQxz0QRO-TlIxZa0SvHmJFa3dfcpRRN2udTElyK7Do69DwsRVUv_NxrjA"></script>');
document.write('<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=true&language=de&region=CH"></script>');
document.write('<script type="text/javascript">google.load(\'jquery\', \'1.6.2\');google.load(\'jqueryui\', \'1.8.14\');</script>');

{fetch file='http://maps.googleapis.com/maps/api/js?sensor=true&language=de&region=CH'}
google.load('jquery', '1.5.2');google.load('jqueryui', '1.8.14');

var headID = document.getElementsByTagName('head')[0];         
var newScript = document.createElement('script');
newScript.type = 'text/javascript';
newScript.html = 'google.load(\"jquery\", \"1.6.2\");google.load(\"jqueryui\", \"1.8.14\");';
headID.appendChild(newScript);
*}

{fetch file='Libs/jQuery/js/jquery-1.5.2.min.js'}
{fetch file='Libs/jquery-ui/ui/minified/jquery-ui.min.js'}

{fetch file='Libs/DataTables/media/js/jquery.dataTables.min.js'}
{fetch file='Libs/jquery_jeditable/jquery.jeditable.js'}
{fetch file='Libs/jgrowl/jquery.jgrowl_compressed.js'}
{fetch file='Libs/jquery_jqclock/format_date.js'}
{fetch file='Libs/jquery_jqclock/jqclock_2.2.0.js'}
{fetch file='Libs/jquery_validation/jquery.validate.min.js'}
{*

include imba javascript files

*}
{fetch file='View/Js/ImbaBaseMethods.js'}
{fetch file='View/Js/ImbaLogin.js'}
{fetch file='View/Js/ImbaAdmin.js'}
{fetch file='View/Js/ImbaGame.js'}
{fetch file='View/Js/ImbaMessaging.js'}
{fetch file='View/Js/ImbaMenu.js'}
{*

fill our imbaAdminContainerWorld container with ImbaIndex.tpl

*}
imbaHtmlContent = "<div id='imbaAdminContainerWorld'>{include file="ImbaLoaderDivConstruct.tpl"}</div>";
{*

and inject it into the page

*}
document.write(imbaHtmlContent);
{/strip}