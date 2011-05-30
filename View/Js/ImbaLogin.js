/**
 * Javascript for handling all the login stuff
 * - am i logged in
 * - who is logged in next to me
 */
// Storring if user is logged in
var isUserLoggedIn = false;
var isSystemInErrorState = false;
var currentModule = null;
var currentGame = null;
var currentGameDo = null;
var currentUserName = null;
var currentUserOpenid = null;

// Reload Online Users every 10000 ms
setInterval('refreshUsersOnline()', 10000);

// Test if user is online, if then show chat, else hide
$(document).ready(function() {
    $.ajaxSetup({
        async: true
    });

    var tmpURL = window.location.toString().split("/");
    var tmpURL2 = "";
    for (var i=0; i<(tmpURL.length-1); i++) {
        tmpURL2 += tmpURL[i] + "/";
    }


    $("#imbaSsoOpenIdSubmit").button();
    $("#imbaSsoOpenIdSubmit").click(function () {
        if ($("#imbaSsoOpenId").val() == "") {
            loadImbaAdminDefaultModule();
            return true;
        } else {
            hideMenu();
            $.jGrowl('Betrete das System...', {
                header: 'Knock, Knock, Neo!'
            });
            $("#imbaSsoOpenIdLoginReferer").attr('value', tmpURL2);
            $("#imbaSsoLoginForm").submit();
            return true;
        }
    });
    $("#imbaSsoOpenIdSubmitLogout").button();
    $("#imbaSsoOpenIdSubmitLogout").click(function () {
        hideMenu();
        $.jGrowl('Verlasse das System...', {
            header: 'Knock, Knock, Neo!'
        });
        $("#imbaSsoOpenIdLogoutReferer").attr('value', tmpURL2);
        $("#imbaSsoLogoutForm").submit();
        return true;
    });
    $("#imbaMessageTextSubmit").button();

    // setting old openid
    var oldOpenId = unescape(decodeURIComponent(readCookie("ImbaSsoLastLoginName")));
    if (oldOpenId != null && oldOpenId != "null" && oldOpenId != ""){
        $("#imbaSsoOpenId").val(oldOpenId);
    }

    // Checking if user is online
    $.post(ajaxEntry, {
        secSession: phpSessionID,
        module: "AjaxUser",
        submodule: "IMBAdminModules",
        ajaxmethod: "getCurrentUserStatus"
    }, function (response){
        if (checkReturn(response) == false) {
            if (response == "Need to register") {
                setLoggedIn(false);
                loadImbaAdminDefaultModule();
            } else if (response == "Not logged in"){
                setLoggedIn(false);
            } else {
                setLoggedIn(true);
                $("#imbaSsoShowNickname").html('Hallo ' + response);

                // Firsttime show users online
                refreshUsersOnline();
            }
        }
    });

    /**
     * Menu
     */
    $("ul.subnav").parent().append("<span></span>");
    $("ul.topnav li span").click(function() {
        var subNav = $(this).parent().find("ul.subnav");
        if (subNav.is(":hidden")){
            subNav.slideDown('fast').show();
        }else {
            subNav.slideUp('fast').show();
        }
    });

    $("ul.topnav li ul li").click(function() {
        var subNav = $(this).parent();
        if (subNav.is(":hidden")){
            subNav.slideDown('fast').show();
        }else {
            subNav.slideUp('fast').show();
        }
    });


    var menuIsThere = true;
    $("#imbaSsoLogoImage").click(function() {
        if (!menuIsThere){
            showMenu();
            menuIsThere = true;
        }
        else {
            hideMenu();
            menuIsThere = false;
        }

        return false;
    });


    /*
     * ImbAdmin Window Tabs Module
     */
    // Setting up the content of the Dialog as tabs
    $("#imbaContentNav").tabs().bind("tabsselect", function(event, ui) {
        var tmpModuleTabId = "";
        $.each($("#imbaContentNav a"), function (k, v) {
            if (k == ui.index){
                var moduleTmp = v.toString().split("#");

                tmpModuleTabId = "#" + moduleTmp[1];

                var data = {
                    secSession: phpSessionID,
                    module: currentModule,
                    ajaxmethod: moduleTmp[1]
                };
                loadImbaAdminTabContent(data, tmpModuleTabId);
            }
        });
    });

    /*
     * ImbaGame Window Tabs Module
     */
    // Setting up the content of the Dialog as tabs
    $("#imbaGameNav").tabs().bind("tabsselect", function(event, ui) {
        var tmpGameTabId = "";
        $.each($("#imbaGameNav a"), function (k, v) {
            if (k == ui.index){
                var gameTmp = v.toString().split("#");

                tmpGameTabId = "#" + gameTmp[1];

                var data = {
                    action: "game",
                    game: currentGame,
                    gameDo: currentGameDo,
                    secSession: phpSessionID,
                    request: gameTmp[1]
                };
                loadImbaGameTabContent(data, tmpGameTabId);
            }
        });
    });

    /**
     * Setting up the Dialog for the ImbaAdmin
     */
    $("#imbaContentDialog").dialog({
        autoOpen: false
    })
    .dialog("option", "width", 700)
    .dialog("option", "height", 600);

    /**
     * Setting up the Dialog for the IMBAGames
     */
    $("#imbaGameDialog").dialog({
        autoOpen: false
    })
    .dialog("option", "width", 800)
    .dialog("option", "height", 700);

    /**
     * Load current active Portal
     */
    loadImbaPortal(-1);

    //Display potential Error Message
    if (imbaAuthReferer.length > 0) {
        $("#imbaSsoLoginInner").hide();
        $("#imbaUsersOnline").hide();
        $("#window").location(imbaAuthReferer);
        $.jGrowl(imbaErrorMessage, {
            header: 'Browser Weiterleitung:',
            life: 2000
        });
    } else if (imbaErrorMessage.length > 0) {
        $.jGrowl(imbaErrorMessage, {
            header: 'Information von vorher:',
            life: 1000
        });
    }
});

