var id = 0;
var aain = 1;
var inp = '';
var popID = 0;
var myLayer = 1005;
var tpl = '<div id="{popupid}"><div class="popContent">{desc}</div></div>';
var posX = 0;
var posY = 0;
var offY = 0;
var ns = (navigator.appName.indexOf("Netscape") != -1);

var activeClick = 0;

var posE = new Array();
var is = -1;

var item_selected = new Array();
var count_selected = new Array();
var search_checksum = new Array();
var session_id = '';
function findPos(obj){
    var pos = $(obj).offset();
    posE[0] = pos.left;
    posE[1] = pos.top;
    //return [pos.left,pos.top];
}

function findPosB(obj){
    var curleft = curtop = 0;
    if(obj.offsetParent){
        curleft = obj.offsetLeft
        curtop = obj.offsetTop
        while(obj = obj.offsetParent){
            curleft += obj.offsetLeft
            curtop += obj.offsetTop
        }
        posE[0] = curleft;
        posE[1] = curtop;
    }
    return [curleft,curtop];
}

function findXY(obj){
    var curleft = curtop = 0;
    if(obj.offsetParent){
        curleft = obj.offsetLeft
        curtop = obj.offsetTop
        while (obj = obj.offsetParent) {
            curleft += obj.offsetLeft
            curtop += obj.offsetTop
        }
    }
    posX = curleft;
    posY = curtop;
    offY = ns ? pageYOffset : document.documentElement && document.documentElement.scrollTop ?
     document.documentElement.scrollTop : document.body.scrollTop;
     offY += ns ? innerHeight : document.documentElement && document.documentElement.clientHeight ?
     document.documentElement.clientHeight : document.body.clientHeight;
}

//fadeBg :
function loadPopup(content, width, positionX, positionY, appendWhere, fadeBg, callInit, beforeClose, multiple_popup, hide_close_btn){
    //closePopup('div.popHead');
    //close all popup,, cannot show muti popup
    //$('div.popHead').parents("div.popup").html("").remove();
    if (multiple_popup == undefined) {
        $("#popup"+popID).remove();
    }
    ++popID;

    if (!appendWhere) {
        appendWhere = 'body';
    }

    if (width == 0 || width == '') {
        width = 'auto';
    }

    var modal = true;
    if (fadeBg == 0 || fadeBg == '' || fadeBg == undefined) {
        modal = false;
    }

    if (callInit == undefined) {
        callInit = 0;
    }

    content = content.replace(/\$\&/g, '&#36;&');
    var html = tpl.replace(/{popupid}/g, 'popup'+popID).replace(/{desc}/g, content);
    $(appendWhere).append(html);
//    var title = $("#popup"+popID+" div.popContent").find("div.popupHeader").html(title).text().replace(/(<([^>]+)>)/ig,"");
    var title = $("#popup"+popID+" div.popContent").find("div.popupHeader").html(title).text();
    // try to decode the html special characters and strip HTML tags
    if (hide_close_btn == 1) {
        hide_close_btn = false;
    } else {
        hide_close_btn = true;
    }

    var $dialogContainer = $("#popup"+popID);
    var $detachedChildren = $dialogContainer.children().detach();
    //position
    $dialogContainer.dialog({
        minHeight: 'auto',
        modal: modal,
        title: title,
        width: width,
        closeOnEscape: hide_close_btn,
        open: function(event, ui) {
            $detachedChildren.appendTo($dialogContainer);
            //center dialog if not requested otherwise
            if (positionX == 0 || positionY == 0) {
                $(this).dialog({ position: { my: "center", at: "center", of: window } });
            }

            if (positionX != 0 || positionY != 0) {
                $("#popup"+popID).parent().css("top", positionY).css("left", positionX);
            }

            $dialogBox = $(this).parent();
            if (parseInt($dialogBox.css("top")) <= 0) {
                $dialogBox.css("top", "0px");
            }
            if (callInit == 1) {
                gain_init("#popup"+popID);
            }
            if(hide_close_btn == false) {
                $("#popup"+popID).parent().find(".ui-dialog-titlebar-close").hide();
            }
            $("#popup"+popID).parent().find(".ui-dialog-titlebar-close").attr("title", $("#popup"+popID).parent().find(".ui-dialog-titlebar-close").find("span").text());

            var index = myLayer-2;
//            $('div.ui-widget-overlay').each(function () {
            $('div.popup').each(function () {
                var z = parseInt($(this).css('z-index'), 10);
                if (z > index) index = z;
            });
            $("#popup"+popID).parent().css('z-index', index);

        },
        beforeclose: function(event, ui) {
            if (beforeClose != undefined && beforeClose != 0) {
                eval(beforeClose+"(event, ui, "+popID+");");
            }
        },
        close: function(event, ui){
            $(this).remove();
            $(window).unbind("beforeunload");
        }
    });
}

