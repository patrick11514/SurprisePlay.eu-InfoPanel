<?php

/**
 * Main class for Todo list
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Adminka;

use patrick115\Main\Database;
use patrick115\Main\Error;
use patrick115\Main\Config;
use patrick115\Main\Tools\Utils;

class Todo  
{
    /**
     * Database class
     * @var object
     */
    private $database;
    /**
     * Config class
     * @var object
     */
    private $config;

    /**
     * Methods for POST
     * @var array
     */
    private $todo_data = [
        "todo" => [
            "for", "tags", "message"
        ],
        "remove-todo" => [
            "id"
        ]
    ];
    /**
     * Data from POST
     * @var array
     */
    private $data;

    /**
     * Construct class
     * @param array $data - data from POST
     */
    public function __construct(array $data)
    {
        foreach ($this->todo_data[$data["method"]] as $datas) {
            if (empty($data[$datas])) {
                define("ERROR", ["Can't find $datas in got data."]);
                return false;
            }
        }
        foreach ($data as $name => $dat) {
            $this->data[Utils::chars($name)] = Utils::chars($dat);
        }
        $this->database = Database::init();
        $this->config = Config::init();
    }

    /**
     * Add new element to ticket
     * @return bool
     */
    public function addTodo()
    {
        $session = \patrick115\Main\Session::init();
        $tags = $this->config->getConfig("Main/todo-tags");
        $tagy = [];

        foreach ($this->data["tags"] as $tag) {
            if (empty($tags[$tag])) {
                define("ERROR", ["Neznámý tag {$tag}!"]);
                return false;
            } else {
                $tagy[] = $tag;
            }
        }

        $_tags = json_encode(["tags" => $tagy], JSON_UNESCAPED_UNICODE);
        $_creator = $session->getData("Account/User/Username");
        $_creator_id = Utils::getClientID($_creator);

        if ($_creator_id === null) {
            define("ERROR", ["Hráč {$_creator} nebyl nikdy v infopanelu!"]);
            return false;
        }

        $_for = $this->data["for"];
        $_for_id = Utils::getClientID($_for);

        if ($_for_id === null) {
            define("ERROR", ["Hráč {$_for} nebyl nikdy v infopanelu!"]);
            return false;
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

    /**
     * Remove element from todo
     * @return bool
     */
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
            define("ERROR", ["Id nenalezeno"]);
            return false;
        }
        $this->database->delete("todo-list", ["id"], [$id]);
        
        define("MESSAGE", ["Úkol úspěšně smazán!"]);
        return true;
    }
}
