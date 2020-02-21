<?php
use ZeroCz\Admin\System;
use ZeroCz\Admin\Session;
use ZeroCz\Admin\Config;
use ZeroCz\Admin\Minecraft;

require_once __DIR__ . '/private/php/init.inc.php';

$page = 1;

if (!$auth->Logged()) {
    System::redirect('./login.php');
}

if (!isset($_GET['group']) || empty($_GET['group'])) {
    System::redirect('./groups.php?group=' . Session::get('group'));
}

$group = $_GET['group'];

if (!isset(Config::get('groups_perms')[$group][Session::get('group')])) {
    System::redirect('./index.php');
}

$minecraft = new Minecraft();

if ($group === 'helper') {
    $command = $minecraft->getHelpers();
} elseif ($group === 'builder') {
    $command = $minecraft->getBuilders();
} elseif ($group === 'youtube') {
    $command = $minecraft->getYoutubers();
} elseif ($group === 'ateam') {
    $command = $minecraft->getCustom(['majitel', 'vedeni', 'leader', 'technik']);
} else {
    System::redirect('./index.php');
}

if (isset($_POST['zmenit'])) {
    if (isset(Config::get('groups_perms')[$group][Session::get('group')][$_POST['group']])) {
        foreach ($command as $key) {
            if ($key['uuid'] === $_POST['uuid'] && $key['username'] === $_POST['username']) {
                $minecraft->changeGroup($_POST['username'], $_POST['uuid'], Config::get('groups_perms')[$group][Session::get('group')][$_POST['group']]);
                System::redirect('./groups.php?group=' . $_GET['group']);
            }
        }
    }
}

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
                            <h1>Skupiny na serveru</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Skupiny na serveru</li>
                            </ol>
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="card">
                    <div class="card-body">

                    <form action="" method="post" class="form-inline">

                    </form>

                    <?php if ($command !== false): ?>
                        <div class="table-responsive">
                            <table class="table table-striped" style="text-align: center;">
                                <thead>
                                    <tr>
                                        <th>Jméno</th>
                                        <th>Skupiny</th>
                                        <th style="width:30%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($command as $data): ?>
                                <tr>
                                    <td><?= $data['username']; ?></td>
                                    <td><code><?= $data['primary_group']; ?></code></td>
                                    <td>
                                        <form action="" method="post" class="form-inline">
                                            <select name="group" id="" class="custom-select my-1 mr-sm-2" required>
                                                <option value=""></option>
                                                <?php foreach (Config::get('groups_perms')[$group][Session::get('group')] as $key => $value): ?>
                                                    <option value="<?= $key ?>"><?= $value; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="uuid" value="<?= $data['uuid']; ?>">
                                            <input type="hidden" name="username" value="<?= $data['username']; ?>">
                                            <button type="submit" name="zmenit" class="btn btn-primary">Změnit</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-dismissible alert-warning">
                            <h4 class="alert-heading">Upozornění!</h4>
                            <p class="mb-0">Seznam je prázdný!</p>
                        </div>
                        <?php endif; ?>
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
</body>

</html>
<?php
echo '<h1>' . (microtime(true) - $start) . '</h1>';
?>