function closePopup(obj){
    if (obj == undefined) {
        $("#popup"+popID).dialog('close');
    } else {
        $(obj).parents("div[id^=popup]").dialog('close');
    }
}

function changeLayer(e){
    ++myLayer;
    $(e).css("z-index", myLayer);
}

function flip( id ){
    var o = document.getElementById(id);
    if ( o.style.display == "block" )
        o.style.display = "none";
    else
        o.style.display = "block";
}

function confirm_word(word){
    if(!confirm(word)){
        return false;
    }
}

function usun(link,comm){
    var agree=confirm(comm);
    if (agree)
    document.location=link;
}

function displayWindow(url, nazwa, width, height){
   window.open(url,nazwa,'width=' + width + ',height=' + height + ',resizable=0,scrollbars=yes,menubar=no,status' )
}

function addNewElement(elemID, elemInf){
    elementRef = document.getElementById(elemID);
    elementNew = document.createElement('span');
    elementRef.appendChild(elementNew);
    elementNew.innerHTML += elemInf;
}

function fw_send_data(e,action){
    var table_name = $(e).parents('div.req_list').attr('table_name');
    var field_type = $(e).parents('div.req_list').attr('field_type');
    var field_name = $(e).parents('div.req_list').attr('field_name');
    var id_item = $(e).parents('div.req_list').attr('id_item');
    var new_value = $(e).parents('div.req_list').attr('new_value');

    if(field_type=='S'){
        fw_send_data_action('#ajax_form select[name='+field_name+']',new_value);
    }else if(field_type=='T'){
        $('#ajax_form textarea[name='+field_name+']').val(new_value);
    }else{
        $('#ajax_form input[name='+field_name+']').val(new_value);
    }
}

function fw_send_data_action(obj,val){
    $(obj).before('***');
    $(obj).val(val);
}

function confirmation(msg,url,where){
    var answer = confirm(msg);
    if (answer) {
        $(where).load(url);
    } else {
        return false;
    }
}

//use for search.php
//show_popup_search(e,$basics['op'],id,link,fadeBg)
function show_popup_in_search(e,mod,id,link,fadeBg){
    $.get("index.php?ajax=1&mod="+mod, {
        link: link,
        id: id,
        popup_id: popID
    },
    function(data){
        loadPopup(data,0,0,0,'#contentArea',1,1);
    });
}

function fw_keypress_word_key(e){
    //allow letters, digits, underscore, space bar
    if (navigator.appName == "Microsoft Internet Explorer")    {
        if((e.keyCode >= 48 && e.keyCode <= 57) || (e.keyCode == 8)) return true;
        else return false;
    } else {
        if ((e.charCode >= 97 && e.charCode <= 122) || (e.charCode >= 65 && e.charCode <= 90) || (e.charCode >= 48 && e.charCode <= 57) || (e.charCode == 95) || (e.charCode == 32) || (e.keyCode == 8)) return true;
        else return false;
    }
}

function fw_effect_loading(where, linked, t) {
    $('body').ajaxStart(function(){
        $('#loading').show();
    });
    if (linked != "") {
        $(where).load(linked, function(){
            gain_init(where);
        });
    }
    $('body').ajaxComplete(function(){
        $('#loading').hide();
    });
}

function gain_init(e){
    var e = $(e); //$(e+" input.jsdate")
    setTimeout(function(){
        e.find("input.jsdate").datepicker({
            dateFormat: JS_DATE_FORMAT,
            showButtonPanel: true,
            showAnim: "slideDown"
        });
        e.find("input.jstime").ptTimeSelect({
            zIndex: 10020,
            containerClass: "timeCntr",
            containerWidth: "350px",
            setButtonLabel: "Select",
            minutesLabel: "min",
            hoursLabel: "Hrs"
        });
        //e.find("input[title!=''], textarea[title!=''], select[title!='']").hint();
//        e.find("a.fancybox").fancybox();
        e.find("a.lightbox").colorbox();
        gain_table_effect(e);
    }, 700);
}

function addCommas(nStr){
    nStr += "";
    x = nStr.split(".");
    x1 = x[0];
    x2 = x.length > 1 ? "." + x[1] : "";
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, "$1" + "," + "$2");
      }
      return x1 + x2;
}

