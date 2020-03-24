<section id="content">
        <div class="card">
            <div class="card-body">
                <p class="title"><b>SurprisePlay</b>.eu</p>
                <p class="description">Přihlášení</p>
                %%ERRORS%%
                %%messages%%
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
                    <button type="submit" class="btn btn-light">Přihlásit&nbsp;se</button>
                </form>
            </div>
        </div>
    </section>
    <script src="//%domain%/public/js/bootstrap.bundle.min.js"></script>