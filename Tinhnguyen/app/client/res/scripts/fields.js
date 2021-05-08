var name_prefix = new Array();
var name_suffix = new Array();
function fw_str_space_replace(str){
    if (str.substr(0, 1) == ' ') {
        str = str.substr(1, str.length);
        if (str.substr(0, 1) == ' ') {
            str = fw_str_space_replace(str);
        }
    }
    if (str.substr((str.length-1), str.length) == ' ') {
        str = str.substr(0, (str.length-1));
        if (str.substr((str.length-1), str.length) == ' ') {
            str = fw_str_space_replace(str);
        }
    }
    return str;
}

////////////////////////////// form_name ///////////////////////////////////////////
function fw_fields_name_check_word(e, key, type, trim_dot, name_last_name) {
    var full_name = fw_str_space_replace($(e).val());
    var $fw_name_fields_hidden = $("#fw_"+key+"_fields_hidden");

    $fw_name_fields_hidden.find("input").each(function(){
        $(this).val("");
    });
    if (full_name) {
        var part_name = full_name.split(" ");
        var n = part_name.length;
        //check prefix
        var prefix_check = part_name[0].toLowerCase();

        if (name_prefix[prefix_check] != undefined) {
            $fw_name_fields_hidden.find("input[name=fw_"+key+"_title]").val(name_prefix[prefix_check]);
            full_name = fw_str_space_replace(full_name.replace(part_name[0], ''));
        } else {
            $fw_name_fields_hidden.find("input[name=fw_"+key+"_title]").val("");
        }

        //check suffix
        var suffix_check = part_name[n-1].toLowerCase();
        if (name_suffix[suffix_check] != undefined) {
            $fw_name_fields_hidden.find("input[name=fw_"+key+"_suffix]").val(name_suffix[suffix_check]);
            full_name = fw_str_space_replace(full_name.replace(part_name[n-1], ''));
        } else {
            $fw_name_fields_hidden.find("input[name=fw_"+key+"_suffix]").val("");
        }

        tmp_name = fw_str_space_replace(full_name);
        var name_first = '';
        var name_last = '';
        var name_middle = '';
        var name_initial = '';
        var part_name3 = tmp_name.split(" ");
        var tmp_name3 = '';
        for (i = 0; i < part_name3.length; i++) {
            var cnt_ini = part_name3[i].length;
            if (cnt_ini < 3 && name_initial == '') {
                if (cnt_ini == 1 || part_name3[i][1] == '.') {
                    name_initial = part_name3[i];
                } else {
                    tmp_name3 = tmp_name3 + " " + part_name3[i];
                }
            } else {
                tmp_name3 = tmp_name3 + " " + part_name3[i];
            }
        }
        $fw_name_fields_hidden.find("input[name=fw_"+key+"_initials]").val(name_initial);

        var part_name2 = fw_str_space_replace(tmp_name3).split(" ");
        var n = part_name2.length;
        if (n == 1) {
            n = 2;
        }
        if (name_last_name == 'first') {
            if (part_name2[(n-1)] != undefined) {
                name_last = part_name2[(n-1)];

                name_first = fw_str_space_replace(tmp_name3.replace(name_last, ''));
                $fw_name_fields_hidden.find("input[name=fw_"+key+"_last]").val(name_last);
            } else {
                name_first = tmp_name3;
                $fw_name_fields_hidden.find("input[name=fw_"+key+"_last]").val('');
            }
            $fw_name_fields_hidden.find("input[name=fw_"+key+"_first]").val(fw_str_space_replace(name_first));
        } else {
            if (part_name2[0] != undefined) {
                name_last = part_name2[0];
                name_first = fw_str_space_replace(tmp_name3.replace(name_last, ''));
                $fw_name_fields_hidden.find("input[name=fw_"+key+"_last]").val(name_last);
            } else {
                name_first = tmp_name3;
                $fw_name_fields_hidden.find("input[name=fw_"+key+"_last]").val('');
            }
            $fw_name_fields_hidden.find("input[name=fw_"+key+"_first]").val(fw_str_space_replace(name_first));
        }

        //return full string
        var full_name2 = '';
        var display_name = '';
        $fw_name_fields_hidden.find("input").each(function(){
            if ($(this).val() != "" && $(this).val() != " ") {
                if ($(this).attr("name") != 'fw_'+key+'_initials' && $(this).attr("name") != 'fw_'+key+'_keep') {
                    full_name2 = full_name2 + $(this).val() + " ";

                    if ($(this).attr("name") != 'fw_'+key+'_title' && $(this).attr("name") != 'fw_'+key+'_suffix') {
                        display_name = display_name + $(this).val() + " ";
                    }
                }
            }
        });
        $("input[name=fw_"+key+"_display]").val(fw_str_space_replace(display_name));
        $("#fw_"+key+"_full").val(fw_str_space_replace(full_name2));
    }
}

