<ul>
    
    {foreach $topnavs as $topnav}
    <li><a href="javascript:void(0)" onclick="javascript: loadImbaAdminModule('{$topnav.module}');" title="{$topnav.comment}">{$topnav.name}</a></li>
    <ul>
        {foreach $topnav.subnavs as $subnav}
        <li><a href="javascript:void(0)" onclick="javascript: loadImbaAdminModule('{$subnav.module}', '{$subnav.ajaxmethod}');" title="{$subnav.comment}">{$subnav.name}</a></li>
        {/foreach}
    </ul>   
    {/foreach}
</ul>