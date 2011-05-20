/**
 * The ImbaManagerMessenger is the Controller Javascript for the Frontend for
 * Messenging and chatting
 */

/**
 * Storage, what we store:
 * Tabs:
 * - tabCount           => how many tabs have been opened, since site was loaded
 * - countOpenTabs      => how many tabs are opend right now
 * - tabMessageCache    => caches all messages in Array[n] = channelid / userid
 * - tabUsers           => stores the users in Tab in Array[n] = users
 * - tabMessageSinceId  => what was the last chat message ID in Array[n] = last msg id
 */
var tabCount = 0;
var countOpenTabs = 0;
var tabMessageCache = new Array();
var tabUsers = new Array();
var tabMessageSinceId = new Array();

/**
 * Types of the Tabs
 */
var tab_type_info = "0";
var tab_type_message = "1";
var tab_type_chat = "2";

/**
 * A Tab can store the following informations
 */
var tab_data_type = "tab_data_type";
var tab_data_name = "tab_data_type";
var tab_data_id = "tab_data_type";

/**
 * When Browser is closed => disconnect from all chat channels
 */
$(window).bind('beforeunload', function() {
    //alert("ByeBye");    
    });
    
/**
 * jQuery DOM-Document has been loaded
 */
$(document).ready(function() {
    // Creats the Dialog around the tabs
    $("#imbaMessagesDialog").dialog({
        position:  ['left','bottom'] ,
        autoOpen: false,
        resizable: false,
        height: 270,
        width: 600
    });
    
    // Setting the hights of the chatcontent and userlist
    $("#imbaChatConversation").height(140);
    $("#imbaChatConversationUserlist").height(140);

    // open messaging on click
    $("#imbaOpenMessaging").click(function(){
        $("#imbaMessagesDialog").dialog("open");
    });
    
    // Load the Tabs an inits the Variable for them and create info tab
    $msgTabs = $('#imbaMessages').tabs();
    createInfoTab();
    
    // Setting a Template for the tabs, making them closeable
    $msgTabs.tabs({
        tabTemplate: "<li><a href='#{href}'>#{label}</a><div class='ui-icon ui-icon-info' style='cursor: pointer; float: left;'>Info</div><div class='ui-icon ui-icon-close'>Remove Tab</div></li>"
    });
    
    // Tab selected change Event (Reload content of that chat window
    $msgTabs.bind("tabsselect", function(event, ui) {
        // Load the Content
        loadChatWindowContent(ui.index);

    // Hide the Star
    // showStarChatWindowTitle(getTabIdFromTabIndex(ui.index), false);
    });

    // Close icon: removing the tab on click
    $("#imbaMessages div.ui-icon-close").live("click", function() {
        var index = $("li", $msgTabs).index($(this).parent());
                
        $msgTabs.tabs("remove", index);
        if (countOpenTabs > 0) {
            countOpenTabs--;
        }

        // load content of new selected Tab
        loadChatWindowContent(getSelectedTabIndex());
    });
    
    // info icon: showing the ImbAdmin module
    $("#imbaMessages div.ui-icon-info").live("click", function() {
        alert("Chat is not yet implemented.");
    /*var index = $("li", $msgTabs).index($(this).parent());
        var tabData = getTabDataFromTabIndex(index);
        if (tabData.substr(0, 1) == "#"){
            alert("Chat is not yet implemented.");
        } else {
            showUserProfile(getTabDataFromTabIndex(index));
        }*/
    });
    
    // User submits the textbox
    $("#imbaMessageTextSubmit").click(function(){
        var tabIndex = getSelectedTabIndex();
        var msgText = $("#imbaMessageText").val();

        //sendChatWindowMessage(msgText, tabIndex);
        alert("Nothing was sent!");

        $("#imbaMessageText").attr("value", "");
        return false;
    });
    
    // autocomplete for Chat
    $("#imbaMessageText").autocomplete({
        source: function( request, response ) {
            if (request.term.substr(0,2) == "/w"){
                $.ajax({
                    type: "POST",
                    url: ajaxEntry,
                    dataType: "json",
                    data: {
                        secSession: phpSessionID,
                        module: "AjaxUser",
                        submodule: "IMBAdminModules",
                        ajaxmethod: "loadUsersStartwith",
                        params: JSON.stringify({
                            "startwith": request.term.substr(3 ,request.term.length)
                        })
                    },
                    success: function( data ) {
                        response( $.map( data, function( item ) {
                            return {
                                label: item.name,
                                value: "/w " + item.name,
                                data: item.id,
                                user: item.user
                            }
                        }));

                    }
                });
            }
            else if (request.term.substr(0,2) == "/j") {
                $.ajax({
                    type: "POST",
                    url: ajaxEntry,
                    dataType: "json",
                    data: {
                        secSession: phpSessionID,
                        module: "AjaxMessenger",
                        submodule: "IMBAdminModules",
                        ajaxmethod: "loadChannels"
                    },
                    success: function( data ) {
                        response( $.map( data, function( item ) {
                            return {
                                label: "Join Channel: " + item.channel,
                                value: "/j " + item.channel,
                                data: item.channel,
                                data2: item.channelId,
                                user: item.user
                            }
                        }));

                    }
                });
            }
        },
        minLength: 0,
        select: function( event, ui ) {
            if (ui.item.user == true){
                createTab(ui.item.label, ui.item.data, tab_type_message);
            } else if (ui.item.user == false){
                createTab(ui.item.data, ui.item.data2, tab_type_chat);
            }
        },
        close: function() {
            $("#imbaMessageText").attr("value", "");
        }
    });
});
    
