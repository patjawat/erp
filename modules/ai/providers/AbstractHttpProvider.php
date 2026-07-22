<?php

declare(strict_types=1);

namespace app\modules\ai\providers;

use RuntimeException;

abstract class AbstractHttpProvider
{
    public int $timeout = 60;

    /**
     * @param array<string, mixed> $payload
     * @param array<int, string> $headers
     * @return array<string, mixed>
     */
    protected function postJson(string $url, array $payload, array $headers = []): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is required for AI provider requests.');
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json'], $headers),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
        ]);

        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new RuntimeException('AI provider request failed: ' . $error);
        }

        $data = json_decode((string) $body, true);
        if (!is_array($data)) {
            throw new RuntimeException('AI provider returned invalid JSON.');
        }

        if ($status >= 400) {
            $message = $data['error']['message'] ?? $data['error'] ?? 'AI provider request failed.';
            $message = is_string($message) ? $message : json_encode($message);
            throw new RuntimeException("AI provider request failed with HTTP status {$status}: {$message}");
        }

        return $data;
    }

    /**
     * @param array<int, string> $headers
     * @return array<string, mixed>
     */
    protected function getJson(string $url, array $headers = []): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is required for AI provider requests.');
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
        ]);

        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new RuntimeException('AI provider request failed: ' . $error);
        }

        $data = json_decode((string) $body, true);
        if (!is_array($data)) {
            throw new RuntimeException('AI provider returned invalid JSON.');
        }

        if ($status >= 400) {
            $message = $data['error']['message'] ?? $data['error'] ?? 'AI provider request failed.';
            throw new RuntimeException(is_string($message) ? $message : json_encode($message));
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<int, string> $headers
     */
    protected function postStream(
        string $url,
        array $payload,
        callable $onLine,
        array $headers = []
    ): void {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('PHP cURL extension is required for AI provider streaming.');
        }

        $buffer = '';
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json'], $headers),
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_WRITEFUNCTION => static function ($curl, string $chunk) use (&$buffer, $onLine): int {
                $buffer .= $chunk;

                while (($position = strpos($buffer, "\n")) !== false) {
                    $line = trim(substr($buffer, 0, $position));
                    $buffer = substr($buffer, $position + 1);

                    if ($line !== '') {
                        $onLine($line);
                    }
                }

                return strlen($chunk);
            },
        ]);

        $ok = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($ok === false) {
            throw new RuntimeException('AI provider stream failed: ' . $error);
        }

        if ($status >= 400) {
            throw new RuntimeException('AI provider stream failed with HTTP status ' . $status . '.');
        }
    }

    /**
     * @param array<string, mixed>|string|null $value
     * @return array<string, mixed>
     */
    protected function decodeArguments(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }
}
