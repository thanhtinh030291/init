$(document).ready(function()
{
    $("#accept_condition").change(function()
    {
        if($("#accept_condition").prop("checked"))
        {
            $("#modal_close_button").removeAttr("disabled");
            $("#modal_close_button").css({ "background-color" : "#337AB7" });
            $("#modal_close_button").css({ "border-color" : "#2E6DA4" });
        }
        else
        {
            $("#modal_close_button").prop("disabled", true);
            disabled_button_color_set();
        }
    });

    $("#modal_close_button").click(function()
    {
        $("#error-modal").modal("hide");
        $("#modal_close_button").prop("disabled", true);
        disabled_button_color_set();
        $("#accept_condition").prop("checked", false);
    });

    disabled_button_color_set();
    $("#modal_close_button").css({ "color" : "#FFFFFF" });

    $("body").css("padding-right", "0px !important");

    $("#policy_search_listing").find(".pol_item.active").click();
});

function disabled_button_color_set()
{
    $("#modal_close_button").css({ "background-color" : "#D8D8D8" });
    $("#modal_close_button").css({ "border-color" : "#C9C9C9" });
}