<div id="content">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <ul class="nav navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" id="sidebarCollapse" href="#"><i class="fas fa-align-left"></i></a>
                </li>
            </ul>
            <p>%%page_name%%</p>
            <ul class="nav navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-sign-in-alt"></i></a>
                </li>
            </ul>
        </div>
    </nav>

    <div id="container" class="container-fluid">
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            %%ERRORS%%
        </div>
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
            %%messages%%
        </div>
        <div class="card">
            <div class="card-body">
                <p>Založit nový Tiket</p>
                <hr>
                <form method="post" action="./requests.php" role="form">
                    <input type="hidden" name="method" value="settings" required>
                    <input type="hidden" name="source_page" value="?settings" required>
                    <input type="hidden" name="CSRF_token" value="%%CSRF_Token%%" required>
                    <div class="form-group">
                        <label for="name">Název tiketu</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Typ tiketu</label>
                        <select name="type" class="form-control" required>
                            <option value="1">Nahlášení Hráče</option>
                            <option value="2">Nahlášení Helpera</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="admin">Určení</label>
                        <select name="type" class="form-control" disabled>
                            <option value="Helper">Helper</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message">Zpráva</label>
                        <textarea type="text" class="form-control" id="message" name="message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-light">Vytvořit tiket</button>
                </form>
            </div>
        </div>
    </div>
</div>