<?php
use ZeroCz\Admin\Session;
use ZeroCz\Admin\System;

require_once __DIR__ . '/private/php/init.inc.php';

if ($auth->Logged()) {
    System::redirect('./index.php');
}

if (isset($_POST["submit"])) {
    if ($auth->login($_POST["username"], $_POST["password"], $_POST['g-recaptcha-response'])) {
        System::redirect("./index.php");
    }
}

?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>AdminLTE 3 | Log in</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="../plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

    <style>
    canvas {
        display: block;
        vertical-align: bottom;
    }

    #particles-js {
        position: absolute;
        width: 100%;
        height: 100%;
    }

    .login-box {
        position: absolute;
        top: 25%;
        left: 50%;
        transform: translate(-50%);
    }

    .admin-desc {
        font-size: 1.2rem;
    }
    </style>
</head>

<body class="hold-transition login-page">
    <div id="particles-js"></div>
    <div class="login-box">
        <div class="login-logo">
            <a href="#"><b>Admin</b>LTE</a>
            <p class="admin-desc">Administration login</p>
        </div>
        <!-- /.login-logo -->
        <div class="card">
            <div class="card-body login-card-body">

                <?php if ($auth->isError()): ?>
                <div class="alert alert-dismissible alert-danger">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php
                       foreach ($auth->getErrors() as $error) {
                           echo $error;
                       }
                    ?>
                </div>
                <?php endif; ?>

                <p class="login-box-msg">Přihlásit se</p>
                <form action="" method="POST">
                    <div class="input-group mb-3">
                        <input type="text" name="username" class="form-control" placeholder="Uživatelské jméno"
                            required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Heslo" required>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <div class="g-recaptcha" data-sitekey="<?= ZeroCz\Admin\Config::get('captcha_site'); ?>"></div>
                    </div>
                    <div class="row">
                        <div class="col-7">
                            <div class="icheck-primary">
                                <input type="checkbox" id="remember">
                                <label for="remember">
                                    Zůstat přihlášen
                                </label>
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-5">
                            <button type="submit" name="submit" class="btn btn-primary btn-block btn-flat">Přihlásit se</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>
            </div>
            <!-- /.login-card-body -->
        </div>
    </div>
    <!-- /.login-box -->

    <!-- jQuery -->
    <script src="../plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- reCAPTCHA-->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <!-- particles.js -->
    <script src="../js/particles.js"></script>

    <script>
    // ParticlesJS Config.
    particlesJS.load('particles-js', '../js/particles.json', function() {
        console.log('callback - particles.js config loaded');
    });
    </script>

    <script>
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    </script>
</body>

</html>