function fw_fields_name_update_word(e, key) {
    var $popid = $(e).parents(".detailsInfo");
    var $item_id = $("#fw_"+key+"_fields_hidden");
    var full_name = '';
    var display_name = '';
    var name_custom = $popid.find("input[name="+key+"_custom]").is(":checked");
    $popid.find(".fw_"+key+"_fields").each(function(){
        $item_id.find("input[name=fw_"+$(this).attr("name")+"]").val($(this).val());
        if ($(this).val() != "") {
            if ($(this).attr("name") != key+'_initials' && $(this).attr("name") != 'fw_'+key+'_keep') {
                full_name = full_name + $(this).val() + " ";
                if (name_custom || (!name_custom && $(this).attr("name") != key+'_title' && $(this).attr("name") != key+'_suffix')) {
                    display_name = display_name + $(this).val() + " ";
                }
            }
        }
    });

    if (name_custom) {
        name_custom = 1;
    } else {
        name_custom = 0;
    }
    $("input[name="+key+"]").val(full_name);
    $("input[name=fw_"+key+"_keep]").val(name_custom);
    $("input[name=fw_"+key+"_display]").val(display_name.substring(0,(full_name.length - 1)));
    $("#fw_"+key+"_full").val(full_name.substring(0,(full_name.length - 1)));
//    closePopup();
    $( "#"+key+"_name_field" ).dialog( "close" );
}

function fw_field_name_load_form(data, key) {
    $( "#"+key+"_name_field" ).dialog( "open" );

//    loadPopup(data,0,0,0,"#contentArea");
    var $pop_id = $( "#"+key+"_name_field" ); //$("#popup"+popID);
    var $fw_name_fields_hidden = $("#fw_"+key+"_fields_hidden");
    $pop_id.find(".fw_"+key+"_fields").each(function(){
        var item_name = $(this).attr("name");
        var item_val = $fw_name_fields_hidden.find("input[name=fw_"+item_name+"]").val();
        $(this).val(item_val);
        if ($(this).val() == '' && item_val != '') {
            $(this).append('<option>'+item_val+'</option>').val(item_val);
        }
    });

    if ($fw_name_fields_hidden.find("input[name=fw_"+key+"_keep]").val() == 1) {
        $("input[name="+key+"_custom]").prop("checked", true);
    } else {
        $("input[name="+key+"_custom]").prop("checked", false);
    }
}

//////////////////////////// form_lookup ///////////////////////////////////////////
//quick search function
var attr = 0;
var quickSearchNew = new Array();
var quickSearchOld = new Array();
var quickSearchBox = new Array();
var idDefaultVal = new Array();
var quickSearchTimeOut = new Array();