function formatAsMoney(mnt) {
    mnt -= 0;
    mnt = (Math.round(mnt*100))/100;

    if (mnt == Math.floor(mnt)) {
        if (typeof(MONEY_FORCE_DECIMALS) != "undefined" && MONEY_FORCE_DECIMALS == 1) {
            mnt = mnt + ".00";
        }
    } else if (mnt*10 == Math.floor(mnt*10)) {
        mnt = mnt + "0";
    }

    return mnt;
}

function formatAsMoneyShow(mnt){
    return addCommas(formatAsMoney(mnt));
}

function formatNumberScale (number, specific, displayZeroDecimals) {
    var scale = '';
    var scale_suffix = '';
    var negative_flag = false;
    if (typeof(specific) === "undefined") {
        specific = 'A';
    }
    if (typeof(displayZeroDecimals) === "undefined") {
        displayZeroDecimals = 1;
    }

    if (number < 0) {
        negative_flag = true;
        number =  number * (-1);
    }
    if ( (number >= 1000000 && specific == 'A') || specific == 'M' ) {
        scale = formatAsMoneyShow((Math.round(number/10000))/100);
        scale_suffix = 'M';
    } else if ( (number >= 1000 && specific == 'A') || specific == 'K' ) {
        scale = formatAsMoneyShow((Math.round(number/10))/100);
        scale_suffix = 'K';
    } else {
        scale = formatAsMoneyShow(number);
    }
    //remove extra decimal zeros
    if(displayZeroDecimals == 0) {
        if (scale.substring(scale.length-3) == '.00') {
            scale = scale.substring(0, scale.length-3);
        } else if (scale.substring(scale.length-2, 1) == '.' && scale.substring(scale.length-1) == '0') {
            scale = scale.substring(0, scale.length-1);
        }
    }
    if (negative_flag) {
        scale = '-'+scale;
    }
    scale += scale_suffix;
    return scale;
}

function copy_number(e, myClass){
    var num = $(e).val();
    if (num == '-') {
        num = 0;
    }

    if (myClass != "spot_exchange_class") {
        $(e).val(formatAsMoneyShow(stripNonNumeric(num)));
    } else {
        $(e).val(Math.round(stripNonNumeric(num)*1000000)/1000000);
    }

    num = $(e).val();
    if (formatAsMoneyShow(num) == 0) {
        $(e).next('input.'+myClass).val('0');
    } else {
        $(e).next('input.'+myClass).val(stripNonNumeric(num));
    }
}

function stripNonNumeric(str){
    str += '';
    var rgx = /^\d|\.|-$/;
    var out = '';
    for( var i = 0; i < str.length; i++ ){
    if( rgx.test( str.charAt(i) ) ){
        if( !( ( str.charAt(i) == '.' && out.indexOf( '.' ) != -1 ) ||
            ( str.charAt(i) == '-' && out.length != 0 ) ) ){
                  out += str.charAt(i);
            }
        }
      }
      return out;
}

function imposeMaxLength(Object, MaxLen){
    var text = $(Object).val();
    var textlength = text.length;
    if (textlength > MaxLen) {
        $(Object).val(text.substr(0, MaxLen));
        return false;
    } else {
        return true;
    }
}

function stripHTML(s){
    var reTag = /<(?:.|\s)*?>/g;
    return s.replace(reTag, "");
};

function gain_notify_reorder(base_position, position){
    var current_height = 0;
    $("div.show_notify[rel="+position+"]").each(function(){
        current_height += $(this).height();
    });
    $("div.show_notify[rel="+position+"]").each(function(){
        current_height -= $(this).height();
        var admend_position = "";
        var new_position = 0;
        if(position=="left" || position=="right"){
            admend_position = "bottom";
            new_position = base_position+current_height;
        }else{
            admend_position = "top";
            new_position = base_position+current_height;
        }
        $(this).css(admend_position, new_position);
        myElem = $(this).attr("id");
        $("#"+myElem+" div").css("height", $("#"+myElem+" div").height());
    });
}

function gain_notify_minimize(e, div_id, direction){
    $("#"+div_id+" p.min").toggle("slide", {direction: direction});
}

function gain_notify_close(e,time_out,base_position,position){
    $('#'+e)
    .fadeOut('slow', function() {
        $(this).remove();
        //reorder elements
        gain_notify_reorder(base_position,position);
     });
}

