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
var tab_data_name = "tab_data_name";
var tab_data_id = "tab_data_id";

// Reload Chats every 2000 ms
setInterval('refreshMessaging()', 5000);
   
/**
 * Check for news and update the tabs
 */
function refreshMessaging() {    
    if (isUserLoggedIn){
        // find the chats
        var chats = new Array();
        var sinceids = new Array();
        var selectedTabIndex = getSelectedTabIndex();
        var reloadCurrentTab = false;
    
        // Walk through all the open tabs    
        $.each($("#imbaMessages a"), function (k, v) {
            // leave out info tab
            if (k > 0){
                var tabData = getTabDataFromTabIndex(k, tab_data_type);
            
                // Check if its a chat
                if (tabData == tab_type_chat){
                    chats.push(tabData);
                    sinceids.push(tabMessageSinceId[k]);
                }
            }
        });
        
        $.post(ajaxEntry, {
            secSession: phpSessionID,
            module: "AjaxMessenger",
            submodule: "IMBAdminModules",
            ajaxmethod: "getAllNewsForMe",
            params: JSON.stringify({
                "channelids": chats, 
                "sinces": sinceids
            })
        }, 
        function(response) {
            var responseData = $.parseJSON(response);
            var gotNewMessages = false;
            
            // Got new messages
            $.each(responseData.newmessages, function(key, val) {                                
                // check if there is a open window with key as id and type is message                
                var tabIndex = getTabIndexFromId(key, tab_type_message);
                
                if (tabIndex != null) {
                    var htmlConversation = "";
                    
                    $.each(val, function(k, v) {
                        htmlConversation += "<div>"
                        + v.time + " "
                        + v.sender + ": "
                        + v.message + "</div>";
                    });
                
                    // Set the content of the tab
                    tabMessageCache[tabIndex] = htmlConversation;
                    reloadCurrentTab = true;
                }
                
                gotNewMessages = true;
            });
            
            // Show icon for new message
            if (gotNewMessages){
                $("#imbaGotMessage").effect("pulsate", {
                    times:3
                }, 2000);
            } else {
                $("#imbaGotMessage").hide();
            }
            
            // Update Users in Channel            
            $.each(responseData.usersinchannel, function(key, val) {          
                // check if there is a open window with key as id and type is chat
                var tabIndex = getTabIndexFromId(key, tab_type_chat);
                                    
                // should never be null though
                if (tabIndex != null) {
                    var htmlUsers = "";
                    $.each(val, function(k, v) {
                        htmlUsers += v + "<br/>";
                    });
                
                    // Set the users in channel
                    tabUsers[tabIndex] = htmlUsers;
                    reloadCurrentTab = true;
                }
            });
            
            // Update Messages in Channels
            $.each(responseData.newchatmessages, function(key, val) { 
                // check if there is a open window with key as id and type is chat
                var tabIndex = getTabIndexFromId(key, tab_type_chat);
                                
                // should never be null though
                if (tabIndex != null) {
                    var htmlConversation = tabMessageCache[tabIndex];
                                        
                    $.each(val, function(k, v) {
                        htmlConversation += "<div>"
                        + v.time + " "
                        + v.sender + ": "
                        + v.message + "</div>";
                    
                        tabMessageSinceId[tabIndex] = v.id;
                    });
                
                    // Set the content of the tab
                    tabMessageCache[tabIndex] = htmlConversation;
                    reloadCurrentTab = true;
                }
            });
            
            if (reloadCurrentTab){
                loadChatWindowContent(selectedTabIndex);
            }
        });
    }
}
   
/**
 * Create the info tab
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
                var htmlUsers = "";
                var responsJSON = $.parseJSON(response);
                    
                $.each(responsJSON.messages, function(key, val) {
                    htmlConversation += "<div>"
                    + val.time + " "
                    + val.nickname + ": "
                    + val.message + "</div>";

                    tabMessageSinceId[countOpenTabs] = val.id;
                });
                
                $.each(responsJSON.users, function(key, val) {
                    htmlUsers += val + "<br/>";
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
        /*$.post(ajaxEntry, {
                secSession: phpSessionID,
                module: "AjaxMessenger",
                submodule: "IMBAdminModules",
                ajaxmethod: "setReadByReciever",
                params: JSON.stringify({
                    "reciever": data
                })
            });*/
        }
                
        tabCount++;
    }
}

/**
 * Refreshs a special chatwindow
 */
function loadChatWindowContent(tabIndex) {
    $("#imbaChatConversation").html(tabMessageCache[tabIndex]);
    $("#imbaChatConversationUserlist").html(tabUsers[tabIndex]);
    
    $("#imbaChatConversation").attr({
        scrollTop: $("#imbaChatConversation").attr("scrollHeight")
    });
                
}

/**
 * Returns the current selected tab index
 */
function getSelectedTabIndex(){
    return $('#imbaMessages').tabs('option', 'selected');
}

/**
 * Return the data of a tab from a tabIndex
 */
function getTabDataFromTabIndex(tabIndex, tab_data){
    var result = "";
    $.each($("#imbaMessages a"), function (k, v) {
        if (k == tabIndex){
            var tmp = v.toString().split("#");
            result = $("#" + tmp[1]).data(tab_data);
        }
    });

    return result;
}

/**
 * Return the data of a tab from a tabIndex
 */
function getTabIndexFromId(tab_data, tab_type){
    var result = null;
    $.each($("#imbaMessages a"), function (k, v) {
        var tmp = v.toString().split("#");
                
        if ($("#" + tmp[1]).data(tab_data_id) == tab_data && $("#" + tmp[1]).data(tab_data_type) == tab_type) {
            result = k;
        }
    });

    return result;
}

/**
 * Sends a message 
 */
function sendChatWindowMessage(msgText, tabIndex) {
    if (tabIndex == 0) return;
    
    // Get data from the tab
    var tabData = getTabDataFromTabIndex(tabIndex, tab_data_id);
    var tabType = getTabDataFromTabIndex(tabIndex, tab_data_type);
    var httpPostData = null

    // What kind of tab we have here
    if (tabType == tab_type_chat){
        httpPostData = {
            secSession: phpSessionID,
            module: "AjaxMessenger",
            submodule: "IMBAdminModules",
            ajaxmethod: "sendChatMessage",
            params: JSON.stringify({
                "channelid": tabData,
                "message": msgText
            })
        };
    } else if (tabType == tab_type_message) {
        httpPostData = {
            secSession: phpSessionID,
            module: "AjaxMessenger",
            submodule: "IMBAdminModules",
            ajaxmethod: "sendMessage",
            params: JSON.stringify({
                "reciever": tabData,
                "message": msgText
            })
        };
    }
    else {
        return;
    }
    
    // Send post
    $.post(ajaxEntry, httpPostData , function(response) {
        if (response != "Message sent"){
            alert(response);
        }
    });

    loadChatWindowContent(tabIndex);
}

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

        sendChatWindowMessage(msgText, tabIndex);
        
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

    // Hide new Message Icon and create Click
    $("#imbaGotMessage").hide().click(function(){
        //showTabsWithNewMessage();        
        $("#imbaGotMessage").hide();
    });
});