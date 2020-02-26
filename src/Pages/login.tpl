<div class="login-box">
    <div class="login-logo">
        <span style="color:#495057"><b>SurprisePlay</b>.eu</span>
    </div>
    <!-- /.login-logo -->
    <div class="card">
        <div class="card-body login-card-body">
            <h2 style="text-align:center;">Přihlášení</h2>
            <h6 style="text-align:center;color:red">%%ERRORS%%</h6>
            <h6 style="text-align:center;color:green">%%messages%%</h6>
            <form action="./requests.php" method="post">
                <input type="hidden" name="method" value="login" required>
                <input type="hidden" name="source_page" value="?login" required>
                <input type="hidden" name="CSRF_token" value="%%CSRF_Token%%" required>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" name="username" placeholder="Nick na serveru" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>
                <div class="input-group mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Heslo" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- /.col -->
                    <div class="col-6">
                        <button type="submit" class="btn btn-primary btn-block">Přihlásit&nbsp;se</button>
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
<script src="//%domain%/public/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="//%domain%/public/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="//%domain%/public/dist/js/adminlte.min.js"></script>