var my_alert = 0;
var notify_timeout = new Array();
function gain_show_notify(txt, txt_type, position, time_out){
    //set defaults
    if (typeof(time_out) != "number") {
        time_out = 3;
    }

    if (position == '' || position == undefined) {
        position = 'right';
    }
    //manage position
    //set default
    var types = new Array();
    types["alert"] = new Array();
    types["alert"][0] = "error"//bg
    types["alert"][1] = "alert"//icon
    types["info"] = new Array();
    types["info"][0] = "highlight"//bg
    types["info"][1] = "info"//icon
    types["snooze"] = new Array();
    types["snooze"][0] = "highlight"//bg
    types["snooze"][1] = "info"//icon

//    alert(current_height);
    //set base position
    var notify_spacing = 15;
    if (position == 'right') {
        pos_bottom = 200;
        pos_right = 0;
        pos_top = '-';
        pos_left = '-';
        pass_position = pos_bottom;
        direction = 'right';
    } else if(position == 'left') {
        pos_bottom = 200;
        pos_right = '-';
        pos_top = '-';
        pos_left = 0;
        pass_position = pos_bottom;
        direction = 'left';
    } else if(position == 'top') {
        pos_bottom = '-';
        pos_right = 0;
        pos_top = 0;
        pos_left = '-';
        pass_position = pos_top;
        direction = 'right';
    } else if (position == 'bottom') {
        pos_bottom = 10;
        pos_right = 0;
        pos_top = '-';
        pos_left = '-';
        pass_position = pos_bottom;
        direction = 'right';
    }

    //create css for position
    var my_css = '';
    if (pos_left != '-') {
        my_css += 'left: '+pos_left+'px;';
    }
    if (pos_right!='-') {
        my_css += 'right: '+pos_right+'px;';
    }
    if (pos_bottom!='-') {
        my_css += 'bottom: '+pos_bottom+'px;';
    }
    if (pos_top!='-') {
        my_css += 'top: '+pos_top+'px;';
    }

    //create id with running number
    ++my_alert;
    var my_id = 'gain_notify'+my_alert;
    var icon_margin = 10;
    var icons = '<a href="#close" onclick="gain_notify_close(\''+my_id+'\',0,'+pass_position+',\''+position+'\')"><span class="icon ui-icon ui-icon-close" style="position: absolute; top: 2px; right: 2px;"></span></a>';
    if(txt_type=='snooze'){
        icons = '<span class="icon ui-icon ui-icon-clock" style="position: absolute; top: 2px; right: 15px;"></span>'+icons;
        icon_margin = 20;
    }

    $("body").append('<div id="'+my_id+'" class="show_notify ui-widget" rel="'+position+'" style="position: fixed; z-index: 10000;'+my_css+'"><div class="ui-state-'+types[txt_type][0]+' ui-corner-all" style="padding: 5pt 0.7em; position: relative;"><a href="#" style="margin-right: '+icon_margin+'px; float: left;" onclick="gain_notify_minimize(this, \''+my_id+'\', \''+direction+'\')"><span class="ui-icon ui-icon-'+types[txt_type][1]+'" style="float: left; margin-right: 0.3em;"></span></a><p class="min" style="width: 200px; margin-right: 2.1em;">'+txt+'</p><span class="xoptions">'+icons+'</span></div></div>').fadeIn('slow');
    if (time_out > 0) {
        time_out *= 1000;
        notify_timeout = setTimeout("gain_notify_close('"+my_id+"',"+time_out+","+pass_position+",'"+position+"')", time_out);
    }
    //calculate height of current elements
    gain_notify_reorder(pass_position, position);
}

function gain_table_effect(e) {
    e.find("table.fw-table_effect tbody tr td").unbind("mouseenter.tableFx, mouseleave.tableFx");
    e.find("table.fw-table_effect tbody tr td").bind({
        "mouseenter.tableFx": function() {
            $(this).parent("tr").addClass("fw-highlight_row");
            if($(this).parent("tr").find("table.fw-table_effect").html() != null || $(this).parent("tr").find("form").html() != null || $(this).parents("form").html() != null) {
                $(this).parent("tr").removeClass("fw-highlight_row");
            }
        },
        "mouseleave.tableFx": function () {
            $(this).parent("tr").removeClass("fw-highlight_row");
        }
    });
}

function gain_ul_effect(ul_id){
    $('#'+ul_id).find('li').unbind("mouseenter.tableFx, mouseleave.tableFx");
    $('#'+ul_id).find('li').bind({
        "mouseenter.tableFx": function() {
            $(this).addClass("fw-highlight_row");
        },
        "mouseleave.tableFx": function () {
            $(this).removeClass("fw-highlight_row");
        }
    });
}

function gain_header_toggle_group(e, area_group) {
    $e = $(e);
    $block = $("."+area_group);
    $block.toggle();
    var ds = $block.css("display");
    var newImg = '';
    if (ds == 'none') {
        newCls = 'ui-icon ui-icon-plusthick';
    newtitle = 'expand';
    } else {
        newCls = 'ui-icon ui-icon-minusthick';
    newtitle = 'collapse';
    }
    $e.find("span").attr("class", newCls);
    $e.find("span").attr("title", newtitle);
}

