<?php
use ZeroCz\Admin\Tickets\Ticket;
use ZeroCz\Admin\System;
use ZeroCz\Admin\Session;
use ZeroCz\Admin\Config;

require_once __DIR__ . '/private/php/init.inc.php';

$page = 2;

if (!$auth->Logged()) {
    System::redirect('./login.php');
}

if (!isset($_GET['group'])) {
    System::redirect('./tickets.php?group=' . Session::get('group'));
}

if (!in_array(Session::get('group'), Config::get('ticket_perms')[$_GET['group']])) {
    System::redirect('./index.php');
}

$group = $_GET['group'];


/*
$controller = new \ZeroCz\Admin\Tickets\TicketController();

try {
    $controller->setType('vpn')
        ->setOwner('ZeroCz_')
        ->setOwnerId(1456)
        ->setSubject('')
        ->setMessage('akosdaksodkosAJKDOJAOJ');
    $controller->create();
} catch (\ZeroCz\Admin\Tickets\TicketException $e) {
    $error = $e->getMessage();
}
if (isset($error)) exit($error);
*/
$ticket = new \ZeroCz\Admin\Tickets\TicketView();

$ticketlist = $ticket->getTicketList($group);
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
        font-size: 1.3rem;
    }

    .noresize {
        resize: none;
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
                    <a class="nav-link" href="./logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <?php include MAIN_DIR . '/../pages/sidebar.php'; ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>Seznam ticketů</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Seznam ticketů</li>
                            </ol>
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="card">
                    <div class="card-body">
                    <?php if (empty($ticketlist)): ?>
                        <div class="alert alert-dismissible alert-warning">
                            <h4 class="alert-heading">Upozornění!</h4>
                            <p class="mb-0">Seznam ticketů je prázdný!</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped" id="sortme">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Datum založení</th>
                                        <th>Založil</th>
                                        <th>Předmět</th>
                                        <th>Typ</th>
                                        <th>Stav</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ticketlist as $data): ?>
                                    <tr>
                                        <td><?= $data['id']; ?></td>
                                        <td><?= $data['created']; ?></td>
                                        <td><?= $data['owner']; ?></td>
                                        <td><?= $data['subject']; ?></td>
                                        <td><?= $data['type']; ?></td>
                                        <td><?= $data['status']; ?></td>

                                        <td><a href="viewer.php?group=<?= $_GET['group'] ?>&id=<?= $data['id'] ?>"><button
                                                    type="button" class="btn btn-primary"><i
                                                        class="fas fa-eye fa-sm"></i> Zobrazit</button></a>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                        <!-- /.getTicketList() -->
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

    <!-- resubmit form -->
    <script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    </script>
    <!-- jQuery -->
    <script src="../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../dist/js/adminlte.min.js"></script>
    <!-- DataTables -->
    <script src="../plugins/datatables/jquery.dataTables.js"></script>
    <script src="../plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>

    <script type="text/javascript">
    $(document).ready(function() {
        $('#sortme').DataTable({
            "lengthChange": false,
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Czech.json"
            },
            "columnDefs": [{
                "orderable": false,
                "targets": 6
            }],
            "order": [
                [0, "desc"]
            ],
            "pageLength": 25
        });
    });
    </script>
</body>

</html>
<?php
echo '<h1>' . (microtime(true) - $start) . '</h1>';
?>