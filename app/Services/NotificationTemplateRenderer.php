<?php

namespace App\Services;

use Illuminate\Support\Arr;

class NotificationTemplateRenderer
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function render(?string $subject, string $body, array $payload = []): array
    {
        $flatPayload = Arr::dot($payload);

        return [
            'subject' => $subject ? $this->replacePlaceholders($subject, $flatPayload) : null,
            'body' => $this->replacePlaceholders($body, $flatPayload),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function replacePlaceholders(string $template, array $payload): string
    {
        return (string) preg_replace_callback('/{{\s*([^}]+)\s*}}/', function (array $matches) use ($payload): string {
            $key = trim($matches[1]);
            $value = $payload[$key] ?? null;

            if (is_array($value)) {
                return json_encode($value, JSON_UNESCAPED_SLASHES) ?: '';
            }

            return $value === null ? '' : (string) $value;
        }, $template);
    }
}
