function fw_manage_entity_group(e, entity, group_token, gname, gaction, token, items_selected, check_sum){
    //return group name
    var param = '';
    if (gaction == 'add_to_group') {
        if ($(e).attr('name') == 'update_filter_group') {
            //TODO alert notify

            var group_name = $("#"+group_token+"_li a[href=#"+group_token+"_content]").text().trim();
            if (confirm(entity_update_filter.replace('{group_name}', group_name))) {
                param = '&filter_as_group=2';
                $.post("index.php?fajax=1&mod=entities&view=manage_entity_group"+param, {
                    entity: entity,
                    group_token: group_token,
                    gaction: gaction,
                    entity_token: token,
                    item_selected: items_selected,
                    check_sum: check_sum,
                    popup_id: popID
                },
                function(data) {
                    window.location = "index.php?mod="+data;
                    //$("#fw-show_search_form_"+data+"2").remove();
                });
            }
            return false;
        } else if ($(e).attr('name') != 'filter_as_group') {
            if (items_selected[0] != '2' && items_selected[1] == '') {
                alert('please select records');
                return false;
            }
        } else {
            param = '&filter_as_group=1';
        }
    } else if (gaction == 'active_group') {
        var group_name = $("#"+group_token+"_li a[href=#"+group_token+"_content]").text().trim();
        if (confirm(entity_close_group.replace('{group_name}', group_name))) {
            $.post("index.php?fajax=1&mod=entities&view=manage_entity_group", {
                entity: entity,
                group_token: group_token,
                gaction: gaction,
                entity_token: token,
                item_selected: items_selected,
                popup_id: popID
            },
            function(data) {
                window.location = "index.php?mod="+data;
            });
            return false;
        } else {
            return false;
        }
    } else if (gaction == 'remove_group' || gaction == 'remove_group_other') {
        var more_word = '';
        if (gaction == 'remove_group_other') {
            gaction = 'remove_group';
            more_word = confirm_remove_group_other+"\n";
        }

        if (confirm(more_word+confirm_remove_group+' "'+gname+'" '+permanently+'?')) {
            $.post("index.php?fajax=1&mod=entities&view=manage_entity_group", {
                entity: entity,
                group_token: group_token,
                gaction: gaction,
                entity_token: token,
                item_selected: items_selected,
                popup_id: popID
            },
            function(data) {
                $(e).parents('li').remove();
            });
        }
        return false;
    }

    //show popup
    //findPos(e);
    $.post("index.php?fajax=1&mod=entities&view=manage_entity_group"+param, {
        entity: entity,
        group_token: group_token,
        gaction: gaction,
        entity_token: token,
        item_selected: items_selected,
        check_sum: check_sum,
        popup_id: popID
    },
    function(data){
        loadPopup(data, 400, 0, 0, '#contentArea', 0);
    });
}

function fw_manage_entity_group_user(e, entity, mod){
    var search_token = $(e).attr('search_token');
    $.post("index.php?fajax=1&mod=entities&view=manage_entity_group_user", {
        module: mod,
        entity: entity,
        search_token: search_token,
        popup_id: popID
    },
    function(data){
        loadPopup(data, 400, 0, 0, '#contentArea', 0);
    });
}

function fw_manage_entity_group_user_remove(t, msg){
    if (confirm(msg)) {
        //send ajax remove
        $(t).parents('li').remove();
    }
}

function fw_manage_search_result_save(entity, entity_key, entity_mod, item_id, opt){
    var group_order = '';
    var i = 0;
    var group_active = '';
    var pass = 1;
    var param = '&'+$('#fw_manage_search_result_form').formSerialize();
    if (opt == "submit") {
        group_active = '&'+$("#fw_manage_search_result_order").sortable("serialize");
    } else if (opt == "restore") {
        if (!confirm(notify_click_restore)) {
            pass = 0;
        }
    }

    if (pass == 1) {
        $.get("index.php?fajax=1&mod=entities&view=search_result_manage"+group_active+param+"&submit="+opt, {
            entity: entity,
            entity_key: entity_key,
            popup_id: popID
        },
        function(data){
            $("#fw_manage_search_result_form").parent().html(data);
            if (entity_mod == "settings" || entity_mod == "newsletter") {
                $("#"+entity+"_search_result").load("index.php?ajax=1&mod="+entity_mod+"&view="+entity);
            } else if (entity_mod != entity && entity_mod != "crm" && entity_mod != "crm_contacts") {
                $("#"+entity_key).load("index.php?ajax=1&mod="+entity_mod+"&view="+entity+"&item_id="+item_id);
            } else {
                $("#"+entity_key+"_content").load("index.php?ajax=1&mod="+entity+"&entity="+entity_key);
            }

        });
        closePopup();
    }
}

