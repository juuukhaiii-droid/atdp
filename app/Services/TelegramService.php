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
        $this->token = config('services.telegram.bot_token');
        $this->chatId = config('services.telegram.chat_id');
    }

    public function send($message)
    {
        if (!$this->token || !$this->chatId) {
            Log::warning('Telegram attendance alert skipped: bot token or chat ID is not configured.');
            return null;
        }

        try {
            $response = Http::connectTimeout(3)->timeout(5)->post(
                "https://api.telegram.org/bot{$this->token}/sendMessage",
                [
                    'chat_id' => $this->chatId,
                    'text' => $message,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ]
            );

            if (!$response->successful() || $response->json('ok') !== true) {
                Log::warning('Telegram attendance alert rejected.', [
                    'http_status' => $response->status(),
                    'telegram_error_code' => $response->json('error_code'),
                ]);
            }

            return $response;
        } catch (\Exception $e) {
            // HTTP exception messages can contain the bot token in the request URL.
            Log::error('Telegram attendance alert could not be delivered.', ['exception' => get_class($e)]);
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
     * Send a photo with a caption
     */
    public function sendPhoto($filePath, $caption = '')
    {
        try {
            return Http::attach(
                'photo',
                fopen($filePath, 'r')
            )->post(
                "https://api.telegram.org/bot{$this->token}/sendPhoto",
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