function gain_toggle_area(e, area_id) {
    $e = $(e);
    $block = $("#"+area_id);
    $block.toggle(0, function(){
        newCls = '';
        if($block.css("display") == "block") {
            newCls = 'ui-icon ui-icon-minusthick';
            newtitle = 'collapse';
        } else {
            newCls = 'ui-icon ui-icon-plusthick';
            newtitle = 'expand';
        }
        $e.find("span").attr("class", newCls);
        $e.find("span").attr("title", newtitle);
    });
}

function filterInput(filterType, evt, allowDecimal, allowCustom, option){
    var keyCode, Char, inputField, filter = '';
    var alpha = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    var num   = '0123456789';
    // Get the Key Code of the Key pressed if possible else - allow
    if (window.event) {
        keyCode = window.event.keyCode;
        evt = window.event;
    } else if (evt) {
        keyCode = evt.which;
    } else {
        return true;
    }
    // Setup the allowed Character Set
    if (filterType == 0) {
        filter = alpha;
    } else if (filterType == 1) {
        filter = num;
    } else if (filterType == 2) {
        filter = alpha + num;
    }
    if (allowCustom) {
        filter += allowCustom;
    }
    if (filter == '') {
        return true;
    }
    // Get the Element that triggered the Event
    inputField = evt.srcElement ? evt.srcElement : evt.target || evt.currentTarget;
    // If the Key Pressed is a CTRL key like Esc, Enter etc - allow
    if((keyCode==null) || (keyCode==0) || (keyCode==8) || (keyCode==9) || (keyCode==13) || (keyCode==27)) {
        return true;
    }
    // Get the Pressed Character
    Char = String.fromCharCode(keyCode);
    // If the Character is a number - allow
    if((filter.indexOf(Char) > -1)) {
        return true;
    } else if (filterType == 1 && allowDecimal && (Char == '.') && inputField.value.indexOf('.') == -1) {
        // Else if Decimal Point is allowed and the Character is '.' - allow
        return true;
    } else if (filterType == 1 && allowDecimal && (option != "unsign") && (keyCode==45) && inputField.value.indexOf('-') == -1 && inputField.value.length == 0) {
        // Else if Negative Decimal is allowed and the Character is '-' allow and should be first charector
        return true;
    } else {
        return false;
    }
}

function isNumberKey(evt){
    var charCode = (evt.which || evt.which==0) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57) && charCode != 46) return false;
    return true;
}

function NumberNoDecimal(evt){
    var charCode = (evt.which || evt.which==0) ? evt.which : event.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57) || charCode == 46) return false;
    return true;
}

function gup(name) {
  name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
  var regexS = "[\\?&]"+name+"=([^&#]*)";
  var regex = new RegExp( regexS );
  var results = regex.exec( window.location.href );
  if( results == null )
    return "";
  else
    return results[1];
}

function getTitle(e){
    var rtitle = 'original-title';
    if ($(e).attr(rtitle) == undefined) {
        rtitle = 'title';
    }
    return $(e).attr(rtitle);
}

function fw_search_quick(entity, param, entity_key, button_name){
    $('#loading').show();
    //clear form
    var $form = $("#fw-search_form_" + entity);
    $form.clearForm();
    $form.append('<input type="hidden" name="button_name" value="'+entity+'_'+button_name+'">');
    //put value
    param_result = param.split(";;");
    for (i = 0; i < param_result.length; i++) {
        if (param_result[i]) {
            if (param_result[i] == 'clear') {
                $form.clearForm();
            } else {
                param_result2 = param_result[i].split("||");
                var type = param_result2[0];
                var field = param_result2[1];
                var value = param_result2[2];
                if (type == 'input' || type == 'daterange') {
                    $form.find('input[name=\''+entity+'['+field+']\']').val(value);
                } else if (type == 'select' || type == 'S') {
                    if ($form.find('select[name=\''+entity+'['+field+']\']').length == 1) {
                        if ($form.find('select[name=\''+entity+'['+field+']\'] option[value=\''+value+'\']').length == 0) {
                            $form.find('select[name=\''+entity+'['+field+']\']').html('<option>'+value+'</option>');
                        }
                        $form.find('select[name=\''+entity+'['+field+']\']').val(value);
                    } else if ($form.find('select[name='+field+']').length == 1){
                        if ($form.find('select[name='+field+'] option[value=\''+value+'\']').length == 0) {
                            $form.find('select[name='+field+']').html('<option>'+value+'</option>');
                        }
                        $form.find('select[name='+field+']').val(value);
                    }
                } else if (type == 'checkbox' || type == 'C') {
                    param_result3 = value.split(";");
                    //var param_result3_l = param_result3.length;
                    for (j = 0; j < (param_result3.length-0); j++) {
                        $form.find('input[id^=\'check-'+entity+'['+field+']\'][value='+param_result3[j]+']').prop("checked", true);
                    }
                } else if (type == 'E') {
                    search_function = eval(field + "_search");
                    if (typeof search_function == 'function') {
                        search_function(value);
                    }
                }
            }
        }
    }

    $form.ajaxSubmit({
        target: "#"+entity_key,
          success: function(){//ui-state-default
              $('#loading').hide();
        }
    });
}

