<script type="text/javascript">
    $(document).ready(function() {
        $('#ImbaAjaxUsersOverviewTable').dataTable( {
            "iDisplayLength": 16,
            "bFilter": true,
            "sPaginationType": "two_button",
            "bJQueryUI": true,
            "bLengthChange": false
        } );
    } );   
    
    function loadUserProfile(userid){
        var data = {
            secSession: phpSessionID,
            module: "AjaxUser",
            ajaxmethod: "viewUserProfile",
            params: JSON.stringify({ "id": userid })
        };
        loadImbaAdminTabContent(data);
    }
   
</script>
<table id="ImbaAjaxUsersOverviewTable" class="dataTableDisplay">
    <thead>
        <tr><th>Nickname</th><th>Zuletzt Online</th><th>Jabber</th><th>Games</th></tr>
    </thead>
    <tbody>

        {foreach $susers as $user}
        <tr onclick="javascript: loadUserProfile('{$user.id}');">
            <td>{$user.nickname}</td>
            <td>{$user.lastonline}</td>
            <td>{$user.jabber}</td>
            <td>{$user.games}</td>
        </tr>
        {/foreach}

    </tbody>
</table>