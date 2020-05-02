<?php

/**
 * Get help arrays for Core of Requests
 * 
 * @author    patrick115 <info@patrick115.eu>
 * @copyright ©2020
 * @link      https://patrick115.eu
 * @link      https://github.com/patrick11514
 * @version   1.0.0
 * 
 */

namespace patrick115\Main\Tools;

class PostChecks
{
    /**
     * Store method
     * @param string
     */
    private $method;

    /**
     * Construct function
     * @param string $method
     * @param array  $check
     */
    public function __construct(string $method, array $check)
    {
        if (!in_array($method, $check)) {
            \patrick115\Main\Error::init()->catchError("Method $method not found!", debug_backtrace());
            return;
        }
        $this->method = $method;
    }

    /**
     * Get array by method
     * @return array
     */
    public function get()
    {
        if (empty($this->method)) return [];
        switch (strtolower($this->method)) {
            case "login":
                return [
                    "check" => [
                        "username",
                        "password"
                    ],
                    "db_requests" => [
                        "use" => true,
                        "custom_error" => "Jméno neexistuje!",
                        "databases" => [
                            "main_authme" => [
                                "authme" => [
                                    "password" => [
                                        "by" => "username",
                                        "hash" => "sha256",
                                        "check_with" => "password"
                                    ]
                                ]
                            ],
                            "main_perms" => [
                                "perms_players" => [
                                    "primary_group" => [
                                        "by" => "username"
                                    ]
                                ]
                            ],
                            "main_online" => [
                                "players" => [
                                    "name" => [
                                        "by" => [
                                            "post" => "username",
                                            "alias" => "name"
                                        ]
                                    ]
                                ]
                            ],
                        ],
                    ]
                ];
            break;
            case "settings":
                return [
                    "check" => [
                        "autologin",
                        "e-mail",
                        "password",
                        "skin"
                    ],
                    "db_requests" => [
                        "use" => false,
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Players\Settings",
                        "function" => "checkSettings",
                        "custom_error" => "Nastavení nebylo změněno!",
                        "success_message" => "Nastavení úspěšně změněno!",
                        "parameters" => [
                            "autologin" => [
                                "from" => "post"
                            ],
                            "e-mail" => [
                                "from" => "post"
                            ],
                            "password" => [
                                "from" => "post"
                            ],
                            "method" => [
                                "from" => "post"
                            ],
                            "skin" => [
                                "from" => "post"
                            ]
                        ]
                    ]
                ];
            break;
            case "vpn-allow":
                return [
                    "check" => [
                        "allow-nick"
                    ],
                    "db_requests" => [
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Players\Settings",
                        "function" => "allowVPN",
                        "custom_error" => "Hráč neexistuje!",
                        "parameters" => [
                            "username" => [
                                "from" => "post",
                                "alias" => "allow-nick"
                            ],
                            "method" => [
                                "from" => "post"
                            ]
                        ]
                    ]
                ];
            break;
            case "unregister":
                return [
                    "check" => [
                        "unregister-nick"
                    ],
                    "db_requests" => [
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Players\Settings",
                        "function" => "unregister",
                        "custom_error" => "Hráč neexistuje",
                        "parameters" => [
                            "username" => [
                                "from" => "post",
                                "alias" => "unregister-nick"
                            ],
                            "method" => [
                                "from" => "method"
                            ]
                        ]
                    ]
                ];
            break;
            case "gems":
                return [
                    "check" => [
                        "gems-nick",
                        "gem-count",
                        "gem-action"
                    ],
                    "db_requests" => [
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Players\Settings",
                        "function" => "gems",
                        "custom_error" => "Hráč neexistuje",
                        "parameters" => [
                            "gems-nick" => [
                                "from" => "post",
                            ],
                            "gem-count" => [
                                "from" => "post",
                            ],
                            "gem-action" => [
                                "from" => "post",
                            ],
                            "method" => [
                                "from" => "post"
                            ]
                        ]
                    ]
                ];
            break;
            case "todo":
                return [
                    "check" => [
                        "for",
                        "tags",
                        "message"
                    ],
                    "db_requests" => [
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Todo",
                        "function" => "addTodo",
                        "custom_error" => "Někde nastala chyba",
                        
                        "parameters" => [
                            "for" => [
                                "from" => "post"
                            ],
                            "tags" => [
                                "from" => "post"
                            ],
                            "message" => [
                                "from" => "post"
                            ],
                            "method" => [
                                "from" => "post"
                            ]
                        ]
                    ]
                ];
            break;
            case "remove-todo":
                return [
                    "check" => [
                        "id",
                    ],
                    "db_requests" => [
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Todo",
                        "function" => "removeTodo",
                        "parameters" => [
                            "id" => [
                                "from" => "post"
                            ],
                            "method" => [
                                "from" => "post"
                            ]
                        ]
                    ]
                ];
            break;
            case "ticket-write":
                return [
                    "check" => [
                        "name",
                        "type",
                        "message"
                    ],
                    "db_requests" => [
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Tickets",
                        "function" => "writeTicket",
                        "custom_error" => "Někde nastala chyba",
                        "parameters" => [
                            "username" => [
                                "from" => "session",
                                "path" => "Account/User/Username"
                            ],
                            "name" => [
                                "from" => "post"
                            ],
                            "type" => [
                                "from" => "post"
                            ],
                            "message" => [
                                "from" => "post"
                            ],
                            "method" => [
                                "from" => "text",
                                "text" => "createTicket"
                            ],
                            "img_url" =>[
                                "from" => "uploader"
                            ]
                        ]
                    ],
                    "uploader" => [
                        "enabled" => true,
                        "method" => "if",
                        "name" => "file",
                        "allowed_extensions" => [
                            "png",
                            "jpg",
                            "jpeg",
                            "webp",
                            "bmp",
                            "gif"
                        ],
                        "on" => [
                            "upload" => [
                                "method" => "function",
                                "class" => "\patrick115\Images\Uploader",
                                "function" => "uploadFile",
                                "parameters" => [
                                    
                                ]
                            ],
                            "error" => [
                                "method" => "error"
                            ]
                        ]
                    ]
                ];
            break;
            case "ticket-send-message":
                return [
                    "check" => [
                        "message",
                        "ticket_id"
                    ],
                    "db_requests" => [
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Tickets",
                        "function" => "ticketCallback",
                        "custom_error" => "Někde nastala chyba",
                        "success_message" => "Zpráva odeslána!",
                        "parameters" => [
                            "username" => [
                                "from" => "session",
                                "path" => "Account/User/Username"
                            ],
                            "message" => [
                                "from" => "post"
                            ],
                            "ticket_id" => [
                                "from" => "post"
                            ],
                            "method" => [
                                "from" => "text",
                                "text" => "callback"
                            ],
                            "callback" => [
                                "from" => "text",
                                "text" => "send-message"
                            ],
                            "source_page" => [
                                "from" => "post"
                            ],
                            "img_url" =>[
                                "from" => "uploader"
                            ]
                        ]
                    ],
                    "uploader" => [
                        "enabled" => true,
                        "method" => "if",
                        "name" => "file",
                        "allowed_extensions" => [
                            "png",
                            "jpg",
                            "jpeg",
                            "webp",
                            "bmp",
                            "gif"
                        ],
                        "on" => [
                            "upload" => [
                                "method" => "function",
                                "class" => "\patrick115\Images\Uploader",
                                "function" => "uploadFile",
                                "parameters" => [
                                    
                                ]
                            ],
                            "error" => [
                                "method" => "error"
                            ]
                        ]
                    ]
                ];
            break;
            case "ticket-send-message-admin":
                return [
                    "check" => [
                        "message",
                        "ticket_id"
                    ],
                    "db_requests" => [
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Tickets",
                        "function" => "ticketCallback",
                        "custom_error" => "Někde nastala chyba",
                        "success_message" => "Zpráva odeslána!",
                        "parameters" => [
                            "username" => [
                                "from" => "session",
                                "path" => "Account/User/Username"
                            ],
                            "message" => [
                                "from" => "post"
                            ],
                            "ticket_id" => [
                                "from" => "post"
                            ],
                            "method" => [
                                "from" => "text",
                                "text" => "callback"
                            ],
                            "callback" => [
                                "from" => "text",
                                "text" => "send-message-admin"
                            ],
                            "source_page" => [
                                "from" => "post"
                            ],
                            "img_url" =>[
                                "from" => "uploader"
                            ]
                        ]
                    ],
                    "uploader" => [
                        "enabled" => true,
                        "method" => "if",
                        "name" => "file",
                        "allowed_extensions" => [
                            "png",
                            "jpg",
                            "jpeg",
                            "webp",
                            "bmp",
                            "gif"
                        ],
                        "on" => [
                            "upload" => [
                                "method" => "function",
                                "class" => "\patrick115\Images\Uploader",
                                "function" => "uploadFile",
                                "parameters" => [
                                    
                                ]
                            ],
                            "error" => [
                                "method" => "error"
                            ]
                        ]
                    ]
                ];
            break; 
            case "toggle-ticket":
                return [
                    "check" => [
                        "value",
                        "ticket_id"
                    ],
                    "db_requests" => [
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Tickets",
                        "function" => "ticketCallback",
                        "custom_error" => "Někde nastala chyba",
                        "success_message" => "Nastavení tiketu úspěšně změněno",
                        "parameters" => [
                            "username" => [
                                "from" => "session",
                                "path" => "Account/User/Username"
                            ],
                            "value" => [
                                "from" => "post"
                            ],
                            "ticket_id" => [
                                "from" => "post"
                            ],
                            "method" => [
                                "from" => "text",
                                "text" => "callback"
                            ],
                            "callback" => [
                                "from" => "text",
                                "text" => "toggle-ticket"
                            ],
                        ]
                    ]
                ];
            break; 
            case "remove-vpn":
                return [
                    "check" => [
                        "id"
                    ],
                    "db_requests" => [
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Players\Settings",
                        "function" => "removeVPN",
                        "custom_error" => "Někde nastala chyba",
                        "success_message" => "Úspěšně zrušena VPN",
                        "parameters" => [
                            "id" => [
                                "from" => "post" 
                            ],
                            "method" => [
                                "from" => "text",
                                "text" => "removeVPN"
                            ]
                        ]
                    ]
                ];
            break;
            case "player-vpn-allow":
                return [
                    "check" => [
                        "reason",
                        "confirm"
                    ],
                    "db_requests" => [
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Tickets",
                        "function" => "allowUserVPN",
                        "custom_error" => "Někde nastala chyba",
                        "success_message" => "Žádost byla odeslána",
                        "parameters" => [
                            "reason" => [
                                "from" => "post" 
                            ],
                            "confirm" => [
                                "from" => "post"
                            ],
                            "method" => [
                                "from" => "text",
                                "text" => "allowVPN"
                            ],
                            "username" => [
                                "from" => "session",
                                "path" => "Account/User/Username"
                            ]
                        ]
                    ]
                ];
            break;
            case "ticket-change-group":
                return [
                    "check" => [
                        "group",
                        "ticket_id"
                    ],
                    "db_requests" => [
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Tickets",
                        "function" => "changeTicketGroup",
                        "custom_error" => "Někde nastala chyba",
                        "success_message" => "Skupina byla změněna",
                        "parameters" => [
                            "group" => [
                                "from" => "post" 
                            ],
                            "method" => [
                                "from" => "text",
                                "text" => "changeGroup"
                            ],
                            "username" => [
                                "from" => "session",
                                "path" => "Account/User/Username"
                            ],
                            "ticket_id" => [
                                "from" => "post"
                            ]
                        ]
                    ]
                ];
            break;
            case "changedata":
                return [
                    "check" => [
                        "from-nick",
                        "to-nick",
                        "type"
                    ],
                    "db_requests" => [
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Minecraft\ChangeData",
                        "function" => "changeData",
                        "custom_error" => "Někde nastala chyba",
                        "success_message" => "Data byla přesunuta",
                        "parameters" => [
                            "from-nick" => [
                                "from" => "post" 
                            ],
                            "to-nick" => [
                                "from" => "post",
                            ],
                            "type" => [
                                "from" => "post"
                            ]
                        ]
                    ]
                ];
            break;
            case "unban":
                return [
                    "check" => [
                        "nick",
                        "reason"
                    ],
                    "db_requests" => [
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Players\Settings",
                        "function" => "unban",
                        "custom_error" => "Někde nastala chyba",
                        "success_message" => "Hráč byl odbanován!",
                        "parameters" => [
                            "nick" => [
                                "from" => "post" 
                            ],
                            "reason" => [
                                "from" => "post"
                            ],
                            "method" => [
                                "from" => "text",
                                "text" => "unban"
                            ]
                        ] 
                    ]
                ];
            break;
            case "delete-ticket":
                return [
                    "check" => [
                        "method",
                        "id"
                    ],
                    "db_requests" =>[
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Tickets",
                        "function" => "ticketCallback",
                        "custom_error" => "Nepovedlo se smazat tiket!",
                        "success_message" => "Tiket byl smazán",
                        "parameters" => [
                            "username" => [
                                "from" => "session",
                                "path" => "Account/User/Username"
                            ],
                            "method" => [
                                "from" => "text",
                                "text" => "callback"
                            ],
                            "callback" => [
                                "from" => "text",
                                "text" => "delete-ticket"
                            ],
                            "id" => [
                                "from" => "post"
                            ]
                        ]
                    ]
                ];
            break;
            case "block-user":
                return [
                    "check" => [
                        "ticket_id",
                        "value",
                        "user"
                    ],
                    "db_requests" =>[
                        "use" => false
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Tickets",
                        "function" => "ticketCallback",
                        "custom_error" => "Někde nastala chyba!",
                        "success_message" => "Akce byla provedena úspěšně",
                        "parameters" => [
                            "username" => [
                                "from" => "session",
                                "path" => "Account/User/Username"
                            ],
                            "method" => [
                                "from" => "text",
                                "text" => "callback"
                            ],
                            "callback" => [
                                "from" => "text",
                                "text" => "block-user"
                            ],
                            "ticket_id" => [
                                "from" => "post"
                            ],
                            "value" => [
                                "from" => "post"
                            ],
                            "user" => [
                                "from" => "post"
                            ],
                            "skip-block" => [
                                "from" => "post"
                            ]
                        ]
                    ]
                ];
            break;
        }
    }
}