function fw_manage_entity_group_user_save(entity, mod){
    var group_order = '';
    $("#fw_group_order_form").find('input.awesome').hide().parent().append("<span style=\"line-height: 21px; float: right;\">Loading...</span>");

    $("ul[id^=fw_group_order]").each(function(){
        group_order = group_order+'&'+$(this).sortable("serialize");
    });
    $('#fw_group_order_form').attr("action", $('#fw_group_order_form').attr("action")+group_order);
    $('#fw_group_order_form').submit(function() {
        $(this).ajaxSubmit({
            success: function() {
                if (mod != "") {
                    window.location = "index.php?mod="+mod;
                } else {
                    window.location = "index.php?mod="+entity;
                }
            }
        });
        return false;
    });
}

//for show title icon
function fw_show_popup_title(id_title, param){
    var offset = $(param).offset();
    var left = offset.left + $(param).width();
    var top = offset.top + $(param).height();
    $("#"+id_title+"_div").html(htmlspecialchars($(param).attr("fw_title")));
    var width = $("#"+id_title+"_div").width();
    var max_left = left+width;
    var main_width = $("#mainPage").width();
    var screen_width = $(window).width();
    var chk_screen = 0;
    if (main_width > screen_width) {
        chk_screen = main_width;
    } else {
        chk_screen = screen_width;
    }
    if (max_left > chk_screen) {
        left = chk_screen - (width+10);
    }
    $("#"+id_title+"_div").css("left", (left)+"px").css("top", top+"px").show();
}

//for checkbox on listing page
function fw_get_item(obj,key){
    var selected_items = new Array();
    var status = $('img.check_items_'+key).attr('rel'); //2: all, 1: page, 0: item
    if(status<2){
        var cnt = -1;
        for (x in item_selected[key]) {
            if (typeof(item_selected[key][x]) == 'string') {
                selected_items[++cnt]  = item_selected[key][x];
            }
        }

        if (cnt == -1) {
            $('#'+key+'_search_result').find("img.check_item_"+key).each(function(i){
                if($(this).attr('rel')==1){
                    selected_items[++cnt] = $(this).attr('item_id');
                }
            });
        }
    } else if (status == 2) {
        var cnt = -1;
        $('#'+key+'_search_result').find("img.check_item_"+key).each(function(i){
            if($(this).attr('rel')==0){
                selected_items[++cnt] = $(this).attr('item_id');
            }
        });
    }
    return [status,selected_items];
}

//for multiple item
function replicate_add_item(id,ain){
    delButton = ' <img src="im/remove.png" onclick="replicate_remove_item(\'{id}\',\''+id+'\');" title="remove" class="fw-point remove_'+id+'"/>';
    if(!ain) ain = ++aain;
    inp = $("#"+id+'_0').html();
    id_ = id+ain;
    inp = inp.replace('{id}',id_);
    delButton = '<td>'+delButton.replace('{id}',id_)+'</td>';
    //add delete button
    $("#"+id+' table').append('<tr id="'+id_+'">'+inp+delButton+'</div>');
}

function replicate_remove_item(id, field){
    $('#'+id).remove();
    var str = 'if (typeof(update_items_'+field+') != "undefined") {update_items_'+field+'();}';
    eval(str);
}

//show detail in popup(short hand details)
function runItemDetails(e){
    var item = $(e).attr('item');
    newClick = $(e).attr('rel');
    //findPos(e);
    if (activeClick > 0) {
        //close popup
        //$('div.popHead').parents("div.popup").html("").remove();
        closePopup();
    }
    if (activeClick != newClick) {
        activeClick = newClick;
        loadPopup($('#'+item+newClick).html(), 0, 0, 0, '', 0, 1);
    } else {
        activeClick = 0;
    }
}

