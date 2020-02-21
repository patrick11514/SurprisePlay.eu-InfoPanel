<?php

namespace ZeroCz\Admin\Tickets;

use ZeroCz\Admin\System;
use ZeroCz\Admin\Session;
use ZeroCz\Admin\Config;
use ZeroCz\Admin\Database;
use ZeroCz\Admin\Cache;

/**
 * ZeroCz's Ticket system
 *
 * @version 1.0.0
 * @author ZeroCz
 */
class Ticket
{
    public const ADMIN = 1;

    public const TICKET_NEW   = 0;
    public const TICKET_REPLY = 1;

    private const WAITING_PLAYER = 1;
    private const WAITING_ADMIN  = 2;
    private const LOCK           = 3;
    private const UNLOCK         = 4;
    private const ARCHIV         = 5;
    private const PRERAZEN       = 6;

    public const TICKET_UZAVREN  = 'uzavren';
    public const TICKET_ARCHIV   = 'archiv';
    public const TICKET_PRERAZEN = 'prerazen';

    private $db;
    private $id;
    private $admin;

    private $group = 'helper';

    private $cache;
    private $validate;

    private $data = [];

    public function __construct(bool $admin = false)
    {
        $this->admin = $admin;
        $this->cache = Cache::init()->cache();
        $this->db    = Database::init()->db();
        $this->db->query("USE `tickets`");
        $this->validate = new \Validation();
    }

    public function select($id, $group)
    {
        $this->id    = $id;
        $this->group = $group;
    }

    public function getSelected()
    {
        $this->select($_GET['id'], $_GET['group']);
        return $this;
    }

    private function getGroupById($id)
    {
        $result = $this->db->select("tickets", "group", [
            "id" => $this->id,
        ]);
        return $result[0];
    }

    /**
     * Typy ticketů
     *
     * @return array
     */
    public function getTicketTypes()
    {
        return $this->cache->refreshIfExpired("ticket_text_type", function () {
            return Config::i()->getValue('ticket_text_type');
        }, 300);
    }

    /**
     * Uzamče ticket
     *
     */
    public function lock()
    {
        $this->db->update("tickets", [
            "status" => self::LOCK,
        ], [
            "id" => $this->id,
        ]);

        Cache::init()->cache()->eraseKey("ticket_list_$this->group");
    }

    /**
     * Otevře ticket
     */
    public function unlock($group = '')
    {
        $this->db->update("tickets", [
            "status" => self::UNLOCK,
        ], [
            "id" => $this->id,
        ]);

        Cache::init()->cache()->eraseKey("ticket_list_$this->group"); //! GROUPKA VE KTERÉ SE TO MÁ PAK ZOBRAZIT !
        //System::redirect("viewer.php?group=helper&id=$this->id");
    }

    public function archive()
    {
        $this->db->update("tickets", [
            "status" => 5,
        ], [
            "id" => $this->id,
        ]);
        Cache::init()->cache()->eraseKey("ticket_list_$this->group");
        Cache::init()->cache()->eraseKey("ticket_list_archive");
    }

    /**
     * Zkotroluje captchu
     * zda není text prázdný nebo příliš krátký
     *
     * @return bool
     */
    public function validate(int $type, $data, $captcha = null)
    {
        if (!$this->admin) {
            if (!Auth::captcha($captcha)) {
                System::setError('Nesprávná reCHAPTCHA!');
                return false;
            }
        }

        if ($type === self::TICKET_NEW) {
            return $this->validateNewTicket($data[0], $data[1], $data[2]);
        } elseif ($type === self::TICKET_REPLY) {
            return $this->validateReplyTicket($data);
        }
    }

    private function validateNewTicket(string $subject, string $message, $type)
    {
        $subject = $this->prepareText($subject);
        $message = $this->prepareText($message);

        $result = false;
        foreach (Config::i()->getValue('ticket_text_type') as $value) {
            if (array_key_exists($type, $value)) {
                $result = true;
            }
        }

        if (!$result) {
            System::setError('Nebyl zvolen typ ticketu!');
            return false;
        }

        $this->validate->name('předmět')->value($subject)->min(3)->max(105)->required();
        $this->validate->name('zpráva')->value($message)->min(3)->max(8192)->required();

        $group = Config::init()->getValue('ticket_urceni');

        if ($this->validate->isSuccess()) {
            $this->data['message'] = $message;
            $this->data['subject'] = $subject;
            $this->data['type']    = $type;
            $this->data['group']   = $group[$type];
            $group                 = Config::init()->getValue('ticket_urceni');
            return true;
        }

        System::setError($this->validate->getError());
        return false;
    }

    private function validateReplyTicket(string $message)
    {
        $message = $this->prepareText($message);

        $this->validate->name('zpráva')->value($message)->min(3)->max(8192)->required();

        if ($this->validate->isSuccess()) {
            $group                 = Config::init()->getValue('ticket_urceni');
            $this->data['message'] = $message;
            return true;
        }

        return false;
    }

