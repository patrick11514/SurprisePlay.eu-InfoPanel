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
                    <a class="nav-link" href="./?logout"><i class="fas fa-sign-in-alt"></i></a>
                </li>
            </ul>
        </div>
    </nav>

    <div id="container" class="container-fluid">
        %%ERRORS%%
        %%messages%%
        
        <div class="row">
            %%server_info%%
        </div>
        <div class="card">
            <div class="card-body">
                <p class="title">Základní informace o Vás ze serveru</p>
                <table class="table table-condensed" style="background-color:white;">
                    <tbody>
                        %%player_info%%
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