function show_menu_tabs(e, id_tabs){
    $('#submenu').find('li').find('a').css('text-decoration','');
    $(e).css('text-decoration', 'underline');

    var entity_index = $("li#"+id_tabs+"_li").attr("rel")*1;
    $("#tab_menu_search_form").tabs("option", "active", entity_index);
    $("#tab_menu_search_result").tabs("option", "active", entity_index);
}

//submit option, click
function fw_display_alert(div_id, data, alert_data){
    //$("div.fw-info_error").remove();
    if (alert_data == undefined) {
        alert(data);
    }
    //$("div.ui-state-error").remove();
    fw_notify_msg(data,"fw_display_notify",'error',$(div_id),'B',60);
    //$(div_id).before("<div class=\"ui-state-error ui-corner-all\" style=\"padding: 0.7em; margin-top: 20px; margin-bottom: 10px;\"><p><span class=\"ui-icon ui-icon-alert\" style=\"float: left; margin-right: 0.3em;\"></span>"+data+"</p></div>");
}

function fw_disable_submit(entity, typ){
    if (entity) {
        entity = '_'+entity;
    } else {
        entity = '';
    }

    $obj = $("#ajax_form"+entity);
    if (typ == 2) {
        $obj.find("input[type=button], input[type=submit], input[type=image], span.fw_re-action_area").removeAttr('disabled').show();
        //$obj.find('img').show();
        $obj.find('input[type=submit]').parents("td.fw-form_buttons").children("span.fw_loading").remove();//remove word loading
        $obj.find('input[type=image]').parent().children("span.fw_loading").remove();
    } else {
        $obj.find("input[type=button], input[type=submit], input[type=image], span.fw_re-action_area").hide();
        //$obj.find('img').hide();
        $obj.find('input[type=submit]').parents(".fw-form_buttons").append("<span class=\'fw_loading\' style=\"line-height: 21px;\">Loading...</span>");//add word loading
        $obj.find('input[type=image]').after("<span class=\'fw_loading\' style=\"line-height: 21px;\">Loading...</span>");
    }
}

//for merge action[in listing entity]
function fw_merge_entity(entity, items, sum){
    var param = '';
    if (items[1] == ''){
        if (sum != 2 && items[0] == 2) {
            alert('you can merge two records only');
            return false;
        }
        alert('please select items');
        return false;
    } else {
        if (items[1].length == 2) {
            param += '&items='+items[1]+'';
        } else {
            alert('you can merge two records only');
            return false;
        }
    }
    window.location = 'index.php?mod=entities&view=merge&entity='+entity+param;
}

//for merge action[in listing entity]
function fw_export_entity(entity, token, items, sum){
    var param = '';
    if (items[1] == '' && items[0] != 2){
        alert('please select items');
        return false;
    } else {
        param += '&entity_token='+token+'&item_selected[]='+items[0]+'&item_selected[]='+items[1]+'';
    }
    window.location = 'index.php?mod='+entity+'&ajax=1&export=1'+param;
}

//for set search default[in search history]
function set_search_default(e, search_token){
    var search_default = $(e).attr('rel');
    var pass = 1;
    if (search_default == 0) {
        if (!confirm(confirm_set_search_default)) {
            pass = 0;
        }
    }
    if (pass == 1) {
        var search_form = $(e).attr('name');

        $('a.set_search_default img').attr('src','im/bookmark.png');
        $.get('index.php?fajax=1&mod=search&view=history&search_token='+search_token+'&search_default='+search_default,{
            set_mod: $(e).attr('mod'),
            search_entity: $(e).attr('search_entity'),
        });
        if (search_form == 1) {
            search_default = 1;
        }

        if (search_form != 'set_as_default') {
            if (search_default == 1) {
                $(e).attr('rel','0').children('img').attr('src','im/bookmark.png');
            } else {
                $(e).attr('rel','1').children('img').attr('src','im/bookmark_active.png');
            }
        }
//        //TODO set redirect
        var mod = $(e).attr('mod');
        $("#"+mod+"_content").load('index.php?ajax=1&mod='+mod);
        ///window.location = 'index.php?mod='+mod;
    }
}

