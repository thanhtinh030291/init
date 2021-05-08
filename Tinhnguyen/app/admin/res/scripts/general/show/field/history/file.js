$("#{$id}-button").on('click', function()
{
    var finder = new CKFinder();
    finder.selectActionFunction = function(fileUrl)
    {
        $("#{$id}").val(fileUrl);
    };
    finder.popup();
});
