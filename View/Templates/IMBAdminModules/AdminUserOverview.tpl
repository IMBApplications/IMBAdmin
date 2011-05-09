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
            module: "AjaxAdministration",
            submodule: "IMBAdminModules",
            ajaxmethod: "viewUserDetail",
            params: JSON.stringify({
                "userid" : userid
            })
        };
        loadImbaAdminTabContent(data);
    }
   
</script>
<table id="ImbaAjaxUsersOverviewTable" class="dataTableDisplay">
    <thead>
        <tr><th>Nickname</th><th>Zuletzt Online</th><th>Rolle</th></tr>
    </thead>
    <tbody>

        {foreach $susers as $user}
        <tr onclick="javascript: loadUserProfile('{$user.userid}');"><td>{$user.nickname}</td><td>{$user.lastonline}</td><td>{$user.role}</td></tr>
        {/foreach}

    </tbody>
</table>