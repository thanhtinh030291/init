function load_pagination(e, page)
{
    var form = $(e).closest("form");
    form.find("input[name=pagination]").val(page);
    form.find("button[name=search]").click();
}

function policy_load(e)
{
    $(".pol_item").removeClass("active");
    $(e).addClass("active");

    $("#policy_search_listing").removeClass("hidden-xs");
    $("#policy_search_listing").addClass("hidden-xs");
    $("#policy_search_detail").removeClass("hidden-xs");
    if ($(e).attr("ptype") == "pl")
    {
        var item_key = $(e).attr("item_key");
        $(".pol_detail").hide();
        $("#"+item_key).show();

//        files/logo/pchi_report.png
        var img_path = $(e).attr("alias");
        $("#insurer_img").attr("src", "/app/client/res/images/pchi_normal.png");

        $.post("index.php?ajax=1&mod=policy", {"keep_history": 1, "policy_no": $(e).attr("policy_no"), "id_insurer": $("#id_insurer").val()});
        $("html, body").animate({ scrollTop: 0 }, 500);
    }
}

function policyBackToList()
{
    $("#policy_search_listing").removeClass("hidden-xs");
    $("#policy_search_detail").removeClass("hidden-xs");
    $("#policy_search_detail").addClass("hidden-xs");
}

function pageOnLoad(loading)
{
    if (loading)
    {
        $(".drawer-header").hide();
        $(".modal").modal(
        {
            backdrop: "static",
            show: true
        });
    }
    else
    {
        $(".drawer-header").show();
        $("#myModal").modal("hide");
    }
}

function hbs_complete_proccess(e, id)
{
    var current_value = $(e).val();
    var prompt_label = lng["hbs_complete_done"];
    if (current_value == 1)
    {
        prompt_label = lng["hbs_complete_undone"];
    }
    if(confirm(prompt_label))
    {
        $.get("index.php?ajax=1&mod=hbs_download&view=complete&id="+id + "&current="+current_value, function(data)
        {
            if (current_value == 0)
            {
                $(e).prop("checked", "checked").val(1).next("span").text(data);
            }
            else
            {
                $(e).prop("checked", "").val(0).next("span").text("");
            }
        });
    }
    else
    {
        if (current_value == 0)
        {
            $(e).prop("checked", "");
        }
        else
        {
            $(e).prop("checked", "checked");
        }
    }
}