/**
 * create the info tab
 */
function createInfoTab(){
    // Create new Window
    $("#imbaMessages").tabs("add", "#imbaMessagesTab_" + tabCount, "Info");

    $("#imbaMessagesTab_" + tabCount).data(tab_data_type, tab_type_info);
    $("#imbaMessagesTab_" + tabCount).data(tab_data_name, "Info");
    $('#imbaMessages').tabs("select", countOpenTabs);
    
    // Set the content of the info tab
    tabMessageCache.push("\
        <div style='margin-left: 10px'>\
            <p><b>/w</b> &lt;Username&gt; zum Chatten mit einem User</p>\
            <p><b>/j</b> zum Chatten in einem Channel</p>\
        </div>");
    
    // for sync reasons
    tabMessageSinceId.push(-1);    
    tabUsers.push("");
    
    loadChatWindowContent(tabCount);
    
    tabCount++;
}

/**
 * Creats a chatwindow
 */
function createTab(name, data, type) {
    // Run through open chats and check if its not already opend,
    // if so => select that
    var found = false;
    countOpenTabs = 0;

    // Open Dialog, just to be save here
    $("#imbaMessagesDialog").dialog("open");

    // Walk through all the open tabs
    $.each($("#imbaMessages a"), function (k, v) {
        var tmpId = v.toString().split("#");
        var tabData = $("#" + tmpId[1]).data(tab_data_id);

        if (tabData == data) {
            // Select the clicked window
            $('#imbaMessages').tabs("select", k);
            found = true;
        }

        countOpenTabs++;
    });

    if (!found){
        // Create new Window
        $("#imbaMessages").tabs("add", "#imbaMessagesTab_" + tabCount, name);

        $("#imbaMessagesTab_" + tabCount).data(tab_data_id, data);
        $("#imbaMessagesTab_" + tabCount).data(tab_data_name, name);
        $("#imbaMessagesTab_" + tabCount).data(tab_data_type, type);
        
        // load initial
        if (type == tab_type_chat) {
            $.post(ajaxEntry, {
                secSession: phpSessionID,
                module: "AjaxMessenger",
                submodule: "IMBAdminModules",
                ajaxmethod: "initChat",
                params: JSON.stringify({
                    "channelid": data
                })
            },
            function(response) {
                var htmlConversation = "";
                var htmlUsers = "You";
                var responsJSON = $.parseJSON(response);
                    
                $.each(responsJSON.messages, function(key, val) {
                    htmlConversation += "<div>"
                    + val.time + " "
                    + val.nickname + ": "
                    + val.message + "</div>";

                    tabMessageSinceId[countOpenTabs] = val.id;
                });
                
                $.each(responsJSON.users, function(key, val) {
                    htmlUsers += "<br/>" + val;
                });
                
                // Set the content of the info tab
                tabMessageCache[countOpenTabs] = htmlConversation;
                tabUsers.push(htmlUsers); 

                $("#imbaChatConversation").attr({
                    scrollTop: $("#imbaChatConversation").attr("scrollHeight")
                });
                    
                $('#imbaMessages').tabs("select", countOpenTabs);
            });
        } else if (type == tab_type_message) {
            // load messenger
            $.post(ajaxEntry, {
                secSession: phpSessionID,
                module: "AjaxMessenger",
                submodule: "IMBAdminModules",
                ajaxmethod: "loadMessages",
                params: JSON.stringify({
                    "reciever": data
                })
            },
            function(response) {
                var htmlConversation = "";

                $.each($.parseJSON(response), function(key, val) {
                    htmlConversation += "<div>"
                    + val.time + " "
                    + val.sender + ": "
                    + val.message + "</div>";
                });
                
                // Set the content of the info tab
                tabMessageCache.push(htmlConversation);
                tabMessageSinceId.push(-1);    
                tabUsers.push("You<br />" + name);                

                $("#imbaChatConversation").attr({
                    scrollTop: $("#imbaChatConversation").attr("scrollHeight")
                });
                
                $('#imbaMessages').tabs("select", countOpenTabs);
            });

            // Mark conversation as read
            $.post(ajaxEntry, {
                secSession: phpSessionID,
                module: "AjaxMessenger",
                submodule: "IMBAdminModules",
                ajaxmethod: "setReadByReciever",
                params: JSON.stringify({
                    "reciever": data
                })
            });
        }
                
        tabCount++;
    }
}

/**
 * Refreshs a special chatwindow
 */
function loadChatWindowContent(tabIndex) {
    /*$.jGrowl("Ausm Cache: " + tabMessageCache[tabIndex], {
        header: 'Lade Tab: ' + tabIndex
    });*/
    
    $("#imbaChatConversation").html(tabMessageCache[tabIndex]);
    $("#imbaChatConversationUserlist").html(tabUsers[tabIndex]);
}

/**
 * Returns the current selected tab index
 */
function getSelectedTabIndex(){
    return $('#imbaMessages').tabs('option', 'selected');
}
