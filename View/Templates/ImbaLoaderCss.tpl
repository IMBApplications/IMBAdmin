{strip}
{*

    include static css files

For menu (basic style)
@import url("Libs/jquery-ui/themes/base/jquery-ui.css");

For ui-darkness (hooha style)

*}
@import url("Libs/jQuery/css/ui-darkness/jquery-ui-1.8.14.custom.css");
@import url("Libs/jgrowl/jquery.jgrowl.css");
{*
    include imba css files

*}
{fetch file='View/Css/ImbaBase.css'}
{fetch file='View/Css/ImbaLogin.css'}
{fetch file='View/Css/ImbaDataTable.css'}
{fetch file='View/Css/ImbaAdmin.css'}
{fetch file='View/Css/ImbaMessaging.css'}
{fetch file='View/Css/ImbaMenu.css'}
{/strip}