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
        "domain" => null //Změna domény v titlu stránky
    ],


    "API" => [
        "permissions" => [
            "get-user-list" => [
                "group" => "leaders" //Jméno kategorie, co má práva získavat informace z API (status gemů, todo...)
            ]
        ]
    ],

    "debug" => true,

    "Main" => [ 
        "player_info" => [ //Informace o hráči na hlavní stránce
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
            ],          
        ],
        "admin_accounts" => [ //Které groupky mají admin účet v admince
            "zk-builder",
            "zk-helper",
            "builder",
            "helper",
            "e-helper",
            "hl-builder",
            "hl-helper",
            "technik",
            "leader",
            "vedeni",
            "majitel"
        ],
        "group_names" => [ //Jak se mají jména groupek zobrazovat
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
            "e-helper" => "Elitní Helper",
            "hl-builder" => "Hlavní Builder",
            "hl-helper" => "Hlavní Helper",
            "technik" => "Technik",
            "leader" => "Leader",
            "vedeni" => "Vedení",
            "majitel" => "Majitel",
        ],
        "vips" => [ //seznam groupek, co jsou VIP
            "surprise",
            "sponzor",
            "legend",
            "heroic"
        ],
        "vip_levels" =>[ //čím menší číslo, tím vyšší level (1 = nejlepší vip) - Nejvyšší vip nemusí začínat číslem 1
            1 => "surprise",
            2 => "sponzor",
            3 => "legend",
            4 => "heroic"
        ],
        "group_colors" => [ //barvičky groupe
            "default" => "#7b7f85",
            "heroic" => "#55FF55",
            "legend" => "#55FFFF",
            "sponzor" => "#FFFF55",
            "surprise" => "#FF5555",
            "youtuber" => "#AA0000",
            "zk-builder" => "#AAAAAA",
            "zk-helper" => "#AAAAAA",
            "builder" => "#AA00AA",
            "helper" => "#00AA00",
            "e-helper" => "#00AAAA",
            "hl-builder" => "#AA00AA",
            "hl-helper" => "#00AA00",
            "technik" => "#FFFF55",
            "leader" => "#FF5555",
            "vedeni" => "#FF5555",
            "majitel" => "#AA0000",
        ],

        "navigation" => [ //Navigace
            "Hlavní Nabídka" => [ //kategorie
                "role" => "category", //role, že to je kategorie
                "permission" => "all", //kdo má přístup k této celé kategorii
                "items" =>[ //itemy v kategorii
                    "Info" => [ //Název itemu
                        "permission" => "all", //Kdo má přístup k itemu
                        "icon" => "fas fa-info", //ikonka
                        "link" => "?main", //stránka
                        "page-name" => "main" //název stránky
                    ],
                    "Nastavení účtu" => [
                        "permission" => "all",
                        "icon" => "fas fa-cog",
                        "link" => "?settings",
                        "page-name" => "settings"
                    ],
                    "Napsat Tiket" => [
                        "permission" => "all",
                        "icon" => "fas fa-pencil-alt",
                        "link" => "?ticket-write",
                        "page-name" => "ticket-write"
                    ]
                ]
            ],
            "Administrace" => [
                "role" => "category",
                "permission" => "mids",
                "items" => [
                    "Povolení VPN" => [
                        "permission" => "leaders",
                        "icon" => "fa fa-globe-europe",
                        "link" => "?vpn-allow",
                        "page-name" => "vpn-allow"
                    ],
                    "Odregistrování uživatele" => [
                        "permission" => "leaders",
                        "icon" => "fa fa-unlock",
                        "link" => "?unregister",
                        "page-name" => "unregister"
                    ],
                    "Správa gemů" => [
                        "permission" => "full",
                        "icon" => "fa fa-gem",
                        "link" => "?gems",
                        "page-name" => "gems"
                    ],
                    "To-Do list" => [
                        "permission" => "leaders",
                        "icon" => "fa fa-praying-hands",
                        "link" => "?todo",
                        "page-name" => "todo"
                    ],
                ]
            ],
            "Podpora" => [
                "role" => "category",
                "permission" => "mids",
                "items" => [
                    "Vedení" => [
                        "permission" => "full",
                        "icon" => "fas fa-circle",
                        "link" => "?tickets-user",
                        "page-name" => "tickets-user"
                    ],
                    "Hlavní Helper" => [
                        "permission" => "hl_helper",
                        "icon" => "fas fa-circle",
                        "link" => "?tickets-user",
                        "page-name" => "tickets-user"
                    ],
                    "Hlavní Builder" => [
                        "permission" => "hl_builder",
                        "icon" => "fas fa-circle",
                        "link" => "?tickets-user",
                        "page-name" => "tickets-user"
                    ],
                    "Helper" => [
                        "permission" => "helpers",
                        "icon" => "fas fa-circle",
                        "link" => "?tickets-user",
                        "page-name" => "tickets-user"
                    ]
                ]
            ],
            "Nábory" => [
                "role" => "category",
                "permission" => "all",
                "visible" => false, #true = zobrazit kategorii náborů; false = nezobrazovat; Toto nastavení lze přidat i do každé položky v items, i do jiných kategorií
                "items" => [
                    "Nábor na helpera" => [
                        "permission" => "all",
                        "icon" => "fa fa-hands-helping",
                        "link" => "#",
                        "page-name" => "#",
                    ]
                ]
            ]
        ],
        "page_perms" => [ //permisse, na určité stránky (Ne navigace)
            "MainPage" => "all",
            "Settings" => "all",
            "ErrorPage" => "all",
            "Logout" => "all",
            "VPNAllow" => "leaders",
            "Unregister" => "leaders",
            "Gems" => "full",
            "TodoList" => "leaders"
        ],
        "group-perms" => [ //jména skupin s permissema
            "full" => [
                "majitel",
                "vedeni",
                "technik",
                "leader"
            ],
            "hl_builder" => [
                "inherits" => [
                    "full"
                ],
                "hl-builder"
            ],
            "hl_helper" => [
                "inherits" => [
                    "full"
                ],
                "hl-helper"
            ],
            "leaders" => [
                "inherits" => [
                    "full"
                ],
                "hl-builder",
                "hl-helper",
                "e-helper"
            ],
            "helpers" => [
                "inherits" => [
                    "hl_helper"
                ],
                "helper",
                "zk-helper"
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
                "e-helper",
                "hl-builder",
                "hl-helper",
                "technik",
                "leader",
                "vedeni",
                "majitel",
            ]
        ],
        "todo-list" => "leaders", //Lidi, kteří budou na výběr při zadávání úkolu
        "todo-tags" => [ //tagy v todo listu
            "important" => [//Libovolné jméno tagu
                "name" => "Důležité", //Jméno tagu, které se bude zobrazovat
                "color" => "#AA0000" //Barva tagu
            ],
            "warning" => [
                "name" => "Varování",
                "color" => "#FFAA00"
            ]
        ]
    ]
];