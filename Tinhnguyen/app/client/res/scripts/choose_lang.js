$(document).ready(function()
{
    $(".flag").click(function()
    {
        $(".flag").removeClass("active");
        $(this).addClass("active");
        var choose_lang = $(this).attr("value");
        $("input[name=choose_lang]").val(choose_lang);
    });
});