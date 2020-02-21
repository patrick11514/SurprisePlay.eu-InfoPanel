<?php

namespace ZeroCz\Admin\Tickets;

use ZeroCz\Admin\Config;
use ZeroCz\Admin\Session;
use ZeroCz\Admin\System;
use ZeroCz\Admin\Database;

class TicketModel
{
    private $db;
    private $config;
    private $val;

    private $id;

    protected $errors = [];

    protected const HRAC_STRING = 'hrac';
    protected const PODPORA_STRING = 'podpora';
    protected const UZAVREN_STRING = 'uzavren';
    protected const OTEVREN_STRING = 'otevren';
    protected const ARCHIVE_STRING = 'archiv';
    protected const PRERAZEN_STRING = 'prerazen';

    protected const HRAC = 1;
    protected const PODPORA = 2;
    protected const UZAVREN = 3;
    protected const OTEVREN = 4;
    protected const ARCHIVE = 5;
    protected const PRERAZEN = 6;


    protected function __construct() {
        $this->db = Database::init()->db();
        $this->db->query('USE tickets');
    }

    private function _checkSelected() {
        throw new TicketException('Ticket is not selected');
    }

    protected function _getTicketById(int $id) {
        $data = $this->db->get('tickets', 'id', [
            'id' => $this->id
        ]);

        if (empty($data))
            throw new TicketException('Ticket neexistuje');

        $this->id = $id;
        return $this;
    }

    protected function _getTicketList($group, $archive = false) {

        $data = $this->db->select('tickets', [
            'id',
            'created',
            'status',
            'type',
            'subject',
            'owner',
            'group',
        ], [
            'group' => $group,
            'status[!]' => self::ARCHIVE,
            'ORDER' => ['id' => 'DESC'],
        ]);

        if (empty($data))
            throw new TicketException('Seznam ticketů je prázdný!');

        return $data;
    }

    protected function _getTicketListArchive() {
        $data = $this->db->select('tickets', [
            'id',
            'created',
            'status',
            'type',
            'subject',
            'owner',
            'group',
        ], [
            'group' => $group,
            'status' => self::ARCHIVE,
            'ORDER' => ['id' => 'DESC'],
        ]);

        if (empty($data))
            throw new TicketException('Seznam ticketů je prázdný!');

        return $data;
    }

    protected function _getTicket($group) {
        if (empty($this->id)) { return $this->_checkSelected(); }

        $data = $this->db->select('tickets', [
            'id',
            'owner',
            'status',
            'type',
            'subject',
            'created',
        ], ['id' => $this->id, 'group' => $group]);

        return $data;
    }

    protected function _getPost($group) {
        if (empty($this->id)) { return $this->_checkSelected(); }

        $data = $this->db->select('posts', [
            'message',
            'post_date',
            'username',
            'admin',
        ], ['ticket_id' => $this->id, 'ORDER' => ['post_id' => 'ASC'],]);

        return $data;
    }

    protected function _addPost(array $data) {
        if (empty($this->id)) { return $this->_checkSelected(); }

        $this->db->insert("posts", [
            "user_id"   => (int)    $data[0],
            "ticket_id" => (int)    $this->id,
            "message"   => (string) $data[1],
            "username"  => (string) $data[2],
            "group"     => (string) $data[3],
            "admin"     => (int)    $data[4]
        ]);
    }

    protected function _addTicket(array $data) {

        $this->db->insert("tickets", [
            "type"     => (string)  $data[0],
            "subject"  => (string)  $data[1],
            "owner"    => (string)  $data[2],
            "owner_id" => (int)     $data[3],
            "group"    => (string)  $data[4]
        ]);

        $this->id = $this->_getLastTicketId();
    }

    protected function _setStatus($status) {
        if (empty($this->id)) { return $this->_checkSelected(); }

        $this->db->update('tickets', [
            'status' => $status
        ], [
            'id' => $this->id
        ]);
    }

    protected function _setGroup($group) {
        if (empty($this->id)) { return $this->_checkSelected(); }

        $this->db->update('tickets', [
            'status' => $group
        ], [
            'id' => $this->id
        ]);
    }

    protected function _delete() {
        if (empty($this->id)) { return $this->_checkSelected(); }

        $this->db->delete('tickets', [
            'id' => $this->id
        ]);
    }

    private function _getLastTicketId() {
        $data = $this->db->query('SELECT id FROM tickets ORDER BY id DESC LIMIT 1')->fetch();
        return $data['id'];
    }
}

class TicketException extends \Exception {}
