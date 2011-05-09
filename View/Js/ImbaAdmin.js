/**
 * loads the ImbaAdmin module in the IMBAdmin window
 */
function loadImbaAdminModule(moduleName, ajaxMethod, params){
    currentModule = moduleName;

    /**
     * fill currentUserName and currentUserOpenid
     */
    loadMyImbaUser();
    
    /**
     * Remove all tabs
     */   
    $("#imbaContentNav").tabs("destroy");  
    
    /**
     * Create new tabs on element
     */
    $("#imbaContentNav").tabs();
    
    /**
     * Set the window title
     */
    $.post(ajaxEntry, {
        module: currentModule,
        submodule: "IMBAdminModules",
        ajaxmethod: "getNavigationData",
        params: JSON.stringify({"datarequest": "name"})
    }, function (response){        
        if (checkReturn(response) == false) {  
            tmpTitle  = "<div onclick='javascript:loadImbaAdminDefaultModule();' style='float: left; cursor: pointer;'>";
            tmpTitle += "<span class='ui-icon ui-icon-home' style='float: left;' /></div>";
            tmpTitle += "<div style='float: left;'>&nbsp;&nbsp;&nbsp;</div>";

            if (currentUserName != null) {
                tmpTitle += "<div style='float: left;'>" + currentUserName + "&nbsp;&nbsp;&nbsp;@&nbsp;&nbsp;&nbsp;</div>";
            }
        
            tmpTitle += "<div style='float: left; cursor: pointer;' onclick='javascript:loadImbaAdminDefaultModule();'>IMBAdmin&nbsp;&nbsp;&nbsp;</div>";

            if (response) {
                tmpTitle += "<div style='float: left;'><span class='ui-icon ui-icon-triangle-1-e' style='float: left;' />&nbsp;&nbsp;&nbsp;</div>";
                tmpTitle += "<div onclick='javascript:loadImbaAdminModule(\"" + moduleName + "\");' style='float: left; cursor: pointer;'>" + response + "</div>";
            }
            $("#imbaContentDialog").dialog({
                title: tmpTitle
            });
        }
    });

    /**
     * get and render tabs
     */
    $.post(ajaxEntry, {        
        module: currentModule,
        submodule: "IMBAdminModules",
        ajaxmethod: "getNavigationData",
        params: JSON.stringify({"datarequest": "nav"})       
    }, function (response){
        $.each($.parseJSON(response), function(key, value){
            $("#imbaContentNav").tabs("add", "#" + value.id, value.name);
            if ((key == 0) && (ajaxMethod == null || ajaxMethod == "")) {
                loadImbaAdminTabContent({
                    module: moduleName,
                    ajaxmethod: value.id,
                    params: params
                });
            } else if ((ajaxMethod != null) && (ajaxMethod != "")) {
                loadImbaAdminTabContent({
                    module: moduleName,
                    ajaxmethod: ajaxMethod,
                    params: params
                });
            }
        });
    });
        
    if (isSystemInErrorState == false) {
        $("#imbaContentDialog").dialog("open");
    }
}

/**
 * loads the ImbaAdminTab content, depending on the data for the post request
 */
function loadImbaAdminTabContent(data, myModuleTabId) {
    var targetModuleIabId = null;
    if (myModuleTabId == null) {
        targetModuleIabId = getImbaAdminTabIdFromTabIndex(getSelectedImbaAdminTabIndex());
    } else {
        targetModuleIabId = myModuleTabId;
    }

    data.submodule = "IMBAdminModules";
    data.secSession = phpSessionID;

    $.post(ajaxEntry, data, function (response){
        if (checkReturn(response) == false) {  
            if (response != ""){
                $(targetModuleIabId).html(response);
            }
        }
    });
}

/**
 * Show the User Profile in IMBAdmin window
 */
function showUserProfile(openid) {
    loadImbaAdminModule("User", "viewprofile", openid);
}

/**
 * Loads the default ImbaAdminTab
 */
function loadImbaAdminDefaultModule(){
    // Get the default module
    $.post(ajaxEntry, {      
        module: "IMBAdminModules",
        submodule: "IMBAdminModules",
        ajaxmethod: "returnDefaultModule"        
    }, function (response){
        if (checkReturn(response) == false) {  
            // Call the loadImbaAdminModule to open the dialog         
            loadImbaAdminModule(response.toString());
        }
    });
}

/**
 * Returns the current selected tab index
 */
function getSelectedImbaAdminTabIndex(){
    return $('#imbaContentNav').tabs('option', 'selected');
}

/**
 * Return the Id of a tab from a tabIndex
 */
function getImbaAdminTabIdFromTabIndex(tabModuleIndex){
    var moduleResult = "";
    $.each($("#imbaContentNav a"), function (k, v) {
        if (k == tabModuleIndex){
            var moduleTmp = v.toString().split("#");
            moduleResult = "#" + moduleTmp[1];
        }
    });

    return moduleResult;
}