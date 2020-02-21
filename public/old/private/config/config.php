<?php
return [
    "host"                        => "127.0.0.1",
    "port"                        => 3306,
    "user"                        => "root",
    "password"                    => "BV64qYYqIizV5XJzvi",
    "database"                    => "",
    "captcha_secret"              => "6Lcb1ccUAAAAAGdfJd5e5Uzjyd4fJsKGGK1QMG0s",
    "captcha_site"                => "6Lcb1ccUAAAAAPGmJctMqGWRo7wPcXxbY8YqMsX9",
    // KDO SE MŮŽE PŘIHLÁSIT
    "groups"                      => [
        'Majitel'         => 'majitel',
        'Vedení'          => 'vedeni',
        'Leader'          => 'leader',
        'Technik'         => 'technik',
        'Hlavní Helper'   => 'hl-helper',
        'Hlavní Builder'  => 'hl-builder',
        'Elitní Helper'   => 'el-helper',
        'Helper'          => 'helper',
        //'Builder',
        'Zkušební Helper' => 'zk-helper',
        //'Zkušební Builder',
        //'Veterán'         => 'veteran',
    ],
    // TICKETY
    'ticket_text'                 => [
        'hrac'     => '<span class="badge badge-warning">Čeká na odpověď hráče</span>',
        'podpora'  => '<span class="badge badge-info">Čeká na odpověď podpory</span>',
        'uzavren'  => '<span class="badge badge-danger">Uzavřený</span>',
        'otevren'  => '<span class="badge badge-success">Otevřený</span>',
        'archiv'   => '<span class="badge badge-secondary">Archivován</span>',
        'prerazen' => '<span class="badge badge-dark">Přeřazeno</span>',
    ],
    'ticket_text_admin'           => [
        'hrac'     => '<span class="badge badge-info">Čeká na odpověď hráče</span>',
        'podpora'  => '<span class="badge badge-warning">Čeká na odpověď podpory</span>',
        'uzavren'  => '<span class="badge badge-danger">Uzavřený</span>',
        'otevren'  => '<span class="badge badge-success">Otevřený</span>',
        'archiv'   => '<span class="badge badge-secondary">Archivován</span>',
        'prerazen' => '<span class="badge badge-dark">Přeřazeno</span>',
    ],

    'ticket_text_type'            => [
        'Nahlášení hráče' => [
            'hacker'  => 'Nahlášení hackera',
            'slovnik' => 'Nevhodný slovník',
            'reklama' => 'Reklama',
            'spam'    => 'Spam',
            'nick'    => 'Nevhodné jméno',
        ],
        'Žádosti'         => [
            'vpn'     => 'Povolení VPN',
            'unbanmc' => 'Žádost o Unban na Serveru',
            'unbants' => 'Žádost o Unban na TeamSpeak',
        ],
        'VIP'             => [
            'vipsms'   => 'Problém s VIP - SMS',
            'vipother' => 'Problém s VIP - OSTATNÍ',
        ],
        'Spolupráce'      => [
            'partner'  => 'Nabídka partnerství',
            'youtuber' => 'Chci být YouTuber',
        ],
        'Ostatní'         => [
            'chyba' => 'Nahlášení chyby',
            'other' => 'Jiné',
        ],
        'Ahoj'            => [
            'pomoc' => 'Potřebuju pomoc!',
        ],
        'Tickety'         => [
            'chyba' => 'Našel jsem chybu',
        ],

    ],

    //texty
    'ticket_sidebar_replace'      => [
        'helper'     => 'Helper',
        'hl-helper'  => 'Hlavní Helper',
        'hl-builder' => 'Hlavní Builder',
        'technik'    => 'Technik',
        'vedeni'     => 'Vedení',
        'archiv'     => 'Archiv',
    ],

    'groups_sidebar_replace'      => [
        'ateam'   => 'Správa týmu',
        'helper'  => 'Správa helperů',
        'builder' => 'Správa builderů',
        'youtube' => 'Správa youtuberů',
    ],

    // priradit k...
    'ticket_urceni'               => [
        'hacker'   => 'helper',
        'slovnik'  => 'helper',
        'reklama'  => 'helper',
        'spam'     => 'helper',
        'nick'     => 'helper',
        // separator
        'vpn'      => 'hl-helper',
        'unbanmc'  => 'helper',
        'unbants'  => 'hl-helper',
        // separator
        'vipsms'   => 'vedeni',
        'vipother' => 'vedeni',
        // separator
        'partner'  => 'vedeni',
        'youtuber' => 'vedeni',
        // separator
        'chyba'    => 'technik',
        'other'    => 'helper',
    ],

    //PERMISSE
    'ticket_perms'                => [
        'vedeni'     => ['majitel', 'vedeni', 'leader'],
        'technik'    => ['majitel', 'vedeni', 'leader', 'technik'],
        'hl-builder' => ['majitel', 'vedeni', 'leader', 'technik', 'hl-builder'],
        'hl-helper'  => ['majitel', 'vedeni', 'leader', 'technik', 'hl-helper'],
        'helper'     => ['majitel', 'vedeni', 'leader', 'technik', 'hl-helper', 'helper', 'zk-helper'],
        'archiv'     => ['majitel', 'vedeni', 'leader', 'technik'],
    ],

    'payment_perms'               => ['majitel', 'vedeni', 'leader', 'technik'],

    'groups_perms'                => [ //['vedeni', 'leader', 'technik', 'hl-helper', 'e-helper', 'helper', 'zk-helper', 'hl-builder', 'e-builder', 'builder', 'zk-helper'],
        'ateam'   => [
            'majitel' => ['vedeni', 'leader', 'technik'], // do sekce "ateam" vidí "majitel" a může nahazovat "vedeni", "leader", "technik" na serveru
            'vedeni'  => ['leader', 'technik'], //do sekce "ateam" vidí (taky) "vedeni" a může nahazovat "leader", "technik"
        ],
        'helper'  => [
            'majitel'   => ['hl-helper', 'e-helper', 'helper', 'zk-helper'],
            'vedeni'    => ['hl-helper', 'e-helper', 'helper', 'zk-helper'],
            'leader'    => ['hl-helper', 'e-helper', 'helper', 'zk-helper'],
            'technik'   => ['hl-helper', 'e-helper', 'helper', 'zk-helper'],
            'hl-helper' => ['e-helper', 'helper', 'zk-helper'],
            'el-helper' => ['helper', 'zk-helper'],
        ],
        'builder' => [
            'majitel'    => ['hl-builder', 'e-builder', 'builder', 'zk-builder'],
            'vedeni'     => ['hl-builder', 'e-builder', 'builder', 'zk-builder'],
            'leader'     => ['hl-builder', 'e-builder', 'builder', 'zk-builder'],
            'technik'    => ['hl-builder', 'e-builder', 'builder', 'zk-builder'],
            'hl-builder' => ['e-builder', 'builder', 'zk-builder'],
        ],
        'youtube' => [
            'majitel' => ['youtuber'],
            'vedeni'  => ['youtuber'],
        ],
    ],

    'ticket_archive_button_perms' => ['majitel', 'vedeni', 'leader', 'technik', 'hl-helper', 'hl-builder'],
];
