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
        "form"
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
            "settings"
        ];

        if (!in_array($form_name, $stored_forms)) {
            $this->error->catchError("Stored form $form_name not found!", debug_backtrace());
            return;
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
                </secti>
                ';
            break;
        }

        return Main::Create("\patrick115\Adminka\Generator", ["form"]);
    }
}