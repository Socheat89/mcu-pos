<?php
// core/classes/TelegramBot.php

class TelegramBot {
    private $token;
    private $chatId;

    public function __construct() {
        $config = require __DIR__ . '/../../config/telegram.php';
        $this->token = $config['bot_token'];
        $this->chatId = $config['chat_id'];
    }

    public function sendMessage($message, $keyboard = null) {
        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";
        $data = [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];

        if ($keyboard) {
            $data['reply_markup'] = json_encode($keyboard);
        }

        return $this->post($url, $data);
    }

    private function post($url, $data) {
        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'ignore_errors' => true
            ]
        ];
        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);
        return $result ? json_decode($result, true) : null;
    }
}