    /**
     * Celý ticket list
     *
     * @return array
     */
    public function getTicketList($group)
    {
        if ($group === 'archiv') {
            return $this->getArchivedTickets();
        }

        return $this->cache->refreshIfExpired("ticket_list_$group", function () use ($group) {
            //global $group;
            /*if ($this->admin) {
            $stmt = $this->conn->prepare("SELECT `id`, `created`, `owner`, `subject`, `type`, `status` FROM `tickets` ORDER BY `id` DESC;");
            $stmt->execute();
            } else {
            $stmt = $this->conn->prepare("SELECT `id`, `created`, `owner`, `subject`, `type`, `status` FROM `tickets` WHERE `owner_id` = ? ORDER BY `id` DESC;");
            $stmt->execute([Session::get('id')]);
            }*/
            try {
                $result = $this->db->select("tickets", [
                    "id",
                    "created",
                    "status",
                    "type",
                    "subject",
                    "owner",
                    "group",
                ], [
                    "group"     => $group,
                    "status[!]" => self::ARCHIV,
                    "ORDER"     => ["id" => "DESC"],
                ]);
                if (count($result) > 0) {
                    $result = System::escape($result, true);
                    return $result;
                }
            } catch (\PDOException $e) {}
            return false;
        }, 300);
    }

    public function getArchivedTickets()
    {
        return $this->cache->refreshIfExpired("ticket_list_archive", function () {
            try {
                $result = $this->db->select("tickets", [
                    "id",
                    "created",
                    "status",
                    "type",
                    "subject",
                    "owner",
                    "group",
                ], [
                    "status" => self::ARCHIV,
                    "ORDER"  => ["id" => "DESC"],
                ]);
                if (count($result) > 0) {
                    $result = System::escape($result, true);
                    return $result;
                }
            } catch (\PDOException $e) {}
            return false;
        }, 300);
    }

    /**
     * Vytvoří ticket
     */
    public function createTicket()
    {
        $this->db->insert("tickets", [
            "type"     => $this->data['type'],
            "subject"  => $this->data['subject'],
            "owner"    => Session::get('realname'),
            "owner_id" => Session::get('id'),
            "group"    => $this->data['group'],
        ]);

        $result = $this->db->select("tickets", "id", [
            "ORDER" => ["id" => "DESC"],
            "LIMIT" => 1,
        ]);

        $this->db->insert("posts", [
            "user_id"   => Session::get('id'),
            "ticket_id" => $result[0],
            "message"   => $this->data['message'],
            "username"  => Session::get('realname'),
            "group"     => $this->data['group'],
            "admin"     => 0,
        ]);

        $this->cache->eraseKey('ticket_list_' . $this->data['group']);

        System::redirect("./tickets.php?id=" . $result);
    }

    /**
     * Odpoví na ticket
     */
    public function replyTicket()
    {
        if ($this->admin) {
            $status = self::WAITING_PLAYER;
        } else {
            $status = self::WAITING_ADMIN;
        }

        $result = $this->db->select("tickets", "status", [
            "id" => $this->id,
        ]);

        if ($result[0] !== self::TICKET_UZAVREN) {

            $this->db->insert("posts", [
                "user_id"   => Session::get('id'),
                "ticket_id" => $this->id,
                "message"   => $this->data['message'],
                "username"  => Session::get('realname'),
                "group"     => $this->group,
                "admin"     => (int) $this->admin,
            ]);

            $this->db->update("tickets", [
                "status" => $status,
            ], [
                "id" => $this->id,
            ]);

            Cache::init()->cache()->eraseKey("ticket_list_$this->group");

        } else {
            System::setError('Ticket je uzamčen!');
        }
    }

    /**
     * Zobrazí ticket
     *
     * @return array
     */
    public function viewTicket()
    {
        try {
            $sql = ["id" => $this->id, "owner_id" => Session::get('id')];

            if ($this->admin) {
                $sql = ["id" => $this->id, "group" => $this->group, "status[!]" => self::ARCHIV];
            }

            if ($this->group === 'archiv') {
                $sql = ['id' => $this->id, "status" => self::ARCHIV];
            }

            $result = $this->db->select("tickets", [
                "id",
                "owner",
                "status",
                "type",
                "subject",
                "created",
            ], [
                "AND" => $sql,
            ]);

            if (empty($result)) {
                return [];
            }
        } catch (\PDOException $e) {}

        return System::escape($result, true);
    }

    public function reassign($group)
    {
        foreach (Config::get('ticket_perms') as $key => $value) {
            if ($group == $key) {
                $this->db->update("tickets", [
                    "group"  => $group,
                    "status" => self::PRERAZEN,
                ], [
                    "id" => $this->id,
                ]);

                Cache::init()->cache()->eraseKey("ticket_list_$this->group");
                Cache::init()->cache()->eraseKey("ticket_list_$group");
                System::redirect('./tickets.php');
                break;
            }
        }
        System::setError('Nepovedlo se přeřadit ticket!');
    }

    /**
     * Zobrazí zobrazí zprávy v ticketu
     *
     * @return array
     */
    public function viewTicketPost()
    {

        $result = $this->db->select("posts", [
            "message",
            "post_date",
            "username",
            "admin",
        ], [
            "ticket_id" => $this->id,
            "ORDER"     => ["post_id" => "ASC"],
        ]);

        if (!count($result) > 0) {
            return [];
        }

        return $result;
    }

    /**
     * Připravý text pro insert do databáze
     *
     * @return string
     */
    private function prepareText(string $text)
    {
        $text = trim($text);
        $text = \stripslashes($text);
        $text = System::purify($text);
        return $text;
    }
}
