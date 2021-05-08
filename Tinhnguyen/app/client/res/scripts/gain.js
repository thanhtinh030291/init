/*
 * mega menu
 */
$.fn.hoverClass = function(c){
    return this.each(function(){
        $(this).hover(
            function() {$(this).addClass(c);},
            function() {$(this).removeClass(c);}
        );
    });
};
function megaHoverOver(){
    $(this).find(".sub").stop().fadeTo("fast", 1).show();
    //Calculate width of all ul\'s
    jQuery.fn.calcSubWidth = function() {
        rowWidth = 0;
        //Calculate row
        $(this).find("ul").each(function() {
            rowWidth += $(this).width();
        });
    };
    if ( $(this).find(".row").length > 0 ) { //If row exists...
        var biggestRow = 0;
        //Calculate each row
        $(this).find(".row").each(function() {
            $(this).calcSubWidth();
            //Find biggest row
            if(rowWidth > biggestRow) {
            biggestRow = rowWidth;
            }
        });
        //Set width
        $(this).find(".sub").css({"width" :biggestRow});
        $(this).find(".row:last").css({"margin":"0"});
    } else { //If row does not exist...
        $(this).calcSubWidth();
        //Set Width
        $(this).find(".sub").css({"width" : rowWidth});
    }
}
function megaHoverOut(){
    $(this).find(".sub").stop().fadeTo("fast", 0, function() {
        $(this).hide();
    });
}

function gain_load_tpl() {
    var config = {
        sensitivity: 2, // number = sensitivity threshold (must be 1 or higher)
        interval: 100, // number = milliseconds for onMouseOver polling interval
        over: megaHoverOver, // function = onMouseOver callback (REQUIRED)
        timeout: 500, // number = milliseconds delay before onMouseOut
        out: megaHoverOut // function = onMouseOut callback (REQUIRED)
    };
    $(window).scroll(function () {
        if($(window).scrollTop() >= 150) {
            $("#back_to_top").stop(true, true).fadeIn("slow");
        } else if ($(window).scrollTop() < 100){
            $("#back_to_top").stop(true, true).fadeOut("slow");
        }
    });
    $("#back_to_top").click(function(e) {
        e.preventDefault();
        $.scrollTo(0, 300);
    });
    $("ul#mainMenu li .sub").css({"opacity":"0"});
    $("ul#mainMenu li").hoverIntent(config);

    $("#menuArea .menu_hover")
    .hover(
        function(){
            $(this).addClass("ui-state-hover");
        },
        function(){
            $(this).removeClass("ui-state-hover");
        }
    )
    $("#topMenu .fg-button:not(.ui-state-disabled):not([rel=1])")
    .hover(
        function(){
            $(this).addClass("ui-state-hover");
        },
        function(){
            $(this).removeClass("ui-state-hover");
        }
    )
    $("#topMenu input[name=quick_search_lookup], #topMenu a").mouseover(function(){
        logged_in_tmp = $("#topMenuLabel").html();
        $("#topMenuLabel").html($(this).attr("msg"));
    }).mouseout(function(){
        $("#topMenuLabel").html(logged_in_tmp);
    });
}

