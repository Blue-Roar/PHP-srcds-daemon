<?php
// Source Dedicated Servers to be monitored
define("SERVERS",[
    "example1" => [
        "name" => "Example Local Server",
        "addr" => "10.0.1.1", // srcds address
        "port" => 27015, // srcds port
        "schtask" => "srcds\\example1" // Path & name to the schedule task
    ],
    "example2" => [
        "name" => "Example Remote Server",
        "addr" => "10.0.1.2", // srcds address
        "port" => 27015, // srcds port
        "schtask" => "srcds\\example2", // Path & name to the schedule task
        "schauth" => [ // credentials for remote schedule task
            "server" => "10.0.1.2",
            "user" => "", // [<domain>\]<user>
            "password" => ""
        ]
    ]
]);

// Online indicator
define("ONLINE_INDICATOR_FIELD","ModDir");

// Retry attempts before server restart
define("RETRY_COUNT",2);

// File saving retry attempts
define("RETRY_FILE",__DIR__ . '/retry.json');
?>