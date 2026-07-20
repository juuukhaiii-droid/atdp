<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $token;
    protected $chatId;

    public function __construct()
    {
        $this->token = env('TELEGRAM_BOT_TOKEN');
        $this->chatId = env('TELEGRAM_CHAT_ID');
    }

    /**
     * Send a simple text message
     */
    public function send($message)
    {
        try {
            return Http::post(
                "https://api.telegram.org/bot{$this->token}/sendMessage",
                [
                    'chat_id' => $this->chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Telegram Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send a message with inline buttons
     */
    public function sendWithButtons($message, $buttons = [])
    {
        try {
            $payload = [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ];

            if (!empty($buttons)) {
                $payload['reply_markup'] = json_encode(['inline_keyboard' => $buttons]);
            }

            return Http::post(
                "https://api.telegram.org/bot{$this->token}/sendMessage",
                $payload
            );
        } catch (\Exception $e) {
            Log::error('Telegram Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send a document/file
     */
    public function sendDocument($filePath, $caption = '')
    {
        try {
            return Http::attach(
                'document',
                fopen($filePath, 'r')
            )->post(
                "https://api.telegram.org/bot{$this->token}/sendDocument",
                [
                    'chat_id' => $this->chatId,
                    'caption' => $caption,
                    'parse_mode' => 'HTML',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Telegram Service Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Edit an existing message
     */
    public function editMessage($messageId, $newText)
    {
        try {
            return Http::post(
                "https://api.telegram.org/bot{$this->token}/editMessageText",
                [
                    'chat_id' => $this->chatId,
                    'message_id' => $messageId,
                    'text' => $newText,
                    'parse_mode' => 'HTML',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Telegram Service Error: ' . $e->getMessage());
            return null;
        }
    }
}