//TODO when update need to remove this function from file share.js
function htmlspecialchars (string, quote_style, charset, double_encode) {
    // http://kevin.vanzonneveld.net
    // +   original by: Mirek Slugen
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Nathan
    // +   bugfixed by: Arno
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Ratheous
    // +      input by: Mailfaker (http://www.weedem.fr/)
    // +      reimplemented by: Brett Zamir (http://brett-zamir.me)
    // +      input by: felix
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // %        note 1: charset argument not supported
    // *     example 1: htmlspecialchars("<a href='test'>Test</a>", 'ENT_QUOTES');
    // *     returns 1: '&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;'
    // *     example 2: htmlspecialchars("ab\"c'd", ['ENT_NOQUOTES', 'ENT_QUOTES']);
    // *     returns 2: 'ab"c&#039;d'
    // *     example 3: htmlspecialchars("my "&entity;" is still here", null, null, false);
    // *     returns 3: 'my &quot;&entity;&quot; is still here'

    var optTemp = 0, i = 0, noquotes= false;
    if (typeof quote_style === 'undefined' || quote_style === null) {
        quote_style = 2;
    }
    if(string == undefined){
        string = '';
    }
    string = string.toString();
    if (double_encode !== false) { // Put this first to avoid double-encoding
        string = string.replace(/&/g, '&amp;');
    }
    string = string.replace(/</g, '&lt;').replace(/>/g, '&gt;');

    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE' : 1,
        'ENT_HTML_QUOTE_DOUBLE' : 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE' : 4
    };
    if (quote_style === 0) {
        noquotes = true;
    }
    if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i=0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
            if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            }
            else if (OPTS[quote_style[i]]) {
                optTemp = optTemp | OPTS[quote_style[i]];
            }
        }
        quote_style = optTemp;
    }
    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
        string = string.replace(/'/g, '&#039;');
    }
    if (!noquotes) {
        string = string.replace(/"/g, '&quot;');
    }

    return string;
}

//fw_notify_msg("test", "rm", "notify")
function fw_notify_msg(text, notify_id, type, append_to, append_where, close_element){
    //info
    var notify_icon = 'ui-state-highlight';
    var icon_show = ' ui-icon-info';

    if (type == 'error') {
        notify_icon = 'ui-state-error';
        icon_show = ' ui-icon-alert';
    } else if (type == 'info') {
        notify_icon = 'ui-state-highlight';
        icon_show = ' ui-icon-info';
    } else if (type == 'notify') {
        notify_icon = 'ui-state-default';
        icon_show = ' ui-icon-info';
    }

    var close_icon = '';
    if (close_element == undefined) {
        close_element = 0;
    }
    if (close_element > -1) {
        //display icon remove
        close_icon = '<span class="ui-icon ui-icon-close fw-point" style="float: right; margin-top: -8px; margin-right: -5px;" onclick="$(\'#'+notify_id +'\').hide();"></span>';
    }

    var content = '<div id="' + notify_id + '" class="' + notify_icon + ' ui-corner-all" style="padding: 0.7em; margin-bottom: 5px;"><p><span class="ui-icon' + icon_show + '" style="float: left; margin-right: 0.3em;"></span>' + close_icon + text + '</p></div>';

    if (append_to == undefined) {
        return content;
    } else {
        $("#"+notify_id).remove();
        if (append_where == undefined || append_where == 'A') {
            append_to.after(content);
        } else {
            append_to.before(content);
        }
    }

    if (close_element > 0) {
        setTimeout("$(\'#"+notify_id+"\').hide()",(parseFloat(close_element*1)*1000));
    }
}

function console_info(text){
    if (typeof console != "undefined" && typeof console.debug != "undefined") {
        console.info(text);
    }
}

