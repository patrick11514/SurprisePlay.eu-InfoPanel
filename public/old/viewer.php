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


$ticket = new Ticket(true);

if (isset($_POST['submit'])) {
    if ($ticket->validate(Ticket::TICKET_REPLY, $_POST['message'])) {
        $ticket->getSelected()->replyTicket();
    }
}

if (isset($_POST['lock'])) {
    $ticket->getSelected()->lock();
}

if (isset($_POST['unlock'])) {
    $ticket->getSelected()->unlock();
}

if (isset($_POST['reassign'])) {
    $ticket->getSelected()->reassign($_POST['group']);
}

if (in_array(Session::get('group'), Config::get('ticket_archive_button_perms'))) {
    if (isset($_POST['archiv'])) {
        $ticket->getSelected()->archive();
    }
}

$ticket->select($_GET['id'], $_GET['group']);


if (empty($ticket->viewTicket()) || empty($ticket->viewTicketPost()))
    System::redirect('./tickets.php');
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
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card">

                                <div class="card-header border-0">
                                    <div class="d-flex justify-content-between">
                                        <h3 class="card-title">Zprávy</h3>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <?php if (System::isError()): ?>
                                    <div class="alert alert-dismissible alert-danger">
                                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                                        <?= System::getError(); ?>
                                    </div>
                                    <?php endif; ?>

                                    <?php foreach ($ticket->viewTicketPost() as $data): ?>
                                    <div class="form-group">
                                        <?php if ($data['admin'] == Ticket::ADMIN): ?>
                                        <!-- Message to the right -->
                                        <div class="direct-chat-msg right">
                                            <div class="direct-chat-infos clearfix">
                                                <span style="color:red;" class="direct-chat-name float-right"><?= $data['username'] ?></span>
                                                <span class="direct-chat-timestamp float-left"><?= System::format($data['post_date'], 'date') ?></span>
                                            </div>
                                            <!-- /.direct-chat-infos -->
                                            <img class="direct-chat-img"
                                                src="<?= $auth->getAvatar($data['username']); ?>"
                                                alt="message user image">
                                            <!-- /.direct-chat-img -->
                                            <div class="direct-chat-text">
                                                <?= $data['message'] ?>
                                            </div>
                                            <!-- /.direct-chat-text -->
                                        </div>
                                        <!-- /.direct-chat-msg -->
                                        <?php else: ?>
                                        <!-- Message. Default to the left -->
                                        <div class="direct-chat-msg">
                                            <div class="direct-chat-infos clearfix">
                                                <span class="direct-chat-name float-left"><?= $data['username'] ?></span>
                                                <span class="direct-chat-timestamp float-right"><?= System::format($data['post_date'], 'date') ?></span>
                                            </div>
                                            <!-- /.direct-chat-infos -->
                                            <img class="direct-chat-img"
                                                src="<?= $auth->getAvatar($data['username']); ?>"
                                                alt="message user image">
                                            <!-- /.direct-chat-img -->
                                            <div class="direct-chat-text">
                                                <?= $data['message'] ?>
                                            </div>
                                            <!-- /.direct-chat-text -->
                                        </div>
                                        <!-- /.direct-chat-msg -->
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card">

                                <div class="card-header border-0">
                                    <div class="d-flex justify-content-between">
                                        <h3 class="card-title">Informace</h3>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <?php foreach ($ticket->viewTicket() as $data): ?>
                                    <?php $status = $data['status']; //Pro schování textarey, když je ticket zamčen ?>
                                    <p><strong>Status: </strong><?= System::format($data['status'], 'status'); ?></p>
                                    <p><strong>Předmět: </strong><?= $data['subject']; ?></p>
                                    <p><strong>Typ: </strong><?= System::format($data['type'], 'type'); ?></p>
                                    <p><strong>Založil: </strong><?= $data['owner']; ?></p>
                                    <p><strong>Vytvořen: </strong><?= System::format($data['created'], 'date'); ?></p>

                                    <?php if ($data['status'] !== Ticket::TICKET_ARCHIV): ?>
                                    <form class="form-inline" action="" method="post">
                                        <div class="form-group">
                                            <select name="group" class="form-control" required>
                                                <option value=""></option>
                                                <option value="vedeni">Vedení</option>
                                                <option value="technik">Technik</option>
                                                <option value="hl-builder">Hlavní Builder</option>
                                                <option value="hl-helper">Hlavní Helper</option>
                                                <option value="helper">Helper</option>
                                                <option value="dsasdsa">debug</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="reassign" class="btn btn-success"
                                            style="margin-left: 10px;">Přeřadit</button>
                                    </form>
                                    <br>
                                        <?php if ($data['status'] !== Ticket::TICKET_UZAVREN): ?>
                                        <form action="" method="post">
                                            <div class="form-group">
                                                <button type="submit" name="lock" class="btn btn-danger"><i class="fas fa-lock fa-sm"></i>&nbsp;Uzavřít</button>
                                            </div>
                                        </form>
                                        <?php else: ?>
                                        <form action="" method="post">
                                            <div class="form-group">
                                                <button type="submit" name="unlock" class="btn btn-success"><i class="fas fa-lock-open fa-sm"></i>&nbsp;Otevřít</button>
                                            </div>
                                        </form>
                                        <?php endif; ?>

                                        <?php if (in_array(Session::get('group'), Config::get('ticket_archive_button_perms'))): ?>
                                            <?php if ($data['status'] !== Ticket::TICKET_ARCHIV): ?>
                                            <form action="" method="post">
                                                <div class="form-group">
                                                    <button type="submit" name="archiv" class="btn btn-secondary" onclick="return confirm('Opravdu archivovat? Akce je nevratná!');">Archivovat</button>
                                                </div>
                                            </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (isset($status) && $status !== Ticket::TICKET_ARCHIV): ?>
                    <div class="card">
                    
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Odpověď</h3>
                            </div>
                        </div>

                        <div class="card-body">
                            <?php if (isset($status) && $status !== Ticket::TICKET_UZAVREN): ?>
                            <form action="" method="post">
                                <div class="form-group">
                                    <textarea name="message" rows="5" class="form-control" required></textarea>
                                </div>
                                <button type="submit" name="submit" class="btn btn-primary">Odeslat</button>
                            </form>
                            <?php else: ?>
                            <div class="alert alert-dismissible alert-danger">
                                Ticket je uzavřen! Čeká se na hráče zda jej znovu otevře.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

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
    <!-- CKEditor4 -->
    <script src="../js/ckeditor/ckeditor.js"></script>

    <script>
    CKEDITOR.replace('message');
    </script>
</body>

</html>
<?php
echo '<h1>' . (microtime(true) - $start) . '</h1>';
?>