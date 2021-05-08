$("#{$id}-gen-pass").click(function()
{
    var text = '';
    var re = new RegExp(/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/);

    do
    {
        text = generatePassword();
    }
    while (!re.test(text));

    $("#{$id}").val(text);
    $("#{$id}-show-pass-label").html(
        '{$showPassLabel}: <strong class="text-danger">' + text + '</strong>'
    );
});
