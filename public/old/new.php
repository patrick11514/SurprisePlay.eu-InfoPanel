<?php
use ZeroCz\Admin\Tickets\Ticket;
use ZeroCz\Admin\System;
use ZeroCz\Admin\Session;

require_once __DIR__ . '/private/php/init.inc.php';

$page = 3;

if (!$auth->Logged()) {
    System::redirect('./login.php');
}


$ticket = new Ticket(true);

if (isset($_POST['submit'])) {
    if ($ticket->validate(Ticket::TICKET_NEW, [$_POST['subject'], $_POST['message'], $_POST['type']])) {
        $ticket->createTicket();
    }
}
/*
if (isset($_POST['submit'])) {
    echo '<textarea>' . $ticket->purify($_POST['message']) . '</textarea>';
    exit;
}*/
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>AdminLTE 3 | Tickets</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <!-- DataTables -->
    <link rel="stylesheet" href="../plugins/datatables-bs4/css/dataTables.bootstrap4.css">

    <style>
    .subject {
        max-width: 40%;
    }

    .noresize {
        resize: none;
    }

    @media screen and (max-height: 575px) {

        #rc-imageselect,
        .g-recaptcha {
            transform: scale(0.77);
            -webkit-transform: scale(0.77);
            transform-origin: 0 0;
            -webkit-transform-origin: 0 0;
        }
    }

    @media only screen and (max-width: 1000px) {
        .subject {
            max-width: 100%;
        }
    }
    </style>
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
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="index.php" class="nav-link">Home</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#">
                        <i class="fas fa-th-large"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <?php include_once MAIN_DIR . '/../pages/sidebar.php'; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Nový ticket</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Vytvořit nový</li>
                            </ol>
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="card">
                    <div class="card-body">
                        <?php if (System::isError()): ?>
                        <div class="alert alert-dismissible alert-danger">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            <?= System::getError(); ?>
                        </div>
                        <?php endif; ?>
                        <form action="" method="post">
                            <div class="form-row">
                                <div class="col">
                                    <label for="subject">Předmět:</label>
                                    <input type="text" id="subject" name="subject" class="form-control" required>
                                </div>
                                <div class="col">
                                    <label for="type">Typ:</label>
                                    <select name="type" id="type" class="form-control" required>
                                        <option value=""></option>
                                        <option value="adasdasd">DEBUG</option>
                                        <?php foreach ($ticket->getTicketTypes() as $label => $option): ?>
                                        <optgroup label="<?= $label ?>">
                                            <?php foreach ($option as $value => $text): ?>
                                            <option value="<?= $value ?>"><?= $text ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="message">Zpráva:</label>
                                <textarea id="message" name="message" class="form-control" required></textarea>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary">Odeslat</button>
                        </form>
                    </div>
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Version</b> 3.0.0-rc.5
            </div>
            <strong>Copyright &copy; 2014-<?= System::getYear(); ?> <a
                    href="http://adminlte.io">AdminLTE.io</a>.</strong> All rights
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
    <!-- CKEditor4 -->
    <script src="../js/ckeditor/ckeditor.js"></script>

    <script>
    CKEDITOR.replace('message');
    </script>
    <!-- resubmit form -->
    <script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    </script>
</body>

</html>
<?php
echo '<h1>' . (microtime(true) - $start) . '</h1>';
?>