function fw_quick_search(attr, defaultVal, link){
    if (attr == 'quick_search' && $('#quickSearch'+attr).val() == '') {
        return;
    }
    quickSearchNew['"'+attr+'"'] = $('#quickSearch'+attr).val();
    if ($('#quickSearch'+attr).val() != undefined) {
        if (quickSearchNew['"'+attr+'"'] == '' || (quickSearchNew['"'+attr+'"'] != defaultVal && quickSearchOld['"'+attr+'"'] != quickSearchNew['"'+attr+'"'] && quickSearchNew['"'+attr+'"'].length > 2)) {
            findPos('#quickSearch'+attr);
            quickSearchOld['"'+attr+'"'] = quickSearchNew['"'+attr+'"'];
            if(quickSearchBox['"'+attr+'"']!=1){
                quickSearchBox['"'+attr+'"'] = 1;
            }
            var post_param = fw_quickSearchResult_param (attr, {searchInfo: quickSearchNew['"'+attr+'"'], fajax: '1', n: attr}, true) ;
            $.post('index.php?'+link, post_param, function(data){
                findPos($("#quickSearch"+attr));
                if (data) {
                    if (attr == "report_search") {
                        //set position for report_search
                        loadPopup(data, 400, (posE[0]-228), (posE[1]+20), '#contentArea', 0, 0, 'fw_quick_search_close', undefined, 1);
                    } else {
                        loadPopup(data, 400, (posE[0]), (posE[1]+20), '#contentArea', 0, 0, 'fw_quick_search_close');
                    }
                    $("#quickSearch"+attr).focus();
                } else {
                    closePopup();//close old popup when no result
                }
            });
        }
    }
}

function fw_quick_search_close(event, ui, popID){
    quickSearchOld = new Array();
    $("#quickSearchquick_search").val("");
}

function fw_quick_search_tmp(attr, defaultVal, link){
    if (attr == 'quick_search' && $('#quickSearch'+attr).val() == '') {
        return;
    }
    quickSearchNew['"'+attr+'"'] = $('#quickSearch'+attr).val();
    if ($('#quickSearch'+attr).val() != undefined) {
        if (quickSearchNew['"'+attr+'"'] == '' || (quickSearchNew['"'+attr+'"'] != defaultVal && quickSearchOld['"'+attr+'"'] != quickSearchNew['"'+attr+'"'] && quickSearchNew['"'+attr+'"'].length > 2)) {
            findPos('#quickSearch'+attr);
            quickSearchOld['"'+attr+'"'] = quickSearchNew['"'+attr+'"'];
            if(quickSearchBox['"'+attr+'"']!=1){
                quickSearchBox['"'+attr+'"'] = 1;
            }
            var post_param = fw_quickSearchResult_param (attr, {searchInfo: quickSearchNew['"'+attr+'"'], fajax: '1', n: attr}, true) ;
            $.post('index.php?'+link, post_param, function(data){
                var a = findPos($("#quickSearch"+attr));
                loadPopup(data, 400, (posE[0]+1), (posE[1]+30), '#contentArea', 0);
            });
        }
    }
}

function fw_remove_lookup(e, attr){
    $(e).parent().css('display', 'none');
    $(e).parent().parent().children('input').val('');
    quickSearchOld = new Array();
    fw_quickSearchResult_value(attr, false);
}

//////////////////////////// form_email ///////////////////////////////////////////
function fw_email_validation(e){
    // $(($(e).parent())+' font').remove();
    $(e).parent().find('font').remove();
    var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    var address = $(e).val();
    if(reg.test(address) == false){
        if(address=='') {
            $(e).parent().children('span').html('');
        } else {
            $(e).parent().children('span').html(' <img src="im/alert.png" title="invalid" />');
        }
        return false;
    } else {
        $(e).parent().children('span').html(' <img src="im/accept.png" title="valid" />');
    }
}

