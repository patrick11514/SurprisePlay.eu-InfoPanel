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
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box blue">
                    <span class="info-box-icon"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="text">Registrováno uživatelů</span>
                        <span class="number">%%registered_users%%</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box red">
                    <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="text">Zabanováno uživatelů</span>
                        <span class="number">%%banned_users%%</span> 
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box green">
                    <span class="info-box-icon"><i class="fas fa-vote-yea"></i></span>
                    <div class="info-box-content">
                        <span class="text">Hlasů pro server</span>
                        <span class="number">%%votes%%</span>  
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box yellow">
                    <span class="info-box-icon"><i class="fas fa-coins"></i></span>  
                    <div class="info-box-content">
                        <span class="text">Oběh peněz na serveru</span>
                        <span class="number">%%currency%%</span> 
                    </div>
                </div>
            </div>
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