function gain_userdirectory_load(){
    $("#user_directory_page_list h3").click(function () {
//        $g_ico = $(this).children("img");
        $g_ico = $(this).children("div.toggle_ico").find("span.ui-icon");
        $(this).next("ul.user_dir").slideToggle("slow", function(){
            if($g_ico.attr("title") == "expand") {
                $g_ico.attr("class", "ui-icon ui-icon-minusthick");
                $g_ico.attr("title", "collapse");
            } else {
                $g_ico.attr("class", "ui-icon ui-icon-plusthick");
                $g_ico.attr("title", "expand");
            }
        });
    });

    $("#filter_user_active").click(function(){
        if($("input#search_user").next("a:visible").length != 0) {
            clear_filter_user($("input#search_user"), $("input#search_user").next("a"), "search_userdirectory", 0);
        }
        $("div.user_search_rsl").remove();
        $("#user_directory_page_list div.group").each(function(i){
            $(this).show();
            $(this).children("ul.user_dir").show();
            $(this).find("div.user_profile[active=0]").parent("li").hide();
            $(this).find("div.user_profile[active=1]").parent("li").show();
            var user_dir_list = $(this).children("ul.user_dir").children("li:visible").length;
            if(user_dir_list == 0) {
                $(this).hide();
            } else {
                $(this).children("ul.user_dir").show();
                $(this).children("h3").children("div.toggle_ico").find("span.ui-icon").attr("class", "ui-icon ui-icon-minusthick");
                $(this).children("h3").children("div.toggle_ico").find("span.ui-icon").attr("title", "collapse");
            }
        });
        var click_result = $("#user_directory_page_list div.group:visible").length;
        if(click_result == 0) {
            if($("div.user_search_rsl").length == 0) {
                $("div.search_set").after('<div style="padding: 0.7em; margin-top: 20px; margin-bottom: 5px;" class="user_search_rsl ui-state-highlight ui-corner-all" id="fw_notify_msg"><p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>no result</p></div>');
            }
        }
    });
    $("#filter_subordinate").click(function(){
        if($("input#search_user").next("a:visible").length != 0) {
            clear_filter_user($("input#search_user"), $("input#search_user").next("a"), "search_userdirectory", 0);
        }
        $("div.user_search_rsl").remove();
        $("#user_directory_page_list div.group").each(function(i){
            $(this).show();
            $(this).children("ul.user_dir").show();
            $("#user_directory_page_list li").hide();
            $("#user_directory_page_list li.subordinate").show();
            var user_dir_list = $(this).children("ul.user_dir").children("li:visible").length;
            if(user_dir_list == 0) {
                $(this).hide();
            } else {
                $(this).children("ul.user_dir").show();
                $(this).children("h3").children("div.toggle_ico").find("span.ui-icon").attr("class", "ui-icon ui-icon-minusthick");
                $(this).children("h3").children("div.toggle_ico").find("span.ui-icon").attr("title", "collapse");
            }
        });
        var click_result = $("#user_directory_page_list div.group:visible").length;
        if(click_result == 0) {
            if($("div.user_search_rsl").length == 0) {
                $("div.search_set").after('<div style="padding: 0.7em; margin-top: 20px; margin-bottom: 5px;" class="user_search_rsl ui-state-highlight ui-corner-all" id="fw_notify_msg"><p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>no result</p></div>');
            }
        }
//        $("#user_directory_page_list li").hide();
//        $("#user_directory_page_list li.subordinate").show();
//        $(this).parents("#user_directory_page_list").children("div.group").children("ul.user_dir").show();
//        $(this).parents("#user_directory_page_list").children("div.group").children("h3").children("div.toggle_ico").find("span.ui-icon").attr("class", "ui-icon ui-icon-minusthick");
//        $(this).parents("#user_directory_page_list").children("div.group").children("h3").children("div.toggle_ico").find("span.ui-icon").attr("title", "collapse");
//        $(this).parents("#user_directory_page_list").children("div.group").children("h3").children("img").attr("src", "im/group_e.png");
    });
    $("#filter_user_all").click(function(){
        if($("input#search_user").next("a:visible").length != 0) {
            clear_filter_user($("input#search_user"), $("input#search_user").next("a"), "search_userdirectory", 0);
        }
        $("#user_directory_page_list div.group, #user_directory_page_list div.group ul.user_dir, #user_directory_page_list li").show();
        $("#user_directory_page_list div.group").children("h3").children("div.toggle_ico").find("span.ui-icon").attr("class", "ui-icon ui-icon-minusthick");
        $("#user_directory_page_list div.group").children("h3").children("div.toggle_ico").find("span.ui-icon").attr("title", "collapse");
    });
}

function loadGoogleMaps(){
    google.load("maps", "2", {"callback" : dummy});
}
function dummy(){}

function escapeRegExp(str) {
    return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}

