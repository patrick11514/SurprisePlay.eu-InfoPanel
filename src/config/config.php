<?php

return [
    "Database" => [
        "address" => "localhost",
        "port" => 3306,
        "username" => "root",
        "password" => "BV64qYYqIizV5XJzvi",
        "database" => "adminka",
    ],
    "Aliases" => [
        "domain" => null
    ],





    "Main" => [
        "player_info" => [
            "username" => [
                "name" => "Jméno",
                "source" => [
                    "source_name" => "session",
                    "data" => "Account/User/Username"
                ],
            ],
            "group" => [
                "name" => "Pozice",
                "source" => [
                    "source_name" => "function",
                    "class" => "\patrick115\Adminka\Players\Rank",
                    "function" => "getRank",
                    "create_param" => [
                        "from" => "session",
                        "data" => "Account/User/Username"
                    ]
                ]
            ],
            "vyprsi" => [
                "name" => "Datum expirace VIP",
                "source" => [
                    "source_name" => "function",
                    "class" => "\patrick115\Minecraft\Stats",
                    "function" => "getVipExpiry",
                    "create_param" => [
                        "from" => "session",
                        "data" => "Account/User/Username"
                    ]
                ]
            ],
            "ip_address" => [
                "name" => "IP Adresa",
                "source" => [
                    "source_name" => "database",
                    "command" => "SELECT `ip` FROM `main_authme`.`authme` WHERE `realname` = '%1' LIMIT 1;",
                    "select" => "ip",
                    "vars" => [
                        "%1" => [
                            "from" => "session",
                            "data" => "Account/User/Username"
                        ]
                    ]
                ]
            ],
            "autologin" => [
                "name" => "Autologin",
                "source" => [
                    "source_name" => "function",
                    "class" => "\patrick115\Minecraft\Stats",
                    "function" => "getAutologinStatus",
                    "create_param" => [
                        "from" => "session",
                        "data" => "Account/User/Username"
                    ]
                ]
            ],
            "antiVPN" => [
                "name" => "Povolení přístupu s VPN",
                "source" => [
                    "source_name" => "function",
                    "class" => "\patrick115\Minecraft\Stats",
                    "function" => "getAntiVPNStatus",
                    "create_param" => [
                        "from" => "session",
                        "data" => "Account/User/Username"
                    ]
                ]
            ],
            "money" => [
                "name" => "Peníze",
                "source" => [
                    "source_name" => "function",
                    "class" => "\patrick115\Minecraft\Stats",
                    "function" => "getMoney",
                    "create_param" => [
                        "from" => "session",
                        "data" => "Account/User/Username"
                    ]
                ]
            ],
            "gemy" => [
                "name" => "Gemy",
                "source" => [
                    "source_name" => "database",
                    "command" => "SELECT `value` FROM `main_kredity`.`supercredits` WHERE `name` = '%1' LIMIT 1;",
                    "select" => "value",
                    "vars" => [
                        "%1" => [
                            "from" => "session",
                            "data" => "Account/User/Username"
                        ]
                    ]
                ]
            ],
            "votes" => [
                "name" => "Hlasů celkem",
                "source" => [
                    "source_name" => "database",
                    "command" => "SELECT `votifier` FROM `survival_cmi`.`cmi_users` WHERE `username` = '%1' LIMIT 1;",
                    "select" => "votifier",
                    "vars" => [
                        "%1" => [
                            "from" => "session",
                            "data" => "Account/User/Username"
                        ]
                    ]
                ]
            ]
        ],
        "admin_accounts" => [
            "zk-builder",
            "zk-helper",
            "builder",
            "helper",
            "hl-builder",
            "hl-helper",
            "technik",
            "leader",
            "vedeni",
            "majitel"
        ],
        "group_names" => [
            "default" => "Hráč",
            "heroic" => "Heroic",
            "legend" => "Legend",
            "sponzor" => "Sponzor",
            "surprise" => "Surprise",
            "youtuber" => "YouTuber",
            "zk-builder" => "Zkušební Builder",
            "zk-helper" => "Zkušební Helper",
            "builder" => "Builder",
            "helper" => "Helper",
            "hl-builder" => "Hlavní Builder",
            "hl-helper" => "Hlavní Helper",
            "technik" => "Technik",
            "leader" => "Leader",
            "vedeni" => "Vedení",
            "majitel" => "Majitel",
        ],
        "vips" => [
            "surprise",
            "sponzor",
            "legend",
            "heroic"
        ],
        "vip_levels" =>[ //čím menśí číslo, tím vyšší level (1 = nejlepší vip) - Nejvyšší vip nemusí začínat číslem 1
            1 => "surprise",
            2 => "sponzor",
            3 => "legend",
            4 => "heroic"
        ],
        "group_colors" => [
            "default" => "#C2C7D0",
            "heroic" => "#55FF55",
            "legend" => "#55FFFF",
            "sponzor" => "#FFFF55",
            "surprise" => "#FF5555",
            "youtuber" => "#AA0000",
            "zk-builder" => "#AAAAAA",
            "zk-helper" => "#AAAAAA",
            "builder" => "#AA00AA",
            "helper" => "#00AA00",
            "hl-builder" => "#AA00AA",
            "hl-helper" => "#00AA00",
            "technik" => "#FFFF55",
            "leader" => "#FF5555",
            "vedeni" => "#FF5555",
            "majitel" => "#AA0000",
        ],

        "navigation" => [
            "Hlavní Nabídka" => [
                "role" => "category",
                "permission" => "all",
                "items"=>[
                    "Info" => [
                        "permission" => "all",
                        "icon" => "fas fa-info",
                        "link" => "?main",
                        "page-name" => "main"
                    ],
                    "Nastavení účtu" => [
                        "permission" => "all",
                        "icon" => "fas fa-cog",
                        "link" => "?settings",
                        "page-name" => "settings"
                    ]
                ]
            ]
        ],
        "page_perms" => [
            "MainPage" => "all",
            "Settings" => "all",
            "ErrorPage" => "all",
            "Logout" => "all",
        ],
        "group-perms" => [
            "full" => [
                "majitel",
                "vedeni",
                "technik",
                "leader"
            ],
            "leaders" => [
                "inherits" => [
                    "full"
                ],
                "hl-builder",
                "hl-helper"
            ],
            "mids" => [
                "inherits" => [
                    "leaders"
                ],
                "builder",
                "helper",
                "zk-builder",
                "zk-helper"
            ],
            "default" => [
                "inherits" => [
                    "mids"
                ],
                "youtuber",
                "surprise",
                "sponzor",
                "legend",
                "heroic",
                "default"
            ],
            "all" => [
                "default",
                "heroic",
                "legend",
                "sponzor",
                "surprise",
                "youtuber",
                "zk-builder",
                "zk-helper",
                "builder",
                "helper",
                "hl-builder",
                "hl-helper",
                "technik",
                "leader",
                "vedeni",
                "majitel",
            ]
        ]
    ]
];