<?php

declare(strict_types=1);

namespace App\Services;

final class TelegramNotifier
{
    public function __construct(
        private readonly string $botToken,
        private readonly string $defaultChatId
    ) {}

    public function sendMessage(string $text, ?string $chatId = null): bool
    {
        $targetChatId = $chatId ?: $this->defaultChatId;
        if ($this->botToken === '' || $targetChatId === '') {
            return false;
        }

        $url = sprintf('https://api.telegram.org/bot%s/sendMessage', $this->botToken);
        $payload = json_encode([
            'chat_id' => $targetChatId,
            'text' => $text,
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return false;
        }

        $decoded = json_decode((string) $response, true);
        return is_array($decoded) && ($decoded['ok'] ?? false) === true;
    }

    public function notifyDeadline(string $taskTitle, string $deadline, ?string $chatId = null): bool
    {
        return $this->sendMessage(
            "Задача: «{$taskTitle}». Срок исполнения: {$deadline}.",
            $chatId
        );
    }

    public function notifyBudgetLimit(string $campaignName, float $spent, float $limit, ?string $chatId = null): bool
    {
        return $this->sendMessage(
            sprintf(
                "Кампания «%s»: достигнут лимит бюджета (%.2f из %.2f).",
                $campaignName,
                $spent,
                $limit
            ),
            $chatId
        );
    }
}