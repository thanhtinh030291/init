$(document).ready(function()
{
    $("body").drawer();
    $(".drawer_menu_with_sub").click(function()
    {
        $submenu_header = $(this);
        if($(this).next("span").is(":hidden"))
        {
            $(this).next("span").slideDown(100);
            $submenu_header.addClass("sub_menu_open_drawer_background");
        }
        else
        {
            $(this).next("span").slideUp(100, function()
          {
                $(this).hide();
                $submenu_header.removeClass("sub_menu_open_drawer_background");
            });
        }
    });
});