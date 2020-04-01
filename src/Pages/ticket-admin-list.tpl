%%ticket_callback_check_if_perms%%
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
                    <a class="nav-link" href="?logout"><i class="fas fa-sign-in-alt"></i></a>
                </li>
            </ul>
        </div>
    </nav>

    <div id="container" class="container-fluid">
        %%ERRORS%%
        %%messages%%
        <div class="card">
            <div class="card-body">
                <p class="title">Tikety</p>
                <div class="table-responsive">
                    %%ticket_callback_get_admin_list%%
                    <!--<table data-request="get-todo" id="todo-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Název</th>
                                <th>Hráč</th>
                                <th>Typ</th>
                                <th>Stav</th>
                                <th>Datum založení</th>
                                <th>Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                              <td>1</td>
                              <td>Nahlášení bugu</td>
                              <td>Skypad6000</td>
                              <td>Nahlášení chyby</td>
                              <td><span class="badge badge-yellow">Čeká na odpověď (Hráče/Podpory)</span></td>
                              <td>3:33 25.03.2020</td>
                              <td><button type="button" class="btn btn-small">Otevřít</button> <button type="button" class="btn btn-small red">Uzavřít</button></td>
                            </tr>
                            <tr>
                              <td>2</td>
                              <td>Nahlášení hráče</td>
                              <td>Skypad6000</td>
                              <td>Nahlášení hráče</td>
                              <td><span class="badge badge-danger">Uzavřen</span></td>
                              <td>3:33 25.03.2020</td>
                              <td><button type="button" class="btn btn-small">Otevřít</button> <button type="button" class="btn btn-small red">Uzavřít</button></td>
                            </tr>
                        </tbody>
                    </table>!-->
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$( function () {
    $('#ticket-list').DataTable({
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Czech.json"
        },
        "searching": false,
        "info": false,
        "lengthChange":false,
        "columnDefs": [
        { "orderable": false, "targets": 5 },
        { "type": "num", "tagets": 0}
        ]

    });
} );
</script>