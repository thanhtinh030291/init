$(document).ready(function()
{
    $("#list-filter").change(function()
    {
        this.form.submit();
    });

    $('#add-filter-form').validate(
    {
        errorElement: "em",
        errorPlacement: function(error, element)
        {
            error.addClass("help-block");
            element.parents(".form-group").addClass("has-feedback");
            if (element.prop("type") === "checkbox")
            {
                error.insertAfter(element.parent( "label" ));
            }
            else
            {
                error.insertAfter(element);
            }
        },
        highlight: function (element, errorClass, validClass)
        {
            var parent = $(element).parents(".form-group");
            parent.addClass("has-success");
            parent.removeClass("has-error");
        },
        unhighlight: function (element, errorClass, validClass)
        {
            var parent = $(element).parents(".form-group");
            parent.addClass("has-success");
            parent.removeClass("has-error");
        }
    });

    $('#add-filter-form').submit(function()
    {
        var builder = $('#add-filter-conditions-builder')
        var condition = builder.queryBuilder('getSQL');
        if (condition.sql.length)
        {
            $('#add-filter-conditions').val(condition.sql);
        }
    });

    $("#export-form").submit(function()
    {
        $('#export-modal').modal('hide');

        var selectedRows = table.column(0).checkboxes.selected();
        $.each(selectedRows, function(index, rowId)
        {
            $("#export-form").append(
                $('<input>').attr('type', 'hidden').attr('name', 'rows[]').val(rowId)
            );
        });
    });

    $("#export-all-form").submit(function()
    {
        $('#export-all-modal').modal('hide');

        var selectedRows = table.column(0).checkboxes.selected();
        $.each(selectedRows, function(index, rowId)
        {
            $("#export-all-form").append(
                $('<input>').attr('type', 'hidden').attr('name', 'rows[]').val(rowId)
            );
        });
    });

    $("#import-button").on('click', function()
    {
        var finder = new CKFinder();
        finder.selectActionFunction = function(fileUrl)
        {
            $("#import").val(fileUrl);
        };
        finder.popup();
    });

    $("#import-form").validate(
    {
        showErrors: function(errorMap, errorList)
        {
            $.each(this.successList, function(index, value)
            {
                var parents = $(value).parents('.form-group');
                parents.addClass('has-success');
                parents.removeClass('has-error');
                return $(value).popover('hide');
            });
            return $.each(errorList, function(index, value)
            {
                var popover = $(value.element).popover(
                {
                    trigger: 'manual',
                    placement: 'top',
                    content: value.message,
                    template: '<div class="popover">' +
                        '<div class="arrow"></div>' +
                        '<div class="popover-inner">' +
                            '<div class="popover-content text-danger bg-danger">' +
                                '<p></p>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                });

                var parents = $(value.element).parents('.form-group');
                parents.addClass('has-error');
                parents.removeClass('has-success');

                popover.data('bs.popover').options.content = value.message;
                return $(value.element).popover('show');
            });
        }
    });
});
