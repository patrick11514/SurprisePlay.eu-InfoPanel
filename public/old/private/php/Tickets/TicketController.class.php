<?php

namespace ZeroCz\Admin\Tickets;

use ZeroCz\Admin\Config;

class TicketController extends TicketModel
{
    private $val;

    private $ticket;

    private $type;
    private $owner;
    private $ownerid;
    private $group;
    private $subject;
    private $message;

    public function __construct() {
        parent::__construct();
        $this->val = new \Validation();
    }

    public function setTicket($id) {
        try {
            $this->_getTicketById($id);
        } catch (TicketException $e) {}
    }

    public function setType($type) {
        $result = false;
        foreach (Config::get('ticket_text_type') as $value) {
            if (array_key_exists($type, $value))
                $result = true;
        }

        if (!$result)
            throw new TicketException('Nebyl zvolen typ ticketu!');

        $this->type = $type;
        $this->setGroup(Config::get('ticket_urceni')[$type]);
        return $this;
    }

    public function setOwner($owner) {
        $this->owner = $owner;
        return $this;
    }

    public function setOwnerId($ownerid) {
        $this->ownerid = $ownerid;
        return $this;
    }

    public function setSubject($subject) {
        $this->val->name('Předmět')->value($subject)->min(3)->max(105)->required();
        if (!$this->val->isSuccess())
            throw new TicketException($this->val->getError());

        $this->subject = $subject;
        return $this;
    }

    public function setMessage($message) {
        $this->val->name('Zpráva')->value($message)->min(3)->max(8192)->required();
        if (!$this->val->isSuccess())
            throw new TicketException($this->val->getError());

        $this->message = $message;
        return $this;
    }

    private function setGroup($group) {
        $this->group = $group;
        return $this;
    }

    public function create() {
        try {
            $id = $this->_addTicket([
                $this->type,
                $this->subject,
                $this->owner,
                $this->ownerid,
                $this->group
            ]);

            $this->_addPost([
                $this->ownerid,
                $this->message,
                $this->owner,
                $this->group,
                true
            ]);
        } catch (TicketException $e) {
            echo '<h1>'.$e->getMessage().'</h1>';
            return false;
        }

        return true;
    }

    public function reply() {
        $this->_addPost([
            $this->ownerid,
            $this->message,
            $this->owner,
            $this->group,
            true
        ]);
    }
}