//for update item number on tabs header
function gain_tab_index_update(del, edit, entity){
    if (del == 1) {//delete
        var cnt_update = parseFloat($("span[name="+entity+"]").html())-1;
//        $("span[name="+entity+"]").html(cnt_update);
    } else if (edit == 0) {//add new
        var cnt_update = parseFloat($("span[name="+entity+"]").html()*1)+1;
//        $("span[name="+entity+"]").html(cnt_update);
        if (cnt_update == 1){
            $("span[name="+entity+"]").addClass("fw-tab-index");
            $("span[name="+entity+"]").css("background-color","#3DAF36");
        } else {
            $("span[name="+entity+"]").removeClass("fw-tab-index");
            $("span[name="+entity+"]").css("background-color","#3DAF36");
        }
    } else if (edit == 1) {//update
        //$("span[name="+entity+"]").css("background-color","#3DAF36");
    }
}

function show_search_form_js(entity) {
    $("#"+entity+"_search_form").css("padding-top","5px").css("margin","3px 0px").toggle();
    $("span." + entity + "_show").toggle();
    $("span." + entity + "_hide").toggle();
}

function fw_entities_tags(entity, id){
    $.post("index.php?fajax=1&mod=entities&view=tags", {
        entity: entity,
        id: id,
        popup_id: popID
    },
    function(data){
        loadPopup(data, 400, 0, 0, '#contentArea', 0);
    });
}

function fw_entities_tags_save(entity, id){
    $.post("index.php?fajax=1&mod=entities&view=tags&link=update", {
        tags: $('#'+entity+'_tags_input').val(),
        entity: entity,
        id: id
    }, function(data){
        note_closePopup($("a.close_popup"));
        load_page_content('index.php?ajax=1&mod='+entity+'&link=detail&id='+id,'#items_details');
    });
}

