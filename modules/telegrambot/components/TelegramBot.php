<?php
namespace app\modules\telegrambot\components;

class TelegramBot
{
    protected $botToken;
    protected $apiUrl;

    public function __construct($botToken)
    {
        $this->botToken = $botToken;
        $this->apiUrl = "https://api.telegram.org/bot{$botToken}/";
    }

    public function sendMessage($chatId, $text)
    {
        $url = $this->apiUrl . "sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($data),
            ],
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }
}