function search_userdirectory(filter_me) {
    if(filter_me) {
        filter_me = escapeRegExp(filter_me);
        regex = new RegExp(filter_me, "i");
        $("#user_directory_page_list div.group").each(function(i){
            $g_ico = $(this).find("span.ui-icon");
            if ($(this).find("li").text().search(regex) <= 0) {
                $(this).hide();
            } else {
                $(this).find("ul.user_dir").show();
                $g_ico.attr("class", "ui-icon ui-icon-minusthick");
                $g_ico.attr("title", "collapse");
                $(this).show();
            }
        });
        $("#user_directory_page_list div.group").find("li").each(function(i){
            if ($(this).text().search(regex) < 0) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    } else {
        $("#user_directory_page_list div.group").each( function(i){
            $g_ico = $(this).find("span.ui-icon");
            var index = $("#user_directory_page_list div.group").index(this);
            $(this).show();
            $(this).find("li").each(function(i){
                $(this).show();
            });
            if(index == 0) {
                $(this).find("ul.user_dir").show();
                $g_ico.attr("class", "ui-icon ui-icon-minusthick");
                $g_ico.attr("title", "collapse");
            } else {
                $(this).find("ul.user_dir").hide();
                $g_ico.attr("class", "ui-icon ui-icon-plusthick");
                $g_ico.attr("title", "expand");
            }
        });
    }
    $("div.user_search_rsl").remove();
    var search_result = $("#user_directory_page_list div.group:visible").length;
    if(search_result == 0) {
        if($("div.user_search_rsl").length == 0) {
            $("div.search_set").after('<div style="padding: 0.7em; margin-top: 20px; margin-bottom: 5px;" class="user_search_rsl ui-state-highlight ui-corner-all" id="fw_notify_msg"><p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-info"></span>no result</p></div>');
        }
    }
    $("input[name=search_user]").removeAttr("title");
}

function quick_filter($e, search_function) {
    $e.wrap('<div style="position: relative; display: inline;"></div>');
    $e.css("padding-right", "20px");
    $e.parent().append('<a href="#'+$e.attr('name')+'" style="position: absolute; right: 3px; top: -1px; z-index: 50; height: 16px; width: 16px;"><span title="clear filter" class="ui-icon ui-icon-circle-close"></span></a>');
    $a = $e.next("a");
    $a.hide();
    $e.val("");
    //bind typing in search field
    $e.bind("keyup", function(){
        $a = $e.next("a");
        var enable_filter = 0;
        var find_txt = jQuery.trim($e.val());
        if (find_txt && find_txt != $e.attr("title")) {
            enable_filter = 1;
        }
        eval(search_function+"(find_txt);");
        if(enable_filter == 1) {
            $a.show();
        } else if (enable_filter == 0) {
            $a.hide();
        }
    });
    $a.click( function() {
        clear_filter_user($e, $(this), search_function, 1);
    });
}

function clear_filter_user($f, $g, s, fc) {
    $f.val("");
    if (fc == 1) {
        $f.focus();
    } else {
        $f.blur();
    }
    $g.hide();
    eval(s + "('');");
}

function login_tip(id){
    $.post("index.php?fajax=1&mod=entities&view=login_tip", {
        id_tip: id,
        popup_id: popID
    },
    function(data){
        loadPopup(data, '', 0, 0, '#contentArea', 1);
    });
}

function close_tip () {
    get_tip = $("input[name=tip]").is(':checked');
    $.post("index.php?fajax=1&mod=entities&view=login_tip", {
        tip: get_tip,
        popup_id: popID
    },
    function(data){
        closePopup();
    });
}

function previous_tip (id, count) {
    if (id != 1) {
        id = id - 1;
    }
    login_tip(id);
    //load_page_content("index.php?fajax=1&mod=entities&view=login_tip&id_tip="+id, "div.popContent");
}

function next_tip (id, count) {
    if (id != count) {
        id = id + 1;
    }
    login_tip(id);
    //load_page_content("index.php?fajax=1&mod=entities&view=login_tip&id_tip="+id, "div.popContent");
}

/**
 * Decimal adjustment of a number.
 *
 * @param    {String}    type    The type of adjustment.
 * @param    {Number}    value    The number.
 * @param    {Integer}    exp        The exponent (the 10 logarithm of the adjustment base).
 * @returns    {Number}            The adjusted value.
 */
function decimalAdjust(type, value, exp) {
    // If the exp is undefined or zero...
    if (typeof exp === 'undefined' || +exp === 0) {
        return Math[type](value);
    }
    value = +value;
    exp = +exp;
    // If the value is not a number or the exp is not an integer...
    if (isNaN(value) || !(typeof exp === 'number' && exp % 1 === 0)) {
        return NaN;
    }
    // Shift
    value = value.toString().split('e');
    value = Math[type](+(value[0] + 'e' + (value[1] ? (+value[1] - exp) : -exp)));
    // Shift back
    value = value.toString().split('e');
    return +(value[0] + 'e' + (value[1] ? (+value[1] + exp) : exp));
}

// Decimal round
if (!Math.round10) {
    Math.round10 = function(value, exp) {
        return decimalAdjust('round', value, exp);
    };
}
// Decimal floor
if (!Math.floor10) {
    Math.floor10 = function(value, exp) {
        return decimalAdjust('floor', value, exp);
    };
}
// Decimal ceil
if (!Math.ceil10) {
    Math.ceil10 = function(value, exp) {
        return decimalAdjust('ceil', value, exp);
    };
}

function refreshing_page(e, entity) {
    $("#"+entity+"_search_form").find("input[type=submit][name=search]").click();
}