////////////////////////////form_password ///////////////////////////////////////////
function getPassword(length, strength){
    function getRandomNum(lbound, ubound) {
        return (Math.floor(Math.random() * (ubound - lbound)) + lbound);
    }

    function getRandomChar(c_replace) {
        var numberChars = "0123456789";
        var lowerChars = "abcdefghijklmnopqrstuvwxyz";
        //var upperChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        var otherChars = "!@#$%"; //(){}[]<>
        var charSet = "";
        if (c_replace == 'c') charSet = lowerChars;
        if (c_replace == 'n') charSet = numberChars;
        if (c_replace == 's') charSet = otherChars;
        return charSet.charAt(getRandomNum(0, charSet.length));
    }

    function getRandomWord() {
        var consonants = "bcdfghjklmnpqrstvwxyz";
        var vowels = "aeiou";
        var con = consonants.charAt(getRandomNum(0, consonants.length));
        var vow = vowels.charAt(getRandomNum(0, vowels.length));
        var charSet = con + vow;
        return charSet;
    }

    function setCharAt(str,index,chr) {
        if(index > str.length-1) return str;
        return str.substr(0,index) + chr + str.substr(index+1);
    }

    if(length == null) length = 9;
    if(strength == null) strength = 3;
    else strength = strength - 1;

    var r = '', n = '', s = '';
    var str_lv = new Array(
        //lower, upper, number, special percentage
        Array("100", "0", "0", "0"),//level 1: lower
        Array("100", "30", "0", "0"),//level 2: lower, upper
        Array("75", "50", "25", "0"),//level 3: lower, upper, number
        Array("30", "20", "30", "20"),//level 4: lower, upper, number, special
        Array("20", "50", "80", "0")//test
    );
    var n_lower = Math.ceil((str_lv[strength][0]/100)*length);
    var n_upper = Math.ceil((str_lv[strength][1]/100)*n_lower);
    var p_upper = str_lv[strength][1];
    var n_number = Math.ceil((str_lv[strength][2]/100)*length);
    var n_special = Math.ceil((str_lv[strength][3]/100)*length);

    while(n_lower+n_number+n_special > length){
        if(n_number > 1) n_number = n_number - 1;
        else n_lower = n_lower - 1;
        if(n_lower+n_number+n_special <= length) break;
        if(n_special > 1) n_special = n_special - 1;
        else n_lower = n_lower - 1;
    }

    if(n_upper > 0) var count_upper = 0;
    if(n_number > 0) var count_number = 0;
    if(n_special > 0) var count_special = 0;
    //get number
    while(count_number < n_number){
        n += getRandomChar('n');
        count_number++;
    }
    //get special
    while(count_special < n_special){
        s += getRandomChar('s');
        count_special++;
    }

    var words = Math.floor(n_lower/2);
    if(n_lower%2 == 1) var one_char = 1;

    for(var i = 0; i < words; ++i) {
        if(getRandomNum(0,2) == 1 && (n.length > 0 || s.length > 0)){
            rnd_pos = r.length;
            rnd_val = n + s;
            n = '';
            s = '';
        }

        r += getRandomWord();

        //get only a char for letter number is odd
        if(one_char == 1 && getRandomNum(0,2) == 1){
            r += getRandomChar('c');
            one_char = 0;
        }
    }

    if((n.length > 0 || s.length > 0)){
        rnd_pos = r.length;
        rnd_val = n + s;
    }

    //get only a char for letter number is odd leftover
    if(one_char==1) r += getRandomChar('c');

    //set upper leftover
    while(count_upper < n_upper){
        for(i = 0; i < r.length; ++i){
            if(getRandomNum(0,100) < p_upper && p_upper > 0 && count_upper < n_upper) {
                r = setCharAt(r,i,r[i].toUpperCase());
                count_upper++;
            }
        }
    }

    if(n_number > 0 || n_special > 0){
        r = r.substr(0,rnd_pos) + rnd_val + r.substr(rnd_pos);
    }

    return r;
}

function password_set(img){
    var pass = getPassword();
//    $(img).prev().val(pass);
//    $(img).prev().prev().val(pass);
    $(img).prev().find("input").val(pass).keyup();
    $(img).prev().prev().find("input").val(pass).keyup();
}

function password_toggle_show(btn){
    var typ_pass = $(btn).prev().prev();
    var typ_txt = $(btn).prev().prev().prev();

    if($(typ_pass).css('display')=='none'){
        $(typ_pass).css('display','inline');
        $(typ_txt).css('display','none');
    }else{
        $(typ_pass).css('display','none');
        $(typ_txt).css('display','inline');
    }
}

function box_autocomplete_item(jdata, i, max) {
    var row = eval("("+ jdata +")");
    return row.name;
    //return i + "/" + max + ": \"" + row.name + "\" [" + row.email + "]";
}

function box_autocomplete_format(jdata, i, max) {
    var row = eval("("+ jdata +")");
    return row.name;
    //return i + "/" + max + ": \"" + row.name + "\" [" + row.email + "]";
}

function box_autocomplete_result(jdata) {
    var row = eval("("+ jdata +")");
    return row.name;
    //return row.name + " ["+ row.email +"]";
}