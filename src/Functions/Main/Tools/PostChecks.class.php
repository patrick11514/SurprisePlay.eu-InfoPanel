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
        }
    }
}