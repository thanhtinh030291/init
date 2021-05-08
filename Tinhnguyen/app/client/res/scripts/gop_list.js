$(document).ready(function()
{
    $('#error-info').html('');
    $('#benefit-info').html('');
    $('#gop-info').html('');
    var tokenName = $('#token-name').val();
    var tokenValue = $('#token-value').val();

    $("#loading-modal").modal("show");

    $.get(window.location.href + "?action=get-gop-info&" + tokenName + "_token=" + tokenValue, function(data)
    {
        $('#gop-info').html(data);
        $('#gop-info-header').show();
        $("#loading-modal").modal("hide");
    });

    $.get(window.location.href + "?action=get-benefit-info&" + tokenName + "_token=" + tokenValue, function(data)
    {
        $('#benefit-info').html(data);
        $('#benefit-info-header').show();
        $("#loading-modal").modal("hide");
    });
});
