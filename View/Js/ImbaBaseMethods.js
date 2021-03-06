/**
* Fills the variables currentUserName and currentUserOpenid
*/
function loadMyImbaUser() {
    $.post(ajaxEntry, {
        secSession: phpSessionID,
        module: "AjaxUser",
        submodule: "IMBAdminModules",
        ajaxmethod: "returnMyself"
    }, function (response){
        $.each($.parseJSON(response), function(key, value){
            if (key == "name") {
                currentUserName = value;
            } else if (key == "openid") {
                currentUserOpenid = value;
            }
        });
    });
}

/**
 *  Retrievs the Columnhead by index
 *  the <th> element needs to have an "title" Attribute
 */  
function getColumnHeadByIndex(tableId, colIndex){
    var result = null;
    $.each($("#"+tableId+" thead th"), function(index, value) { 
        if (index == colIndex) result = value.getAttribute("title");
    });
        
    return result;
}

/**
 * Creats a cookie, with name, value und days of expire
 */
function createCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        expires = "; expires="+date.toGMTString();
    }
    document.cookie = name+"="+value+expires+"; path=/";
}

/**
 * Reads a cookie by name
 */
function readCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}

/**
 * Erases a cookie by nane
 */
function eraseCookie(name) {
    createCookie(name,"",-1);
}

/**
 * Datatable sort pugins from
 * http://www.datatables.net/plug-ins/sorting
 */
jQuery.fn.dataTableExt.oSort['title-numeric-asc']  = function(a,b) {
    var x = a.match(/title="*(-?[0-9]+)/)[1];
    var y = b.match(/title="*(-?[0-9]+)/)[1];
    x = parseFloat( x );
    y = parseFloat( y );
    return ((x < y) ? -1 : ((x > y) ?  1 : 0));
};

jQuery.fn.dataTableExt.oSort['title-numeric-desc'] = function(a,b) {
    var x = a.match(/title="*(-?[0-9]+)/)[1];
    var y = b.match(/title="*(-?[0-9]+)/)[1];
    x = parseFloat( x );
    y = parseFloat( y );
    return ((x < y) ?  1 : ((x > y) ? -1 : 0));
};
    
jQuery.fn.dataTableExt.oSort['title-string-asc']  = function(a,b) {
    var x = a.match(/title="(.*?)"/)[1].toLowerCase();
    var y = b.match(/title="(.*?)"/)[1].toLowerCase();
    return ((x < y) ? -1 : ((x > y) ?  1 : 0));
};

jQuery.fn.dataTableExt.oSort['title-string-desc'] = function(a,b) {
    var x = a.match(/title="(.*?)"/)[1].toLowerCase();
    var y = b.match(/title="(.*?)"/)[1].toLowerCase();
    return ((x < y) ?  1 : ((x > y) ? -1 : 0));
};

/**
     * Shows the Menu and stuff around
     */
function showMenu() {
    // run the effect
    /*
    $("#imbaMenu").show("slide", {
        direction: "up"
    });
    */

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
    /*
    $("#imbaMenu").hide("slide", {
        direction: "up"
    });
    */

    if (isSystemInErrorState == false) {
        $("#imbaSsoLoginInner").hide("slide", {
            direction: "right"
        });

        $("#imbaUsersOnline").hide("slide", {
            direction: "up"
        });
    }
}
