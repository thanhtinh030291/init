

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

if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
  
      navigator.serviceWorker.register('/firebase-messaging-sw.js')
        .then(registration => {
  
          //Confirm user permission for notification
          Notification.requestPermission()
            .then(permission => {
  
              messaging.getToken().then(
                token => {
                  console.log(token)
                })
  
  
              if (permission === 'granted') {
                //If notification is allowed
                console.log('granted!!!!!')
                navigator.serviceWorker.ready.then(p => {
  
                  p.pushManager.getSubscription().then(subscription => {
  
                    if (subscription === null) {
  
                      //If there is no notification subscription, register.
                      let re = p.pushManager.subscribe({
                        userVisibleOnly: true
                      })
  
                    }
                  })
  
                })
  
              } else {
                //If notification is not allowed
                console.log(permission)
              }
            })
        })
    })
  }
