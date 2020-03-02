<?php

namespace patrick115\Adminka;

use patrick115\Main\Error;
use patrick115\Adminka\Main;

class Generator
{

    private $error;

    private $method;
    private $genData;

    private $aviliable_methods = [
        "form",
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
            "Gems"
        ];

        if (!in_array($form_name, $stored_forms)) {
            $this->error->catchError("Stored form $form_name not found!", debug_backtrace());
        }

        switch ($form_name) {
            case "settings":
                $this->genData = '
                <section class="content">
                    <div class="card" style="max-width:80%;width:80%;left:50%;transform: translate(-50%);">
                        <div class="card-body">
                            <form method="post" action="./requests.php" role="form">
                                <input type="hidden" name="method" value="settings" required>
                                <input type="hidden" name="source_page" value="?settings" required>
                                <input type="hidden" name="CSRF_token" value="%%CSRF_Token%%" required>
                                <div class="form-group">
                                    <label>Autologin</label>
                                    <select name="autologin" class="form-control">
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
                                <button type="submit" class="btn btn-primary btn-block">Uložit</button>
                            </form>
                        </div>
                    </div>
                </section>
                ';
            break;
            case "VPNAllow":
                $this->genData = '
                <section class="content">
                    <div class="card" style="max-width:80%;width:80%;left:50%;transform: translate(-50%);">
                        <div class="card-body">
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
                                <button type="submit" id="vpn-button" class="btn btn-primary btn-block">Povolit přístup</button>
                            </form>
                        </div>
                    </div>
                    <div class="card" style="max-width:80%;width:80%;left:50%;transform: translate(-50%);">
                        <div class="card-body">
                            <h4>Povolené přístupy s VPN</h4>
                            <table id="allow-vpn-table" class="table table-bordered">
                                <thead>                  
                                    <tr>
                                        <th style="width: 10px" style="text-align:center">#</th>
                                        <th style="text-align:center">Jméno</th>
                                        <th style="text-align:center">Rank</th>
                                    </tr>
                                </thead>
                                <tbody id="vpn-allow-user-list">
                                </tbody>
                            </table>
                            <nav id="vpn-page-buttons" style="padding-top:10px;">
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
                </section>


                
                ';
            break;
            case "Unregister":
                $this->genData = '
                <section class="content">
                    <div class="card" style="max-width:80%;width:80%;left:50%;transform: translate(-50%);">
                        <div class="card-body">
                            <form method="post" action="./requests.php" role="form">
                                <input type="hidden" name="method"  value="unregister" required>
                                <input type="hidden" name="source_page" value="?unregister" required>
                                <input type="hidden" name="CSRF_token" id="CSRF_TOKEN" value="%%CSRF_Token%%" required>
                                <div class="form-group">
                                    <label for="unregister-nick">Zadej nick, který chceš odregistrovat</label>
                                    <input type="text" name="unregister-nick" id="unregister-nick" class="form-control" placeholder="Zadej nick" required>
                                    <div class="list-group" id="nicks">
                                    </div>
                                </div>
                                <button type="submit" id="unregister-button" class="btn btn-primary btn-block">Odregistrovat</button>
                            </form>
                        </div>
                    </div>
                    <div class="card" style="max-width:80%;width:80%;left:50%;transform: translate(-50%);">
                        <div class="card-body">
                            <h4>Záznam odregistrovaných uživatelů</h4>
                            <table id="allow-unregister-table" class="table table-bordered">
                                <thead>                  
                                    <tr>
                                        <th style="width: 10px" style="text-align:center">#</th>
                                        <th style="text-align:center">Jméno</th>
                                        <th style="text-align:center">Rank</th>
                                        <th style="text-align:center">Odregistroval</th>
                                        <th style="text-align:center">Kdy</th>
                                    </tr>
                                </thead>
                                <tbody id="unregister-allow-user-list">
                                </tbody>
                            </table>
                            <nav  id="unregister-page-buttons" style="padding-top:10px;">
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
                </section>
                ';
            break;
            case "Gems":
                $this->genData = '
                <section class="content">
                    <div class="card" style="max-width:80%;width:80%;left:50%;transform: translate(-50%);">
                        <div class="card-body">
                            <form method="post" action="./requests.php" role="form">
                                <input type="hidden" name="method" value="gems" required>
                                <input type="hidden" name="source_page" value="?gems" required>
                                <input type="hidden" name="CSRF_token" id="CSRF_TOKEN" value="%%CSRF_Token%%" required>
                                <div class="form-group">
                                    <label for="gems-nick">Zadej nick, který chceš spravovat</label>
                                    <input type="text" name="gems-nick" id="gems-nick" class="form-control" placeholder="Zadej nick" required>
                                    <div class="list-group" id="nicks">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="gem-count">Zadej částku</label>
                                    <input type="number" name="gem-count" id="gem-count" class="form-control" placeholder="Počet gemů" required>
                                </div>
                                <div class="form-group">
                                    <label for="gem-action">Co chceš udělat?</label>
                                    <select class="form-control" id="gem-action" name="gem-action" required>
                                        <option value="add">Přidat</option>
                                        <option value="remove">Odebrat</option>
                                    </select>
                                </div>
                                <button type="submit" id="gems-button" class="btn btn-primary btn-block disabled">Loading...</button>
                            </form>
                        </div>
                    </div>
                    <div class="card" style="max-width:80%;width:80%;left:50%;transform: translate(-50%);">
                        <div class="card-body">
                            <h4>Seznam transakcí gemů</h4>
                            <table id="allow-gems-table" class="table table-bordered">
                                <thead>                  
                                    <tr>
                                        <th style="width: 10px" style="text-align:center">#</th>
                                        <th style="text-align:center">Jméno</th>
                                        <th style="text-align:center">Rank</th>
                                        <th style="text-align:center">Admin</th>
                                        <th style="text-align:center">Částka</th>
                                        <th style="text-align:center">Metoda</th>
                                        <th style="text-align:center">Kdy</th>
                                    </tr>
                                </thead>
                                <tbody id="gems-allow-user-list">
                                </tbody>
                            </table>
                            <nav  id="gems-page-buttons" style="padding-top:10px;">
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
                </section>
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