$.extend({URLEncode:function(c){var o='';var x=0;c=c.toString();var r=/(^[a-zA-Z0-9_.]*)/;
  while(x<c.length){var m=r.exec(c.substr(x));
    if(m!=null && m.length>1 && m[1]!=''){o+=m[1];x+=m[1].length;
    }else{if(c[x]==' ')o+='+';else{var d=c.charCodeAt(x);var h=d.toString(16);
    o+='%'+(h.length<2?'0':'')+h.toUpperCase();}x++;}}return o;},
URLDecode:function(s){var o=s;var binVal,t;var r=/(%[^%]{2})/;
  while((m=r.exec(o))!=null && m.length>1 && m[1]!=''){b=parseInt(m[1].substr(1),16);
  t=String.fromCharCode(b);o=o.replace(m[1],t);}return o;}
});

function fw_quickSearchResult_value (id, set_value) {
    if ( typeof(fw_quickSearchResult_value.val) == "undefined" ) {
        fw_quickSearchResult_value.val = new Object();
    }

    if ( typeof(fw_quickSearchResult_value.val[id]) == "undefined" ) {
        fw_quickSearchResult_value.val[id] = false;
    }

    if ( typeof(set_value) != "undefined" ) {
        fw_quickSearchResult_value.val[id] = set_value;
    }

    return fw_quickSearchResult_value.val[id];
}

function fw_quickSearchResult_link (id, set_link) {
    if ( typeof(fw_quickSearchResult_link.val) == "undefined" ) {
        fw_quickSearchResult_link.val = new Object();
    }

    if ( typeof(fw_quickSearchResult_link.val[id]) == "undefined" ) {
        fw_quickSearchResult_link.val[id] = "";
    }

    if ( typeof(set_link) != "undefined" ) {
        fw_quickSearchResult_link.val[id] = set_link;
    }

    return fw_quickSearchResult_link.val[id];
}

function fw_quickSearchResult_param (id, obj , clear) {
    if ( typeof(fw_quickSearchResult_param.val) == "undefined" || clear == true) {
        fw_quickSearchResult_param.val = new Object();
    }

    if ( typeof(fw_quickSearchResult_param.val[id]) == "undefined" ) {
        fw_quickSearchResult_param.val[id] = new Object();
    }

    if ( typeof(obj) == "object" ) {
        for (var i in obj) {
            fw_quickSearchResult_param.val[id][i] = obj[i];
        }
    }

    return fw_quickSearchResult_param.val[id];
}

function entity_limit_per_page(e, entity_key){
    var v = $(e).val();
    var $form = $("#fw-search_form_"+entity_key);
    $form.find("select[name$='[limit]']").val(v);
    $form.submit();
}

function fw_selector_update(entity_key){
    if (count_selected[entity_key] > 0) {
        var selector = pattern_selector.replace('{items_selected}', count_selected[entity_key]).replace('{items_all}', search_checksum[entity_key]);
        $("#"+entity_key+"_selector").html(selector);
        $("#"+entity_key+"_selector_action").show();
    } else {
        var item_display = '';
        var page = $("#"+entity_key+"_search_result").find("div.fw-pages:first").find("span.active").text();
        if (page == '') {
            page = $("#"+entity_key).find("div.fw-pages:first").find("span.active").text();
        }
        var per_page = $("#"+entity_key+"_search_result").find("select[name=limit]").val();
        if (per_page == undefined) {
            per_page = per_page_session;
        }
        page--;
        if (page == -1 || page == 0) {
            var first_num = 1;
            if (search_checksum[entity_key] <= per_page) {
                var last_record = search_checksum[entity_key];
            } else {
                var last_record = per_page;
            }
        } else if (search_checksum[entity_key] > per_page) {
            var first_num = (parseFloat(page*1)*parseFloat(per_page*1));
            first_num++;
            var last_record = parseFloat(first_num) + parseFloat(per_page) - 1;
        }
        if (last_record > search_checksum[entity_key] || last_record < 1) {
            last_record = search_checksum[entity_key];
        }
        var selector = pattern_selected_none.replace('{items_display}', first_num + "-" + last_record).replace('{items_all}', search_checksum[entity_key]);
        $("#"+entity_key+"_selector").html(selector);
        $("#"+entity_key+"_selector_action").hide();
    }
}

function ajax_send(e,attr){
    if (attr == undefined) {
        attr = 'rel';
    }
    $.get($(e).attr(attr));
}

