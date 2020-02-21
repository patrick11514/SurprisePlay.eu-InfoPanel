<?php

namespace ZeroCz\Admin\Tickets;

use ZeroCz\Admin\Config;

class TicketView extends TicketModel
{
    public function __construct() {
        parent::__construct();
    }

    public function getTicketList($group) {
        $view = [];
        $i = 0;
        try {
            foreach($this->_getTicketList($group) as $data) {
                foreach ($data as $key => $value) {
                    switch ($key) {
                        case 'created':
                            $view[$i][$key] = date("d.m.Y H:i:s", strtotime($value));
                            break;

                        case 'status':
                            foreach (Config::get('ticket_text_admin') as $key2 => $value2) {
                                if ($key2 == $value) {
                                    $view[$i][$key] = str_replace($key2, $value2, $value);
                                    break 2;
                                }
                            }

                        case 'type':
                            foreach (Config::get('ticket_text_type') as $value2) {
                                foreach ($value2 as $key3 => $value3) {
                                    if ($key3 == $value) {
                                        $view[$i][$key] = str_replace($key3, $value3, $value);
                                        break 3;
                                    }
                                }
                            }
                        
                        default:
                            $view[$i][$key] = $value;
                            break;
                    }
                }
                $i++;
            }
        } catch (TicketException $e) {
            return [];
        }
        //echo '<pre>';
        //print_r($view);
        //echo '</pre>';
        //exit;

        return $view;
    }
}