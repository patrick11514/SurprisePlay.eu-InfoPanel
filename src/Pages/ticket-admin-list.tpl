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
                <p class="title">Tikety - %%ticket_group%%</p>
                <div class="table-responsive">
                    %%ticket_callback_get_admin_list%%
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