//function countmein(session_time) {
//    $("body").prepend(session_time+" | ");
//    var interval_t = 1000;
//    var x_time = session_time - interval_t;
//    if(x_time <= 1) {//load login form via ajax
//     $.get("check_session.php?check_session=" + session_id, function(data){
//         if (data <= 1) {
//            $("#session_box").hide();
//    //        alert(x_time);
//            show_popup_link(this, 'ajax=1&mod=login', 1, 650);
//            //start checking real expiry of session
//            checkreloadsession();
//            return false;
//        } else {
//            setTimeout("countmein("+ (data*1000) +")", interval_t);
//            return false;
//        }
//     });
//    } else if(x_time <= 10000) {//notify 300000
//        $("#session_box").html("Session Time left: " + x_time).fadeIn("slow");
//        setTimeout("countmein("+ x_time +")", interval_t);
//    } else {
//        setTimeout("countmein("+ x_time +")", interval_t);
//    }
//}
var display_login = 0;
function countmein(session_time) {
    var interval_t = 1000;
    var login_info_display = 300000;//300000
    var x_time = session_time - interval_t;
    if (x_time%30000 == 0 && x_time <= (login_info_display+30000)) {
//        $("body").prepend("before | ");
        $.ajax({
         type: "GET",
         url: "check_session.php?check_session=" + session_id,
         async: false,
         success: function(data){
            x_time = data*1000;//change into miliseconds
         }
     });
//        $("body").prepend("checked time" + x_time + " ||| ");
    }

//    $("body").prepend("check " + x_time + "| ");
    if(x_time <= 1) {//load login form via ajax
            if (display_login == 0){
                $("#session_box").hide();
                show_popup_link(this, 'ajax=1&mod=login', 1, 650,'',1);
            }
            display_login = 1;
    } else if(x_time <= login_info_display) {//notify 300000
        var x = x_time / 1000;
        var seconds = x % 60;
        x /= 60;
        var minutes = x % 60;
        if($("#session_box").css("display") == 'block') {
            $("#session_box").html("Session Time left: " + Math.floor(minutes) + ":" + Math.floor(seconds));
        } else {
            $("#session_box").html("Session Time left: " + Math.floor(minutes) + ":" + Math.floor(seconds)).fadeIn("slow");
        }
        if (display_login == 1) {
//            $(".ui-dialog").dialog("close");
//            $("div.ui-dialog").remove();
            closePopup();
            display_login = 0;
        }
    } else {
        if($("#session_box").css("display") == 'block') {
            $("#session_box").hide();
            //close popup with login
        }
        if (display_login == 1) {
//            $(".ui-dialog").dialog("close");
//            $("div.ui-dialog").remove();
            closePopup();
            display_login = 0;
        }
    }
    setTimeout("countmein("+ x_time +")", interval_t);
}

function fw_number_prepare (num, decimal) {
    if (!decimal) {
        decimal = 2;
    }
    var rounder = Math.pow(10,decimal);
    return Math.round(num*rounder)/rounder;
}

function fw_edit_translation(e, event) {
    event.preventDefault();
    $(e).attr("title", "edit translation");
    var key = atob($(e).attr("word_key_id"));
    eval("show_popup_link( e, \'ajax=1&mod=settings&view=translations&link=edit&other_lang=2&replace=1&word_key_id=" + key + "\', 0, \'auto\', 1)");
}

function gain_verify_item(t, msg, url){
    $.get("index.php?ajax=1&"+url, {
        popup_id: popID
    },
    function(data){
        loadPopup(data,400,0,0,'#contentArea',0);
    });
}

function fw_set_actions_menu(entity_key) {
    var m = $("#"+entity_key+"_selector_content").parent().html();
    $("#"+entity_key+"_selector_content").parent().remove();
    $("#contentArea").after(m);
    $("#"+entity_key+"_selector_content").css({"position": "absolute", "width": "150px", "display": "none"}).menu();

    $("#actions_"+entity_key).button({
//        icons: {
//            secondary: "ui-icon-triangle-1-s"
//        }
    }).click( function(){
        var offset = $("#actions_"+entity_key).offset();
        $("#"+entity_key+"_selector_content").css({"left": offset.left, "top": offset.top+25}).toggle();
    }).blur(function(){
        $("#"+entity_key+"_selector_content").hide();
    });
}

function gain_bind_ajax_button (elem_obj, elem_url, return_alert) {
    if(return_alert === undefined) {
        return_alert = 1;
    }
    $(elem_obj).click(function(){
        var $click_button = $(this);
        var label_tmp = $click_button.val();
        $click_button.prop("disabled", true).val("loading ..").removeClass("red").addClass("gray");
        $.get(elem_url, function(data){
            if (return_alert === 1) {
                alert(data);
            }
            $click_button.prop("disabled", false).val(label_tmp).removeClass("gray").addClass("red");
        });
    });
}
