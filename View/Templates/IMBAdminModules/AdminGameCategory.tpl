<script type="text/javascript">
    $(document).ready(function() {
        // Init DataTable
        var oTable = $('#ImbaAjaxAdminCategoriesTable').dataTable( {
            "iDisplayLength": 16,
            "bFilter": true,
            "sPaginationType": "two_button",
            "bJQueryUI": true,
            "bLengthChange": false
        } );
	
        // Apply the jEditable handlers to the table
        $("td[editable|='true']", oTable.fnGetNodes()).editable(ajaxEntry, {
            "callback": function( sValue, y ) {
                var aPos = oTable.fnGetPosition( this );
                oTable.fnUpdate( sValue, aPos[0], aPos[1] );
            },
            "submitdata": function ( value, settings ) {
                return {
                    secSession: phpSessionID,
                    module: "AjaxAdministration",
                    submodule: "IMBAdminModules",
                    ajaxmethod: "updateGameCategory",
                    params: JSON.stringify({
                        "categoryid": this.parentNode.getAttribute('id').substr(11),
                        "categorycolumn": getColumnHeadByIndex("ImbaAjaxAdminCategoriesTable", oTable.fnGetPosition(this)[2])                 
                    })
                };
            },
            "height": "14px"
        } );
        
        $("#ImbaAjaxAdminCategoriesTable tr td span").click(function(){
            if(confirm("Soll die Kategorie wirklich geloescht werden?")){                
                $.post(ajaxEntry, {
                    secSession: phpSessionID,
                    module: "AjaxAdministration",
                    submodule: "IMBAdminModules",
                    ajaxmethod: "deleteGameCategory",
                    params: JSON.stringify({
                        "categoryid": this.parentNode.parentNode.getAttribute('id').substr(11)
                    })
                });
                
                var data = {
                    secSession: phpSessionID,
                    module: "AjaxAdministration",
                    submodule: "IMBAdminModules",
                    ajaxmethod: "viewGameCategory"
                };
                loadImbaAdminTabContent(data);
            }            
        });

        $("#ImbaAddCategoryOK").click( function() {
            if (ImbaAddCategoryName.value.valueOf() != "") {
                $.post(ajaxEntry, {
                    secSession: phpSessionID,
                    module: "AjaxAdministration",
                    submodule: "IMBAdminModules",
                    ajaxmethod: "addGameCategory",
                    params: JSON.stringify({
                        "name": ImbaAddCategoryName.value.valueOf()
                    })
                });

                var data = {
                    secSession: phpSessionID,
                    module: "AjaxAdministration",
                    submodule: "IMBAdminModules",
                    ajaxmethod: "viewGameCategory"
                };
                loadImbaAdminTabContent(data);
                
            } else {
                alert('Please fill out all the fields');                
            }                
        });
        
    } );  
</script>
<table id="ImbaAjaxAdminCategoriesTable" class="dataTableDisplay">
    <thead>
        <tr>
            <th title="Name">Name</th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>

        {foreach $categories as $category}
        <tr id="categoryid_{$category.id}">
            <td editable="true">{$category.name}</td>
            <td editable="false" class="ui-state-error"><span class="ui-icon ui-icon-closethick">X</span></td>
        </tr>
        {/foreach}
    </tbody>
    <tfoot>
        <tr>
            <td><input id="ImbaAddCategoryName" type="text" style="width: 100%; overflow: auto; height: 24px;"></td>
            <td id="ImbaAddCategoryOK" style="cursor: pointer;"><b>OK</b></td>
        </tr>
    </tfoot>
</table>