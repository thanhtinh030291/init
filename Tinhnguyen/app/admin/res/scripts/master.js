function generatePassword()
{
    var possible = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                 + 'abcdefghijklmnopqrstuvwxyz'
                 + '0123456789![]{}()%&*$#^~@|';
    var text = '';
    for(var i = 0; i < 16; i++)
    {
        text += possible.charAt(
            Math.floor(
                Math.random() * possible.length
            )
        );
    }
    return text;
}

function toggle(name)
{
    var field = document.getElementById(name);
    var button = document.getElementById(name + '-button');
    var checkbox = document.getElementById('updates-' + name);

    checkbox.checked = field.disabled;
    if (button != null)
    {
        button.disabled = !field.disabled;
    }
    field.disabled = !field.disabled;
    return field.disabled;
}

function toggleHtml(name)
{
    CKEDITOR.instances[name].setReadOnly(
        toggle(name)
    );
}

function toggleCheckbox(name)
{
    $('#' + name).bootstrapSwitch('disabled', toggle(name));
}

function changeVersion(version)
{
    $('div[id^="version-"]').each(function(index, element)
    {
        $('#' + this.id).addClass('hide');
    });
    $('#version-' + version).removeClass('hide');
}

$(document).ready(function()
{
    $.datetimepicker.setLocale('en');
    $.fn.select2.defaults.set('theme', 'bootstrap');

    $(window).keydown(function(event)
    {
        if(event.keyCode == 13 && event.target.nodeName != 'TEXTAREA')
        {
          event.preventDefault();
          return false;
        }
    });

    $('input[type=checkbox][bootstrap=bootstrap]').bootstrapSwitch();

    $('select').select2(
    {
        containerCssClass: ":all"
    });

    $('select[multiple=multiple]').on("select2:select", function (e)
    {
        var element = e.params.data.element;
        var $element = $(element);

        $element.detach();
        $(this).append($element);
        $(this).trigger("change");
    });

    $.validator.addMethod(
        "regex",
        function(value, element, regexp)
        {
            var re = new RegExp(regexp);
            return this.optional(element) || re.test(value);
        },
        "Please check your input"
    );

    $('.form').tooltip(
    {
        selector: "[data-toggle=tooltip]",
        container: "body"
    });

    $("[data-toggle=popover]").popover();

    $('[show=loading]').submit(function()
    {
        $("#loading-modal").modal("show");
    });

    if ($('#page-wrapper').outerHeight(true) < $('#side-menu').outerHeight(true))
    {
        $('#page-wrapper').height($('#side-menu').outerHeight(true));
    }

    $("#file-browser").on('click', function()
    {
        var finder = new CKFinder();
        finder.selectActionFunction = function(api)
        {
            this.connector.app.execCommand('ViewFile');
            return false;
        };
        finder.popup();
    });
});
