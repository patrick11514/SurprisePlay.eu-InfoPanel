<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>%%page%% | %%domain%%</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="//%%domain%%/public/imgs/favicon.ico">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="//%%domain%%/public/plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="//%%domain%%/public/dist/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

</head>
<style>
@media (min-width: 1000px) {
    .tlacitko {
        visibility:hidden;
    }
}
</style>
<body class="hold-transition sidebar-mini">
<!-- Site wrapper -->
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link tlacitko" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Notifications Dropdown Menu -->
            <li class="nav-item">
                <a class="nav-link" href="./?logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a class="brand-link">
            <img src="//%%domain%%/public/imgs/nav_icon.png" class="brand-image img-circle elevation-3" alt="Logo" style="opacity: .8">
            <span class="brand-text font-weight-light" style="color:white;text-align:center;"><b>SurprisePlay</b>.eu</span>
        </a>        
        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="%%skin_URL%%" style="width: 45px;margin-top:15%" alt="User Image">
                </div>
                <div class="info">
                    <span style="color:#C2C7D0;" class="d-block">%%username%%</span>
                    <span class="d-block"><span class="badge badge-dark" style="color:%%RANK_COLOR%%">%%rank%%</span></span>
                </div>
            </div>

            <!-- Sidebar Menu -->
            %%NAVIGATION%%

            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        %%ERRORS%%
        %%messages%%
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>%%page_name%%</h1>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        %%content%%
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
        <div class="container" style="width:100%;max-width:100%;">
            <div class="row">
                <div class="col-sm" style="text-align:left">
                    <b>Verze:</b> %%version%%
                </div>
                <div class="col-sm" style="text-align:center">
                    %%own%%
                </div>
                <div class="col-sm" style="text-align:right">
                    <strong>%%copyright%%</strong>
                </div>
            </div>
        </div>
        
    </footer>

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="//%%domain%%/public/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="//%%domain%%/public/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="//%%domain%%/public/dist/js/adminlte.min.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="//%%domain%%/public/dist/js/demo.js"></script>
</body>
</html>
