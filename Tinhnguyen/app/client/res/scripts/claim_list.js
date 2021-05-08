$(document).ready(function()
{
    $('#claim-info').html('');
    $('#call-info').html('');
    var tokenName = $('#token-name').val();
    var tokenValue = $('#token-value').val();

    var isPCV = $('#is-pcv').val();
    if (isPCV === 'yes')
    {
        $.get(window.location.href + "?action=get-claim-info&" + tokenName + "_token=" + tokenValue, function(data)
        {
            $('#claim-info').html(data);
            $('#claim-info-header').show();
            $("#loading-modal").modal("hide");
        });

        $.get(window.location.href + "?action=get-call-info&" + tokenName + "_token=" + tokenValue, function(data)
        {
            $('#call-info').html(data);
            $('#call-info-header').show();
            $("#loading-modal").modal("hide");
        });
    }
});
