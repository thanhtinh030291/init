<!DOCTYPE html>
<html lang="vn">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title', config('app.name'))</title>
        <meta name="description" content="{{ config('app.name') }}">
        <meta name="author" content="{{ config('app.name') }}">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name='key-notify' content="{{ config("constants.VAPID_PUBLIC_KEY") }}">
        <!-- Favicon -->
        <link rel="shortcut icon" href="{{asset('images/favicon.ico')}}">
        <!-- Google Font: Source Sans Pro -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" rel="stylesheet" type="text/css">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{asset("plugins/fontawesome-free/css/all.min.css?vision=") .$vision }}" rel="stylesheet" type="text/css">
        <!-- Ionicons -->
        <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css">
        <!-- Tempusdominus Bootstrap 4 -->
        <link rel="stylesheet" href="{{asset("plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css?vision=") .$vision }}" rel="stylesheet" type="text/css">
        <!-- iCheck -->
        <link rel="stylesheet" href="{{asset("plugins/icheck-bootstrap/icheck-bootstrap.min.css?vision=") .$vision }}" rel="stylesheet" type="text/css">
        <!-- JQVMap -->
        <link rel="stylesheet" href="{{asset("plugins/jqvmap/jqvmap.min.css?vision=") .$vision }}" rel="stylesheet" type="text/css">
        <!-- bottraps style -->
        <link rel="stylesheet" href="{{asset("css/bootstrap.css?vision=") .$vision }}" rel="stylesheet" type="text/css">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{asset("css/adminlte.min.css?vision=") .$vision }}" rel="stylesheet" type="text/css">
        <!-- overlayScrollbars -->
        <link rel="stylesheet" href="{{asset("plugins/overlayScrollbars/css/OverlayScrollbars.min.css?vision=") .$vision }}" rel="stylesheet" type="text/css">
        <!-- Daterange picker -->
        <link rel="stylesheet" href="{{asset("plugins/daterangepicker/daterangepicker.css?vision=") .$vision }}" rel="stylesheet" type="text/css">
        <!-- summernote -->
        <link rel="stylesheet" href="{{asset("plugins/summernote/summernote-bs4.min.css?vision=") .$vision }}" rel="stylesheet" type="text/css">
        <!-- aletr sweet -->
        <link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css?vision=') .$vision }}" rel="stylesheet" type="text/css">
        <!-- select2 -->
        <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css?vision=') .$vision }}" rel="stylesheet" type="text/css">
        
        @yield('stylesheets')
    </head>
    <body class="hold-transition sidebar-mini layout-fixed">
        <div class="loader"></div>
        <div class="wrapper">
            <!-- top bar navigation -->
            @include('layouts.admin.top_bar_navigation')
            <!-- End Navigation -->
            <!-- Left Sidebar -->
            @include('layouts.admin.left_sidebar')
            <!-- End Sidebar -->
            <div class="content-wrapper">
                <div class="content-header">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-xl-12">
                                @include('layouts.admin.message')
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Start content -->
                <div class="content">
                    <div class="container-fluid">
                        @yield('content')
                    </div>
                </div>
                <!-- END content -->
            </div>
            <!-- END content-page -->
            <!-- Start footer -->
            @include('layouts.admin.footer')
            <!-- END footer -->
        </div>
        <!-- END main -->
        
        <script type='text/javascript' src="{{ asset('plugins/jquery/jquery.min.js?vision=') .$vision }}"></script>
        <!-- jQuery UI 1.11.4 -->
        <script type='text/javascript' src="{{ asset('plugins/jquery-ui/jquery-ui.min.js?vision=') .$vision }}"></script>
        <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
        <script>
        $.widget.bridge('uibutton', $.ui.button)
        </script>
        <!-- Bootstrap 4 -->
        <script type='text/javascript' src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js?vision=') .$vision }}"></script>
        <!-- ChartJS -->
        <script type='text/javascript' src="{{ asset('plugins/chart.js/Chart.min.js?vision=') .$vision }}"></script>
        <!-- Sparkline -->
        <script type='text/javascript' src="{{ asset('plugins/sparklines/sparkline.js?vision=') .$vision }}"></script>
        
        <!-- jQuery Knob Chart -->
        <script type='text/javascript' src="{{ asset('plugins/jquery-knob/jquery.knob.min.js?vision=') .$vision }}"></script>
        <!-- daterangepicker -->
        <script type='text/javascript' src="{{ asset('plugins/moment/moment.min.js?vision=') .$vision }}"></script>
        <script type='text/javascript' src="{{ asset('plugins/daterangepicker/daterangepicker.js?vision=') .$vision }}"></script>
        <!-- Tempusdominus Bootstrap 4 -->
        <script type='text/javascript' src="{{ asset('plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js?vision=') .$vision }}"></script>
        <!-- Summernote -->
        <script type='text/javascript' src="{{ asset('plugins/summernote/summernote-bs4.min.js?vision=') .$vision }}"></script>
        <!-- Summernote -->
        <script type='text/javascript' src="{{ asset('plugins/summernote/summernote-bs4.min.js?vision=') .$vision }}"></script>
        <!-- alert sweest -->
        <script type='text/javascript' src="{{ asset('plugins/sweetalert2/sweetalert2.min.js?vision=') .$vision }}"></script>
        
        <!-- alert sweest -->
        <script type='text/javascript' src="{{ asset('plugins/select2/js/select2.min.js?vision=') .$vision }}"></script>

        <!-- AdminLTE App -->
        <script type='text/javascript' src="{{ asset('js/adminlte.js?vision=') .$vision }}"></script>
        <!-- AdminLTE for demo purposes -->
        <script type='text/javascript' src="{{ asset('js/demo.js?vision=') .$vision }}"></script>
        <!-- custom -->
        <script type='text/javascript' src="{{ asset('js/custom.js?vision=') .$vision }}"></script>
        <!-- The core Firebase JS SDK is always required and must be listed first -->
        <script src="https://www.gstatic.com/firebasejs/8.6.1/firebase-app.js"></script>

        <!-- TODO: Add SDKs for Firebase products that you want to use
            https://firebase.google.com/docs/web/setup#available-libraries -->
        <script src="https://www.gstatic.com/firebasejs/8.6.1/firebase-analytics.js"></script>
        <script src="https://www.gstatic.com/firebasejs/8.6.1/firebase-messaging.js"></script>

        <script>
        // Your web app's Firebase configuration
        // For Firebase JS SDK v7.20.0 and later, measurementId is optional
        var firebaseConfig = {
            apiKey: "AIzaSyBTHTKBDMg9feCwbB5Mp9ceiR-kR3QFL3M",
            authDomain: "pacific-cross.firebaseapp.com",
            projectId: "pacific-cross",
            storageBucket: "pacific-cross.appspot.com",
            messagingSenderId: "501542859634",
            appId: "1:501542859634:web:0274ffd7f050783f55a3eb",
            measurementId: "G-W2HN0MDWL3"
        };
        // Initialize Firebase
        firebase.initializeApp(firebaseConfig);
        firebase.analytics();
        
        
        const messaging = firebase.messaging();
	
        messaging.requestPermission()
            .then(function () {
            //MsgElem.innerHTML = "Notification permission granted." 
                console.log("Notification permission granted.");

                // get the token in the form of promise
                return messaging.getToken()
            })
            .then(function(token) {
            // print the token on the HTML page     
            console.log(token);
            
            
            
            })
        .catch(function (err) {
            console.log("Unable to get permission to notify.", err);
        });
        </script>
        
        <!-- BEGIN Java Script for this page -->
        @yield('scripts')
        <!-- END Java Script for this page -->
    </body>
</html>
