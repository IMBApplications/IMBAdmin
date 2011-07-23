<style type="text/css">
    #ImbaContentClickable {
        padding: 3px;
        margin: 3px;
        border: 2px grey solid;
        cursor: pointer;
        height: 105px; 
        width: 305px;
        float: left;
        padding: 3px;
        text-align: center;
        /*        clear: both; */
    }
    #ImbaContentClickable:hover {
        border: 2px lightgrey solid;
        background-color: #222222;
    }
</style>
<script>
    $(function() {
        $( ".imbaPortletColumn" ).sortable({
            connectWith: ".imbaPortletColumn",
            tolerance: "pointer",
            axis: 'y'
        });

        $( ".imbaPortlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
        .find( ".imbaPortlet-header" )
        .addClass( "ui-widget-header ui-corner-all" )
        .prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
        .end()
        .find( ".imbaPortlet-content" );

        $( ".imbaPortlet-header .ui-icon" ).click(function() {
            $( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
            $( this ).parents( ".imbaPortlet:first" ).find( ".imbaPortlet-content" ).toggle();
        });

        $( ".imbaPortletColumn" ).disableSelection();
    });
    
    $(function($) {
        $('.jclock').jclock();
    });
</script>
<div>
    <div class="imbaTitle" style="float: left; width: 380px;">
        <b>Hallo {$nickname}.</b><br />
        <i>Du befindest dich auf {$niceDomain}.</i><br />
        <i>Heute ist der {$today} um <span class="jclock"></span></i>.<br />
        <br />
        <ul>
            {foreach $navs as $nav}
            <li>
                <a href="javascript:void();"  onclick="javascript: loadImbaAdminModule('{$nav.module}');" title="{$nav.comment}">{$nav.name}</a>
            </li>
            {/foreach}
        </ul>
    </div>
    <div class="imbaTitle" style="float: left; with: 230px;">
        <ul id="imbaPortlet" style="list-style-type: none; text-indent: 0px;">
            <i>&#8222; {$tip} &#8221;</i>
            <br />
            <br />
            <li>
                <script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>
            <g:plusone></g:plusone><br />
            <iframe src="http://www.facebook.com/plugins/like.php?href={$thrustRoot}&amp;send=true&amp;layout=button_count&amp;width=190&amp;show_faces=false&amp;action=like&amp;colorscheme=dark" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:190px; height:30px;" allowTransparency="true"></iframe>
            </li>
            {foreach $portlets as $portlet}
            <li>
                <div class="imbaPortletColumn" style="float: left;">
                    <div class="imbaPortlet">
                        <div class="imbaPortlet-header">{$portlet.name}</div>
                        <div class="imbaPortlet-content">{$portlet.content}</div>
                    </div>
                </div>
            </li>
            {/foreach}
            {*
            <li>
                <div class="imbaPortletColumn" style="float: left;">
                    <div class="imbaPortlet">
                        <div class="imbaPortlet-header">Navigation</div>
                        <div class="imbaPortlet-content">
                            {foreach $navs as $nav}
                            <a href="javascript:void();"  onclick="javascript: loadImbaAdminModule('{$nav.module}');" title="{$nav.comment}">{$nav.name}</a><br />
                            {/foreach}
                        </div>
                    </div>
                </div>
            </li>
            *}
        </ul>
    </div>
</div>
