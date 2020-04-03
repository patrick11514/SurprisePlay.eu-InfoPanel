<?php

namespace patrick115\Adminka;

use patrick115\Main\Error;
use patrick115\Adminka\Main;
use patrick115\Main\Session;
use patrick115\Main\Tools\Utils;

use patrick115\Adminka\Tickets;

class Generator
{

    private $error;

    private $method;
    private $genData;

    private $aviliable_methods = [
        "form",
        "data",
        "table"
    ];

    public function __construct($method)
    {
        $this->error = Error::init();
        if (in_array($method, $this->aviliable_methods)) {
            $this->method = $method;
        } else {
            $this->error->catchError("Undefined method $method avliliable methods: " . implode(", ", $this->aviliable_methods) . ".", debug_backtrace());
            return;
        }
    }

    public function generate()
    {
        return $this->genData;
    }


    public function getData($data_name)
    {
        if ($this->method != "data") {
            $this->error->catchError("Can't use getData, when method is not data.", debug_backtrace());
            return;
        }
        $stored_data = [
            "tickets_reasons"
        ];

        if (!in_array($data_name, $stored_data)) {
            $this->error->catchError("Stored data $data_name not found!", debug_backtrace());
        }

        switch ($data_name) {
            case "tickets_reasons":
                $username = Session::init()->getData("Account/User/Username");

                $arr = [
                    "method" => "getData",
                    "username" => $username
                ];
                $tickets = new Tickets($arr);

                $reasons = $tickets->getReasons();
                $groups = $tickets->getGroups();

                $ret = "";
                $ret .= "<option></option>";
                foreach ($groups as $group_id => $group_name) {
                    $ret .= "<optgroup label=\"{$group_name}\">";

                    foreach ($reasons[$group_id] as $reason) {
                        $ret .= "<option value=\"" . Utils::createPackage("%%TICKET_ID;" . $reason . ";TICKET_ID%%")[1] . "\">{$reason}</option>";
                    }

                    $ret .= "</optgroup>";
                }

                $this->genData = $ret;
            break; 

        }
        return Main::Create("\patrick115\Adminka\Generator", ["data"]);
    }

