<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LibreTranslateClient
{
    /**
     * Best-effort Arabic → English for display hints (third-party rate limits may apply).
     */
    public function translate(?string $text, string $source = 'ar', string $target = 'en'): ?string
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $base = rtrim((string) config('services.libretranslate_url', 'https://libretranslate.com'), '/');

        if ($base === '') {
            return null;
        }

        try {
            $response = Http::timeout(25)
                ->acceptJson()
                ->post($base.'/translate', [
                    'q'      => $text,
                    'source' => $source,
                    'target' => $target,
                    'format' => 'text',
                ]);

            if ($response->successful()) {
                $out = $response->json('translatedText');

                return is_string($out) && $out !== '' ? $out : null;
            }
        } catch (\Throwable $e) {
            Log::debug('LibreTranslate skipped', ['message' => $e->getMessage()]);
        }

        return null;
    }

    /**
     * @param  array{name?: ?string, father_name?: ?string, place_of_birth?: ?string, raw_text?: ?string}  $parsed
     * @return array<string, mixed>
     */
    public function addEnglishHintFields(array $parsed): array
    {
        if (! config('services.libretranslate_enabled', true)) {
            return $parsed;
        }

        foreach (['name', 'father_name', 'place_of_birth', 'mother_name', 'grandfather_name'] as $key) {
            $val = $parsed[$key] ?? null;
            if (! is_string($val) || trim($val) === '') {
                continue;
            }
            if (! preg_match('/\p{Arabic}/u', $val)) {
                continue;
            }

            $en = $this->translate($val, 'ar', 'en');
            if ($en !== null) {
                $parsed[$key.'_en'] = $en;
            }
        }

        return $parsed;
    }
}
