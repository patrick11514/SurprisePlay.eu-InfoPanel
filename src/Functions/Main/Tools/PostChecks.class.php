<?php

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
    public function __construct($method, array $check)
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
                        "password"
                    ],
                    "db_requests" => [
                        "use" => false,
                    ],
                    "check_with" => [
                        "method" => "function",
                        "class" => "\patrick115\Adminka\Players\Settings",
                        "function" => "checkSettings",
                        "custom_error" => "Nastavení nebylo změněno!",
                        "success_message" => "<span style=\"color:green\">Nastavení úspěšně změněno!</span>",
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
                            ]
                        ]
                    ]
                ];
            break;
        }
    }
}