/**
 * refreshing the users online tag cloud
 */
function refreshUsersOnline(){
    if (isUserLoggedIn){
        $.post(ajaxEntry, {
            secSession: phpSessionID,
            module: "AjaxUser",
            submodule: "IMBAdminModules",
            ajaxmethod: "loadUsersOnline"
        }, function (response){
            //create list for tag links
            $("#imbaUsersOnline").html("");
            $("<ul>").attr("id", "imbaUsersOnlineTagList").appendTo("#imbaUsersOnline");

            //create tags
            $.each($.parseJSON(response), function(key, value){
                //create item
                var li = $("<li>");
                li.text(value.name);
                li.appendTo("#imbaUsersOnlineTagList");
                li.css("fontSize", value.fontsize);
                li.css("color", value.color);
                li.attr("title", "Start Chat with " + value.name);

                li.click(function (){
                    //createChatWindow(value.name, value.id);
                    createTab(value.name, value.id, "1");
                });

            });
        });
    }
}

/**
 * Sets the user loggedin
 */
function setLoggedIn(isLoggedIn){
    if (isLoggedIn){
        $("#imbaSsoLoginForm").hide();
        $("#imbaSsoLogoutForm").show();
        $("#imbaOpenMessaging").show();
    } else {
        $("#imbaSsoLoginForm").show();
        $("#imbaSsoLogoutForm").hide();
        $("#imbaOpenMessaging").hide();
    }

    isUserLoggedIn = isLoggedIn;
}

/**
 * Sets the system in error state
 */
function checkReturn(returnData){
    if (imbaJsDebug == 'false') {
        if (returnData.substring(0,6) == "Error:") {
            isSystemInErrorState = true;
            setLoggedIn(false);
            $("#imbaSsoLoginInner").hide();
            $("#imbaUsersOnline").hide();
            $.jGrowl(returnData.substring(6), {
                header: 'Error',
                life: 200
            });
            return true;
        }
        return false;
    } else {
        if (returnData.substring(0,6) == "Error:") {
            $.jGrowl(returnData.substring(6), {
                header: 'Error',
                life: 4000,
                sticky: true
            });
            return true;
        } else if (returnData.length == 0) {
            $.jGrowl("Keine Daten erhalten", {
                header: 'Warning',
                life: 1000
            });
            return false;
        } else {
            $.jGrowl("Daten geladen:<br />" + returnData, {
                header: 'Info',
                life: 2000
            });
        }
        return false;
    }
}
/**
 * Shows the Menu and stuff around
 */
function showMenu() {
    // run the effect
    $("#imbaMenu").show("slide", {
        direction: "right"
    });

    if (isSystemInErrorState == false) {
        $("#imbaSsoLoginInner").show("slide", {
            direction: "right"
        });

        $("#imbaUsersOnline").show("slide", {
            direction: "up"
        });
    }
}

/**
 * Hids the Menu and stuff around
 */
function hideMenu() {
    // run the effect
    $("#imbaMenu").hide("slide", {
        direction: "right"
    });

    if (isSystemInErrorState == false) {
        $("#imbaSsoLoginInner").hide("slide", {
            direction: "right"
        });

        $("#imbaUsersOnline").hide("slide", {
            direction: "up"
        });
    }
}

/**
 * Sets the current portal
 */
function loadImbaPortal(portalId) {
    $.post(ajaxEntry, {
        secSession: phpSessionID,
        module: "AjaxPortal",
        ajaxmethod: "getPortal",
        params: JSON.stringify({
            "id": portalId
        })
    }, function (response){
        if (checkReturn(response) == false) {
            var currentPortal = $.parseJSON(response);
            if (portalId != null) {
                $.jGrowl('<img src="' + currentPortal.icon + '" style="width: 24px; height: 24px; vertical-align: middle; padding: 3px;" /> <big>' + currentPortal.name + '</big>', {
                    life: 350,
                    header: 'Portal geladen:<br /><br />'
                });
            }
            $("#imbaNavigationPortal").html(currentPortal.navigation);
            $("#imbaSsoLogoImage").attr('src', currentPortal.icon);
            $("#document").attr('title', currentPortal.name + ' Portal');

            // Send auth post
            if (isUserLoggedIn) {
                if (currentPortal.portalauth != ""){
                    $.jGrowl("Starte Auth nach: " + currentPortal.portalauth);
                    $.post(currentPortal.portalauth, function(data) {
                        if (data != "") $.jGrowl(data);
                        else $.jGrowl("bereits angemeldet");
                    });
                }
            }
        }
    });
}