

// confirm before submit (add class .needs-validation)
$('.btn-needs-validation').click(function(event){
    var form = jQuery(".needs-validation");
    Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
    if (result.isConfirmed) {
        form.submit();
    }
    })
    
});

$(document).ready(function(){
    $('.select2').select2();
    $(".active").parent().parent().parent().addClass("menu-open").addClass("menu-is-opening");
});
