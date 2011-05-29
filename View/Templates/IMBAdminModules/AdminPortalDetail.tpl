<script type="text/javascript">
    $(document).ready(function() {
        // User submits the ImbaAjaxAdminProfileForm
        $("#ImbaAjaxAdminProfileBackToOverview").button();
        $("#ImbaAjaxAdminProfileSave").button();
        $("#ImbaAjaxAdminProfileSave").click(function(){
            // submit the change
            $.post(ajaxEntry, {
                secSession: phpSessionID,
                module: "AjaxAdministration",
                submodule: "IMBAdminModules",
                ajaxmethod: "updatePortal",
                params: JSON.stringify({
                    "portalid": $("#myPortalId").val(),
                    "icon": $("#myPortalIcon").val(),
                    "name": $("#myPortalName").val(),
                    "comment": $("#myPortalComment").val(),
                    "portalentries": $("#myPortalEntries").val(),
                    "portalmodules": $("#myPortalModules").val(),
                    "portalauth": $("#myPortalAuth").val()
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

        // User adds a portal alias
        $("#myPortalAddAliasOK").click(function(){
            if ($("#myPortalAddAlias").val() != ""){
                $.post(ajaxEntry, {
                    secSession: phpSessionID,
                    module: "AjaxAdministration",
                    submodule: "IMBAdminModules",
                    ajaxmethod: "addPortalAlias",
                    params: JSON.stringify({
                        "portalid": $("#myPortalId").val(),
                        "alias": $("#myPortalAddAlias").val()
                    })
                }, function(response){
                    if (response != "Ok"){
                        $.jGrowl(response, { header: 'Error' });
                    } else {
                        $.jGrowl('Daten wurden gespeichert!', { header: 'Erfolg' });
                        reloadPortalDetail();
                    }
                });
            }
        });

        // User delets a portal alias
        $("#myPortal tr td table tr td span").click(function(){
            if(confirm("Soll das Alias wirklich geloescht werden?")){
                $.post(ajaxEntry, {
                    secSession: phpSessionID,
                    module: "AjaxAdministration",
                    submodule: "IMBAdminModules",
                    ajaxmethod: "deletePortalAlias",
                    params: JSON.stringify({
                        "portalid": $("#myPortalId").val(),
                        "alias": $(this).html()
                    })
                }, function(response){
                    if (response != "Ok"){
                        $.jGrowl(response, { header: 'Error' });
                    } else {
                        $.jGrowl('Daten wurden gespeichert!', { header: 'Erfolg' });
                        reloadPortalDetail();
                    }
                });
            }
        });
    } );

    function reloadPortalDetail(){
        var data = {
            secSession: phpSessionID,
            module: "AjaxAdministration",
            submodule: "IMBAdminModules",
            ajaxmethod: "viewPortalDetail",
            params: JSON.stringify({
                "portalid": $("#myPortalId").val()
            })
        };
        loadImbaAdminTabContent(data);
    }

    function backToPortalOverview(){
        var data = {
            secSession: phpSessionID,
            module: "AjaxAdministration",
            submodule: "IMBAdminModules",
            ajaxmethod: "viewPortalOverview"
        };
        loadImbaAdminTabContent(data);
    }

</script>
<form id="ImbaAjaxAdminProfileForm" action="post">
    <input id="myPortalId" type="hidden" name="id" value="{$id}" />
    <table id="myPortal" class="ImbaAjaxBlindTable" style="cellspacing: 1px;">
        <tbody>
            <tr>
                <td>Name</td>
                <td><input id="myPortalName" type="text" name="name" value="{$name}" /></td>
            </tr>
            <tr>
                <td>Icon</td>
                <td><input id="myPortalIcon" type="text" name="icon" value="{$icon}" /></td>
            </tr>
            <tr>
                <td>Comment:</td>
                <td><textarea id="myPortalComment" name="comment" rows="4" cols="50">{$comment}</textarea></td>
            </tr>
            <tr>
                <td>Portal Auth:</td>
                <td><input id="myPortalAuth" type="text" name="icon" value="{$portalauth}" /></td>
            </tr>
            <tr>
                <td style="vertical-align: top;">Aliases</td>
                <td>
                    <table>
                        {foreach $aliases as $alias}
                        <tr>
                            <td>{$alias}</td>
                            <td class="ui-state-error"><span class="ui-icon ui-icon-closethick">{$alias}</span></td>
                        </tr>
                        {/foreach}

                        <tr>
                            <td><input id="myPortalAddAlias" type="text" value="" /></td>
                            <td id="myPortalAddAliasOK" style="cursor: pointer;"><b>OK</b></td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;">Portal Entries</td>
                <td>
                    <select id="myPortalEntries" multiple="true" size="5">
                        {foreach $portalentries as $portalentry}
                        <option value="{$portalentry.id}" {if $portalentry.selected == 'true'}selected{/if} >{$portalentry.name} ({$portalentry.url})</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            <tr>
                <td style="vertical-align: top;">Portal Modules</td>
                <td>
                    <select id="myPortalModules" multiple="true" size="5">
                        {foreach $modules as $module}
                        <option value="{$module.name}" {if $module.selected == 'true'}selected{/if}>{$module.name}</option>
                        {/foreach}
                    </select>
                </td>
            </tr>
            <tr>
                <td><a id="ImbaAjaxAdminProfileBackToOverview" href="javascript:void(0)" onclick="javascript: backToPortalOverview();">Back</a></td>
                <td><input id="ImbaAjaxAdminProfileSave" type="submit" value="Save" /></td>
            </tr>
        </tbody>
    </table>
</form>