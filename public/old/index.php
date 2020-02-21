<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Adminka</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <!-- Data Tables -->
    <link rel="stylesheet" href="../plugins/datatables-bs4/css/dataTables.bootstrap4.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>

<body class="hold-transition sidebar-mini">
    <!-- Site wrapper -->
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="./logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>

        <?php include_once './private/pages/sidebar.php'; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <br />
            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Registrováno uživatelů</span>
                                <span class="info-box-number">10000 </span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-cube"></i></span>
                            
                            <div class="info-box-content">
                                <span class="info-box-text">Minecraft</span>
                                <span
                                    class="info-box-number">50/100</span>

                                <div class="progress">
                                    <div class="progress-bar"
                                        style="width: 50%">
                                    </div>
                                </div>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fab fa-teamspeak"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Teamspeak</span>
                                <span
                                    class="info-box-number">20/40</span>

                                <div class="progress">
                                    <div class="progress-bar"
                                        style="width: 50%">
                                    </div>
                                </div>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>

                            <div class="info-box-content">
                                <span class="info-box-text">Zabanování hráči</span>
                                <span class="info-box-number">10</span>

                                <div class="progress">
                                    <div class="progress-bar"
                                        style="width: 20%">
                                    </div>
                                </div>
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h4>Todo List</h4>
                        <table class="table table-condensed">
                            <thead>
                                <tr>
                                    <th style="width: 20px"><center>Vypracuje</center></th>
                                    <th><center>Práce</center></th>
                                    <th style="width: 20px"><center>Zadal</center></th>
                                    <th style="width: 180px"><center>Datum</center></th>
                                    <th style="width: 80px"><center>Akce</center></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><center>patrick115</center></td>
                                    <td>Lorem ipsum dolor sit amet, consectetuer</td>
                                    <td><center>Krille69</center></td>
                                    <td><center>23:43 18.10.2019</center></td>
                                    <td><button type="button" class="btn btn-block btn-danger">Smazat</button></td>
                                </tr>
                                <tr>
                                    <td><center>Krille69</center></td>
                                    <td>adipiscing elit. In enim a arcu imperdiet malesuada.</td>
                                    <td><center>ZeroCz_</center></td>
                                    <td><center>5:21 5.4.2003</center></td>
                                    <td><button type="button" class="btn btn-block btn-danger">Smazat</button></td>
                                </tr>
                                <tr>
                                    <td><center>_Hauf</center></td>
                                    <td>Curabitur sagittis hendrerit ante. Fusce wisi.</td>
                                    <td><center>patrick115</center></td>
                                    <td><center>12:11 1.1.1611</center></td>
                                    <td><button type="button" class="btn btn-block btn-danger">Smazat</button></td>
                                </tr>
                                <tr>
                                    <td><center>ImPsycho</center></td>
                                    <td>Quisque tincidunt scelerisque libero.</td>
                                    <td><center>Dend4X</center></td>
                                    <td><center>23:59 31.12.3033</center></td>
                                    <td><button type="button" class="btn btn-block btn-danger">Smazat</button></td>
                                </tr>
                            </tbody>
                        </table>
                        <hr>
                        <center>
                            <form action="" method="post">
                                <div class="row" style="max-width:80%;">
                                    <div class="col-sm">
                                        <input class="form-control form-control-sm" name="work" type="text" placeholder="Zadej úkol" required>
                                    </div>
                                    <div class="col-sm">
                                        <select class="form-control form-control-sm" name="worker" required>
                                            <option value="">Vyber člena AT</option>
                                            <option value="Krille69">Krille69</option>
                                            <option value="ImPsycho">ImPsycho</option>
                                            <option value="_Hauf">_Hauf</option>
                                            <option value="patrick115">patrick115</option>
                                        </select>
                                    </div>
                                    <div class="col-sm">
                                        <button type="submit" class="btn btn-block btn-primary btn-sm">Přidat</button>
                                    </div>
                                </div>
                            </form>
                        </center>
                        <hr>
                    </div>
                </div>
                <br />
                        <div class="card">
                            <div class="card-body">
                                <h4>SMS Brána (posledních 10 plateb)</h4>
                                <div class="table-responsive">
                                    <table class="table table-condensed">
                                        <thead>
                                            <tr>
                                                <th style="width: 20px">
                                                    <center>#</center>
                                                </th>
                                                <th>
                                                    <center>Nick</center>
                                                </th>
                                                <th>
                                                    <center>Datum</center>
                                                </th>
                                                <th>
                                                    <center>Částka</center>
                                                </th>
                                                <th>
                                                    <center>Kód platby</center>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <center>12</center>
                                                </td>
                                                <td>
                                                    <center>patrick115</center>
                                                </td>
                                                <td>
                                                    <center>20:30 1.1.1999</center>
                                                </td>
                                                <td>
                                                    <center>20&nbsp;<span
                                                            class="badge badge-warning">kre</span></center>
                                                </td>
                                                <td>
                                                    <center>1546</center>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
            </section>
        </div>

        <!-- /.content -->

        <!-- /.content-wrapper -->

        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Version</b> 3.0.0-rc.5
            </div>
            <strong>Copyright &copy; 2014-<span id="year"></span> <a href="http://adminlte.io">AdminLTE.io</a>.</strong>
            All rights
            reserved.

        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->

        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../dist/js/adminlte.min.js"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="../dist/js/demo.js"></script>
    <!-- Year Script -->
    <script>
    $('#year').text(new Date().getFullYear());
    </script>

</body>

</html>