//TODO when update need to remove this function from file share.js
function google_img(type){
    if (type == 'client') {
        var name = $('input[name="client_name"]').val();
        window.open("http://www.google.co.th/images?q=+"+ name.replace(/&/g, "+") +"+logo&ie=UTF-8");
    } else if (type == 'client_people') {
        var name_first = $('input[name="fw_client_people_name_first"]').val();
        var name_last = $('input[name="fw_client_people_name_last"]').val();
        window.open("http://www.google.co.th/images?q=+"+ htmlspecialchars_remove(name_first) + " " + htmlspecialchars_remove(name_last) +"+photo&ie=UTF-8");
    }
}
//TODO when update need to remove this function from file share.js
function htmlspecialchars_remove(string){
    string = string.replace(/&/g, " ");
    string = string.replace(/\'/g, " ");
    string = string.replace(/\"/g, " ");
    string = string.replace(/@/g, " ");
    string = string.replace(/$/g, " ");
    string = string.replace(/%/g, " ");
    string = string.replace(/\*/g, " ");
    string = string.replace(/#/g, " ");

    return string;
}

function htmlspecialchars_decode(string){
    string = string.replace(/&amp;/g, "&");
    string = string.replace(/&lt;/g, "<");
    string = string.replace(/&gt;/g, ">");
    string = string.replace(/&quot;/g, "\"");
    return string;
}

//for tags
function split(val) {
    return val.split( /,\s*/ );
}

//for tags
function extractLast(term) {
    return split( term ).pop();
}

function fw_add_mailing(e, entity, token, items_selected, check_sum){
    //return group name

    var param = '';
            if (items_selected[0] != '2' && items_selected[1] == '') {
                alert('please select records');
                return false;
            }
    //show popup
    //findPos(e);
    $.post("index.php?fajax=1&mod=entities&view=add_mailing"+param, {
        entity: entity,
        entity_token: token,
        item_selected: items_selected,
        check_sum: check_sum,
        popup_id: popID
    },
    function(data){
        loadPopup(data, '', 0, 0, '#contentArea', 0);
    });
}

function fw_manage_permission(e, entity, entity_id){
    $.post("index.php?fajax=1&mod=entities&view=manage_permission", {
        entity: entity,
        entity_id: entity_id,
        popup_id: popID
    },
    function(data){
        loadPopup(data, 400, 0, 0, '#contentArea', 0);
    });
}


var notify_word = new Array();
function fw_check_items(e, entity_key){
    if (count_selected[entity_key] == undefined) {
        count_selected[entity_key] = 0;
    }
    if (item_selected[entity_key] == undefined) {
        item_selected[entity_key] = new Array();
    }
    if ($(e).attr("rel") == 0) {//page selected
        //set start selected items
        //entity_key+_count_selected = 0;
        count_items_selected[entity_key] = 0;
        count_selected[entity_key] = 0;

        $(e).attr("src","im/checkbox_page.png").attr("rel","1");
        $("img.check_item_"+entity_key).each(function(){
            $(this).attr("src","im/checkbox_page.png").attr("rel","1");
            $(this).parents("tr").addClass("fw-tr_selected");

            count_items_selected[entity_key]++;
            count_selected[entity_key]++;
            item_selected[entity_key][++is] = $(this).attr("item_id");
        });
        //gain_show_notify('. $entity_key .'_count_selected+" '. msg($data['entity']) .' '. msg('selected') .'", "info","right",5);
        gain_show_notify(count_items_selected[entity_key]+notify_word[entity_key], "info","right",5);
    } else if ($(e).attr("rel") == 1) {//every page
        $(e).attr("src","im/checkbox_all.png").attr("rel","2");
        $("img.check_item_"+entity_key).attr("src","im/checkbox_page.png").attr("rel","1").parents("tr").addClass("fw-tr_selected");

        //remove
        item_selected[entity_key] = new Array();
        //gain_show_notify('. $entity_key .'_search_checksum+" '. msg($data['entity']) .' '. msg('selected') .'", "info","right",5);
        gain_show_notify(search_checksum[entity_key]+notify_word[entity_key], "info","right",5);
        count_selected[entity_key] = search_checksum[entity_key];
    } else if ($(e).attr("rel") == 2) {//remove
        $(e).attr("src","im/checkbox_none.png").attr("rel","0");
        $("img.check_item_"+entity_key).attr("src","im/checkbox_none.png").attr("rel","0").parents("tr").removeClass("fw-tr_selected");

        //remove
        item_selected[entity_key] = new Array();
        count_selected[entity_key] = 0;
    }
    fw_selector_update(entity_key);
}

function fw_check_item(e, entity_key){
    if ($('img.check_items_'+entity_key).attr("rel") != 2) {
        if (count_selected[entity_key] == undefined) {
            count_selected[entity_key] = 0;
        }
        if (item_selected[entity_key] == undefined) {
            item_selected[entity_key] = new Array();
        }
        if ($(e).attr("rel") == "1") {
            $(e).attr("src","im/checkbox_none.png").attr("rel","0");
            if($("img.check_items_"+entity_key).attr("rel") == 1) {
                $("img.check_items_"+entity_key).attr("rel", 0);
                $("img.check_items_"+entity_key).attr("src","im/checkbox_none.png");
            }
            $(e).parents("tr").removeClass("fw-tr_selected");

            count_selected[entity_key]--;
            var deleteMe = item_selected[entity_key].indexOf($(e).attr("item_id"));
            delete item_selected[entity_key][deleteMe];
        } else {
            $(e).attr("src","im/checkbox_page.png").attr("rel","1");
            $(e).parents("tr").addClass("fw-tr_selected");
            count_selected[entity_key]++;
            item_selected[entity_key][++is] = $(e).attr("item_id");
        }
        fw_selector_update(entity_key);
    }
}

function fw_grid_items_unselect(entity_key){
    count_selected[entity_key] = 0;
    item_selected[entity_key] = new Array();
    count_items_selected[entity_key] = 0;
    $("img.check_item_"+entity_key).each(function(){
        $(this).attr("src","im/checkbox_none.png").attr("rel","0");
        $(this).parents("tr").removeClass("fw-tr_selected");
    });
    $("img.check_items_"+entity_key).each(function(){
        $(this).attr("src","im/checkbox_none.png").attr("rel","0");
        $(this).parents("tr").removeClass("fw-tr_selected");
    });
    fw_selector_update(entity_key);
}

function entity_address_return_position(mod){
    var c = new Array();
    var a = -1;
    var item_id = $("#tab_menu").attr("item_id");
    $("#sortable_address li").each(function(){
        c[++a] = $(this).attr("rel");
    });

    $.post("index.php?mod="+mod+"&view=address&link=update_position&id="+item_id,
        {"order[]": c}, function () {
            load_page_content("index.php?mod="+mod+"&view=address&item_id="+item_id+"&ajax=1", "#address");
        }
    );
}

function displayQS (entity) {
    if ($("#"+entity+"_qs_rsl").attr("rel") == 1) {
        //collapse
        $("#"+entity+"_qs_header span.ui-icon").attr("class","ui-icon ui-icon-plusthick").attr("title", 'expand');
        $("#"+entity+"_qs_rsl").attr("rel","0");
    } else {
        //expand
        $("#"+entity+"_qs_header span.ui-icon").attr("class","ui-icon ui-icon-minusthick").attr("title", 'collapse');
        $("#"+entity+"_qs_rsl").attr("rel","1");
    }
    $("#"+entity+"_qs_rsl").slideToggle();

    var display = $("#"+entity+"_qs_rsl").attr("rel");
    if (display != undefined) {
        $.get("index.php?ajax=1&mod=entities&view=quick_search&entity_qs="+entity+"&display="+display);
    }
}

function login_tip(){
    $.post("index.php?fajax=1&mod=entities&view=login_tip", {
        popup_id: popID
    },
    function(data){
        loadPopup(data, '', 0, 0, '#contentArea', 0);
    });
}

function ignore_tip () {
    $.post("index.php?fajax=1&mod=entities&view=login_tip&ignore=1", {
        popup_id: popID
    },
    function(data){
        closePopup();
    });
}

function next_tip (id) {
    load_page_content("index.php?fajax=1&mod=entities&view=login_tip&id_tip="+id, "div.popContent");
}

function check_all_user(e, id) {
    var v = $(e).attr("action_to");
    if (v == "select_all") {//to select all checkbox
        v = "unselect_all";
        $("#"+id).find("input[type=checkbox]:visible").prop("checked", true);
        $(e).parent("p").prev().find('span.ui-icon-plusthick').each( function() {//expand main div
            $(this).closest("div.expand_title").click();
        });
        $("#"+id).find("div.expand_title").each( function() {//expand child div
            if ($(this).find('span.ui-icon-plusthick').length > 0) {
                $(this).click();
            }
        });
    } else {//to unselect all checkbox
        v = "select_all";
        $("#"+id).find("input[type=checkbox]:not(:disabled)").prop("checked", false);
    }
    $(e).attr("action_to", v).html(lng[v]);
}

var task_schedule = new Array();
var now = new Array();
function fw_entities_task_schedule_init() {
    $.getJSON("index.php?ajax=1&mod=entities&view=task_schedule",
        function(data) {
            for (var i in data) {
                now[i] = new Date();
                if (typeof(task_schedule[i]) == "undefined") {
                    task_schedule[i] = new Array();
                    task_schedule[i]['run_time'] = data[i];
                    task_schedule[i]['next_run'] = now[i];
                }
            }
        fw_entities_task_schedule_set_time_out();
    });
}

function fw_entities_task_schedule_set_time_out() {
    now = new Date();
    for (var i in task_schedule) {
        if (task_schedule[i]['next_run'] <= now) {//check if next time to run is passed
            window[i]();
            task_schedule[i]['next_run'].setSeconds(task_schedule[i]['next_run'].getSeconds() + task_schedule[i]['run_time']);//plus second to that function
        }
    }
    setTimeout("fw_entities_task_schedule_set_time_out();", 1000*5); //time to re-check the notify e.g. 1000*10 means 10 seconds
}


//var favicon=new Favico({ //use for init favico number
//    animation:'fade',
////    type : 'rectangle',
//});
//function fw_entities_set_fav_no() {
//    $.getJSON("index.php?ajax=1&mod=dashboard&view=daily_focus&return=fav_no", function(data){
//        if (data.rsl_number > 0) {
//            favicon.badge(data.rsl_number);
//            $("#daily_focus_number").html(data.rsl_number).show();
//        } else {
//            favicon.reset();
//            $("#daily_focus_number").hide();
//        }
//    });
//}