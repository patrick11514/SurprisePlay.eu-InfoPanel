<?php

namespace patrick115\Adminka;

use patrick115\Main\Database;
use patrick115\Main\Error;
use patrick115\Main\Config;
use patrick115\Main\Tools\Utils;

class Todo  
{
    private $database;
    private $config;

    private $todo_data = [
        "for", "tags", "message"
    ];

    private $data;

    public function __construct($data)
    {
        if ($data["method"] != "remove-todo") {
            foreach ($this->todo_data as $datas) {
                if (empty($data[$datas])) {
                    define("MESSAGE", ["<span style=\"color:red\">Can't find $datas in got data.</span>"]);
                    return true;
                }
            }
        }
        foreach ($data as $name => $dat) {
            $this->data[Utils::chars($name)] = Utils::chars($dat);
        }
        $this->database = Database::init();
        $this->config = Config::init();
    }

    public function addTodo()
    {
        $session = \patrick115\Main\Session::init();
        $tags = $this->config->getConfig("Main/todo-tags");
        $tagy = [];

        foreach ($this->data["tags"] as $tag) {
            if (empty($tags[$tag])) {
                define("MESSAGE", ["<span style=\"color:red\">Undefined tag {$tag}!</span>"]);
                return true;
            } else {
                $tagy[] = $tag;
            }
        }

        $_tags = json_encode(["tags" => $tagy], JSON_UNESCAPED_UNICODE);
        $_creator = $session->getData("Account/User/Username");
        $_creator_id = Utils::getClientID($_creator);

        if ($_creator_id === null) {
            define("MESSAGE", ["<span style=\"color:red\">User {$_creator} has been never in infopanel!</span>"]);
            return true;
        }

        $_for = $this->data["for"];
        $_for_id = Utils::getClientID($_for);

        if ($_for_id === null) {
            define("MESSAGE", ["<span style=\"color:red\">User {$_for} has been never in infopanel!</span>"]);
            return true;
        }

        $_message = strip_tags($this->data["message"]);
        
        $this->database->insert("todo-list", [
            "id",
            "creator_id",
            "creator",
            "for_id",
            "for",
            "message",
            "tags",
            "date",
            "timestamp"
        ],
        [
            "",
            $_creator_id,
            $_creator,
            $_for_id,
            $_for,
            $_message,
            $_tags,
            date("H:i:s d.m.Y"),
            time()
        ]);

        define("MESSAGE", ["Úloha přidána!"]);
        return true;
    }

    public function removeTodo()
    {
        $id = rtrim(
            ltrim(
                Utils::getPackage(
                    [
                        "1" => $this->data["id"]
                    ]
                )
                ,
                "%%ID;"
            )
        ,
        ";ID%%");
        $rv = $this->database->select(["id"], "todo-list", "LIMIT 1", "id" , $id);
        if (!$rv || $this->database->num_rows($rv) == 0) {
            define("MESSAGE", ["<span style=\"color:red\">Id nenalezeno</span>"]);
            return true;
        }
        $this->database->delete("todo-list", ["id"], [$id]);
        
        define("MESSAGE", ["Úkol úspěšně smazán!"]);
        return true;
    }
}