    public function getForm($form_name)
    {
        if ($this->method != "form") {
            $this->error->catchError("Can't use getForm, when method is not form.", debug_backtrace());
            return;
        }
        $stored_forms = [
            "settings",
            "VPNAllow",
            "Unregister",
            "Gems",
            "Todo",
            "Change-User-Data",
            "Unban"
        ];

        if (!in_array($form_name, $stored_forms)) {
            $this->error->catchError("Stored form $form_name not found!", debug_backtrace());
        }

        switch ($form_name) {
            case "settings":
                $this->genData = '
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
                        <div class="card">
                            <div class="card-body">
                                <p>Natavení tvého účtu</p>
                                <hr>
                                <form method="post" action="./requests.php" role="form">
                                    <input type="hidden" name="method" value="settings" required>
                                    <input type="hidden" name="source_page" value="?settings" required>
                                    <input type="hidden" name="CSRF_token" value="%%CSRF_Token%%" required>
                                    <div class="form-group">
                                        <label for="autologin">Autologin</label>
                                        <select name="autologin" id="autologin" class="form-control">
                                            <option value="%%autologin_st%%">%%autologin_st_name%%</option>
                                            <option value="%%autologin_nd%%">%%autologin_nd_name%%</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">E-mail</label>
                                        <input type="text" class="form-control" id="email" value="%%user-email%%" name="e-mail" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="password">Heslo</label>
                                        <input type="text" class="form-control" id="password" value="%%password%%" name="password" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="skin">Reset Skinu <span style="color:red;font-size:small;">(Pouze, pokud nemáš na webu svůj nový skin)</label>
                                        <select name="skin" id="skin" class="form-control">
                                            <option value="none"></option>
                                            <option value="reset">Resetovat</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-light">Uložit</button>
                                </form>
                            </div>
                        </div>
                        %%settings_allow_vpn_form%%
                    </div>
                </div>
                ';
            break;
            case "VPNAllow":
                $this->genData = '
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
                        <div class="card">
                            <div class="card-body">
                                <p>Povolení VPN</p>
                                <hr>
                                <form method="post" action="./requests.php" role="form">
                                    <input type="hidden" name="method"  value="vpn-allow" required>
                                    <input type="hidden" name="source_page" value="?vpn-allow" required>
                                    <input type="hidden" name="CSRF_token" id="CSRF_TOKEN" value="%%CSRF_Token%%" required>
                                    <div class="form-group">
                                        <label for="allow-nick">Zadej nick, pro který chceš povolot připojení s VPN</label>
                                        <input type="text" name="allow-nick" id="allow-nick" class="form-control" placeholder="Zadej nick" required>
                                        <div class="list-group" id="nicks">
                                        </div>
                                    </div>
                                    <button type="submit" id="vpn-button" class="btn btn-light">Povolit přístup</button>
                                </form>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <p class="title">Povolené přístupy s VPN</p>
                                <div id="loader" class="d-flex justify-content-center" style="padding-top:5%;">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="allow-vpn-table" style="visibility:hidden;" class="loading table table-striped">
                                        <thead>                  
                                            <tr>
                                                <th>#</th>
                                                <th>Jméno</th>
                                                <th>Rank</th>
                                                <th>#</th>
                                            </tr>
                                        </thead>
                                        <tbody id="vpn-allow-user-list">
                                        </tbody>
                                    </table>
                                </div>
                                <nav id="vpn-page-buttons" class="loading" style="visibility:hidden;">
                                  <ul class="pagination justify-content-center mb-0">
                                    <li id="li-vpn-prev-page" class="page-item">
                                        <a id="vpn-prev-page" class="page-link" href="#">Přechozí</a>
                                    </li>
                                    <li class="page-item active">
                                        <span id="vpn-page-id" data-page="" class="page-link">
                                        </span>
                                    </li>
                                    <li id="li-vpn-next-page" class="page-item">
                                        <a id="vpn-next-page" class="page-link" href="#">Další</a>
                                    </li>
                                  </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            ';
            break;
            case "Unregister":
                $this->genData = '
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
                        <div class="card">
                            <div class="card-body">
                                <p>Odregistrování hráče</p>
                                <hr>
                                <form method="post" action="./requests.php" role="form">
                                    <input type="hidden" name="method" value="unregister" required>
                                    <input type="hidden" name="source_page" value="?unregister" required>
                                    <input type="hidden" name="CSRF_token" id="CSRF_TOKEN" value="%%CSRF_Token%%" required>
                                    <div class="form-group">
                                        <label for="unregister-nick">Zadej nick, který chceš odregistrovat</label>
                                        <input type="text" name="unregister-nick" id="unregister-nick" class="form-control"
                                            placeholder="Zadej nick" required>
                                        <div class="list-group" id="nicks">
                                        </div>
                                    </div>
                                    <button type="submit" id="unregister-button"
                                        class="btn btn-light">Odregistrovat</button>
                                </form>
                                
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <p class="title">Záznam odregistrovaných uživatelů</p>
                                <div id="loader" class="d-flex justify-content-center" style="padding-top:5%;">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="allow-unregister-table" style="visibility:hidden;"
                                        class="loading table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Jméno</th>
                                                <th>Rank</th>
                                                <th>Odregistroval</th>
                                                <th>Kdy</th>
                                            </tr>
                                        </thead>
                                        <tbody id="unregister-allow-user-list">
                                        </tbody>
                                    </table>
                                </div>
                                <nav id="unregister-page-buttons" class="loading" style="visibility:hidden;">
                                    <ul class="pagination justify-content-center mb-0">
                                        <li id="li-unregister-prev-page" class="page-item">
                                            <a id="unregister-prev-page" class="page-link" href="#">Přechozí</a>
                                        </li>
                                        <li class="page-item active">
                                            <span id="unregister-page-id" data-page="" class="page-link">
                                            </span>
                                        </li>
                                        <li id="li-unregister-next-page" class="page-item">
                                            <a id="unregister-next-page" class="page-link" href="#">Další</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            ';
            break;
            case "Gems":
                $this->genData = '
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
                                <p>Správa gemů</p>
                                <hr>
                                <form method="post" action="./requests.php" role="form">
                                    <input type="hidden" name="method" value="gems" required>
                                    <input type="hidden" name="source_page" value="?gems" required>
                                    <input type="hidden" name="CSRF_token" id="CSRF_TOKEN" value="%%CSRF_Token%%" required>
                                    <div class="form-group">
                                        <label for="gems-nick">Zadej nick, který chceš spravovat</label>
                                        <input type="text" name="gems-nick" id="gems-nick" class="form-control"
                                            placeholder="Zadej nick" required>
                                        <div class="list-group" id="nicks">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="gem-count">Zadej částku</label>
                                        <input type="number" name="gem-count" id="gem-count" class="form-control"
                                            placeholder="Počet gemů" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="gem-action">Co chceš udělat?</label>
                                        <select class="form-control" id="gem-action" name="gem-action" required>
                                            <option value="add">Přidat</option>
                                            <option value="remove">Odebrat</option>
                                        </select>
                                    </div>
                                    <button type="submit" id="gems-button"
                                        class="btn btn-light disabled">Loading...</button>
                                </form>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <p class="title">Seznam transakcí gemů</p>
                                <div id="loader" class="d-flex justify-content-center" style="padding-top:5%;">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="allow-gems-table" style="visibility:hidden;"
                                        class="loading table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Jméno</th>
                                                <th>Rank</th>
                                                <th>Admin</th>
                                                <th>Částka</th>
                                                <th>Metoda</th>
                                                <th>Kdy</th>
                                            </tr>
                                        </thead>
                                        <tbody id="gems-allow-user-list">
                                        </tbody>
                                    </table>
                                </div>
                                <nav class="loading" id="gems-page-buttons" style="visibility:hidden;">
                                    <ul class="pagination justify-content-center mb-0">
                                        <li id="li-gems-prev-page" class="page-item">
                                            <a id="gems-prev-page" class="page-link" href="#">Přechozí</a>
                                        </li>
                                        <li class="page-item active">
                                            <span id="gems-page-id" data-page="" class="page-link">
                                            </span>
                                        </li>
                                        <li id="li-gems-next-page" class="page-item">
                                            <a id="gems-next-page" class="page-link" href="#">Další</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            ';
            break;
            case "Todo":
                $this->genData = '
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
                                <p class="title">Úkoly:</p>
                                <div id="loader" class="d-flex justify-content-center" style="padding-top:5%;">
                                    <div class="spinner-border" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table data-request="get-todo" id="todo-table" style="visibility:hidden;"
                                        class="table table-striped loading">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Jméno</th>
                                                <th>Úkol</th>
                                                <th>Tagy</th>
                                                <th>Zadal</th>
                                                <th>Datum zadání</th>
                                                <th>#</th>
                                            </tr>
                                        </thead>
                                        <tbody id="todo-items">
                                        </tbody>
                                    </table>
                                </div>
                                <nav data-ajax-var="todo" class="loading" id="todo-paginator" style="visibility:hidden;">
                                    <ul class="pagination justify-content-center mb-0">
                                        <li id="li-todo-prev-page" class="page-item">
                                            <a id="todo-prev-page" class="page-link" href="#">Přechozí</a>
                                        </li>
                                        <li class="page-item active">
                                            <span id="todo-page-id" data-page="" class="page-link">
                                            </span>
                                        </li>
                                        <li id="li-todo-next-page" class="page-item">
                                            <a id="todo-next-page" class="page-link" href="#">Další</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <p>Vytvořit úkol</p>
                                <hr>
                                <form action="./requests.php" method="post" role="form">
                                    <input type="hidden" name="method" value="todo" required>
                                    <input type="hidden" name="source_page" value="?todo" required>
                                    <input type="hidden" name="CSRF_token" id="CSRF_TOKEN" value="%%CSRF_Token%%" required>
                                    <div class="form-group">
                                        <label for="todo-nicks">Pro koho</label>
                                        <div id="loader-nicks" class="d-flex justify-content-center" style="padding-top:5%;">
                                            <div class="spinner-border" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                        </div>
                                        <select id="todo-nicks" style="visibility:hidden;" name="for"
                                            class="loading-nicks form-control" required>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="tags">Tagy (CTRL + CLICK)</label>

                                        <select name="tags[]" id="tags" class="form-control" multiple="multiple" required>
                                            %%TODO_TAGS%%
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="message">Zpráva</label>
                                        <textarea id="message" name="message" class="form-control" required></textarea>
                                    </div>
                                    <button type="submit" id="todo-button" class="btn btn-light">Přidat</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            ';
            break;
            case "Change-User-Data":
                $this->genData = '
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
                                <p>Přesun dat (VIP, tagy)</p>
                                <hr>
                                <form action="./requests.php" method="post">
                                    <input type="hidden" name="method" value="changeData" required>
                                    <input type="hidden" name="source_page" value="?change-user-data" required>
                                    <input type="hidden" name="CSRF_token" id="CSRF_TOKEN" value="%%CSRF_Token%%" required>
                                    <div class="form-group">
                                        <label for="from-nick">Z jakého nicku</label>
                                        <input type="text" id="from-nick" name="from-nick" class="form-control" required>
                                        <div class="list-group" id="from-nicks" pos="1">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="to-nick">Na jaký nick</label>
                                        <input type="text" id="to-nick" name="to-nick" class="form-control" required>
                                        <div class="list-group" id="to-nicks" pos="2">
                                        </div>
                                    </div>
                                    <button type="submit" id="confirm-button" class="btn btn-light">Přesunout data</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            ';
            break;
            case "Unban":
                $this->genData = '
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
                                <p>Odbanování uživatele</p>
                            	<hr>
                                <form action="./requests.php" method="post">
                                    <input type="hidden" name="method" value="unban" required>
                                    <input type="hidden" name="source_page" value="?unban" required>
                                    <input type="hidden" name="CSRF_token" id="CSRF_TOKEN" value="%%CSRF_Token%%" required>
                                    <div class="form-group">
                                        <label for="nick">Nick</label>
                                        <input type="text" id="nick" name="nick" class="form-control" required>
                                        <div class="list-group" id="nicks">
                                        </div>
                                    </div>

                                    <button type="submit" id="confirm-button" class="btn btn-light">Odbanovat</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                ';
            break;
            default:
                $this->genData = '
                <section class="content">
                    <h2 style="color:red;text-align:center">Chyba!</h2>
                </section>';
            break;
        }

        return Main::Create("\patrick115\Adminka\Generator", ["form"]);
    }
}
