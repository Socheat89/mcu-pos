<?php
$payload = [
    'update_id' => 123456789,
    'callback_query' => [
        'id' => 'abc123',
        'from' => ['id' => 7372079283, 'is_bot' => false, 'first_name' => 'Admin'],
        'message' => ['message_id' => 111, 'chat' => ['id' => 7372079283, 'type' => 'private'], 'text' => "New Payment Waiting Approval\nRef: abcdef123"],
        'data' => 'approve::abcdef123'
    ]
];
$payload_json = json_encode($payload);
$opts = ['http'=>['method'=>'POST','header'=>"Content-Type: application/json\r\n",'content'=>$payload_json]];
$context=stream_context_create($opts);
$res = @file_get_contents('https://daren-voltametric-selenographically.ngrok-free.dev/Mekong_CyberUnit/public/api/telegram_callback.php', false, $context);
var_dump($res);
