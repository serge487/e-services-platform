<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OcrService
{
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.ocr_space_api_key') ?: null;
    }

    /**
     * Run OCR on an image already stored on the public disk (absolute path).
     */
    public function extractFromImagePath(string $absolutePath, string $originalFilename): array
    {
        if (! is_readable($absolutePath)) {
            return $this->emptyResult('unreadable_file');
        }

        if (empty($this->apiKey)) {
            return $this->emptyResult('missing_api_key');
        }

        // OCR.space rejects PHP booleans in multipart — send "true"/"false" strings.
        // Language must be one 3-letter code or "auto" (not "eng,ara"). "auto" needs OCREngine 2.
        $language = (string) config('services.ocr_space_language', 'auto');

        $response = Http::timeout(60)
            ->attach('file', file_get_contents($absolutePath), $originalFilename)
            ->post('https://api.ocr.space/parse/image', [
                'apikey'               => $this->apiKey,
                'language'             => $language,
                'isOverlayRequired'    => 'false',
                'detectOrientation'    => 'true',
                'scale'                => 'true',
                'OCREngine'            => '2',
            ]);

        if (! $response->successful()) {
            Log::warning('OCR.space HTTP error', ['status' => $response->status()]);

            return $this->emptyResult('http_error');
        }

        $data = $response->json();

        if (($data['IsErroredOnProcessing'] ?? false) === true) {
            $apiErr = $data['ErrorMessage'] ?? $data['ErrorDetails'] ?? null;
            Log::warning('OCR.space processing error', ['message' => $apiErr]);

            return $this->emptyResult('api_error', $this->normalizeOcrApiMessage($apiErr));
        }

        $parsedResults = $data['ParsedResults'] ?? [];
        $extractedText = is_array($parsedResults) && isset($parsedResults[0]['ParsedText'])
            ? (string) $parsedResults[0]['ParsedText']
            : '';

        $parsed = $this->parseIdText($extractedText);
        $parsed = app(LibreTranslateClient::class)->addEnglishHintFields($parsed);
        $parsed['_meta'] = [
            'source'        => 'ocr_space',
            'raw_non_empty' => trim($extractedText) !== '',
        ];

        return $parsed;
    }

    private function emptyResult(string $reason, ?string $apiMessage = null): array
    {
        $base = $this->parseIdText('');

        $base['_meta'] = [
            'source'       => 'none',
            'reason'       => $reason,
            'api_message'  => $apiMessage,
        ];

        return $base;
    }

    /**
     * OCR.space may return ErrorMessage as a string or a list of strings.
     */
    private function normalizeOcrApiMessage(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value !== '' ? $value : null;
        }

        if (is_array($value)) {
            $parts = array_filter(array_map(static function ($item) {
                if (is_string($item) || is_numeric($item)) {
                    return (string) $item;
                }

                return '';
            }, $value));

            return $parts === [] ? null : implode(' ', $parts);
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return null;
    }

    /**
     * Parse extracted text (Lebanese national ID — Latin and Arabic line noise).
     */
    private function parseIdText(string $text): array
    {
        $result = [
            'raw_text'        => $text,
            'name'            => null,
            'id_number'       => null,
            'dob'             => null,
            'place_of_birth'  => null,
            'father_name'     => null,
        ];

        $lines = array_values(array_filter(array_map('trim', explode("\n", $text))));

        foreach ($lines as $line) {
            $norm = preg_replace('/\s+/', ' ', $line);

            if ($result['dob'] === null && preg_match('/\b(\d{4}[\/\-\.]\d{2}[\/\-\.]\d{2})\b/', $line, $m)) {
                $parsed = $this->normalizeDob($m[1]);
                if ($parsed) {
                    $result['dob'] = $parsed;
                }
            }

            if ($result['dob'] === null && preg_match('/\b(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})\b/', $line, $m)) {
                $parsed = $this->normalizeDob($m[1]);
                if ($parsed) {
                    $result['dob'] = $parsed;
                }
            }

            if ($result['id_number'] === null && preg_match('/(?<![0-9])(\d{6,14})(?![0-9])/', $line, $m)) {
                $result['id_number'] = $m[1];
            }

            if (preg_match('/^(place|lieu|birth|naiss|محل|P\.?\s*O\.?\s*B\.?)\s*(of\s*)?(birth|الميلاد)?\s*[:\-]?\s*(.+)$/iu', $norm, $m)) {
                $val = trim($m[4] ?? $m[3] ?? '');
                if ($val !== '' && mb_strlen($val) > 1) {
                    $result['place_of_birth'] = $this->titleLine($val);
                }
            }

            if (preg_match('/^(father|nom\s*du\s*p[eè]re|اسم\s*الأب|father\'?s\s*name)\s*[:\-]?\s*(.+)$/iu', $norm, $m)) {
                $val = trim($m[2] ?? '');
                if ($val !== '' && mb_strlen($val) > 1) {
                    $result['father_name'] = $this->titleLine($val);
                }
            }

            if ($result['name'] === null && preg_match('/^[A-Z][A-Z\s\-\']{4,}$/u', $line) && ! preg_match('/^\d/', $line)) {
                $result['name'] = $this->titleLine($line);
            }
        }

        if ($result['name'] === null) {
            foreach ($lines as $line) {
                if (preg_match('/^[A-Za-z][A-Za-z\s\-\']{4,}$/u', $line) && ! str_contains(strtolower($line), 'republic')) {
                    $result['name'] = $this->titleLine($line);
                    break;
                }
            }
        }

        if (preg_match('/\p{Arabic}/u', $text)) {
            $ar = $this->parseArabicLebaneseIdCard($text);
            foreach (['name', 'id_number', 'dob', 'place_of_birth', 'father_name'] as $k) {
                if (! empty($ar[$k])) {
                    $result[$k] = $ar[$k];
                }
            }
        }

        return $result;
    }

    /**
     * Lebanese ID cards in Arabic: الاسم، الشهرة، اسم الأب، محل الولادة، تاريخ الولادة + Eastern digits.
     */
    private function parseArabicLebaneseIdCard(string $text): array
    {
        $out = [
            'name'            => null,
            'id_number'       => null,
            'dob'             => null,
            'place_of_birth'  => null,
            'father_name'     => null,
        ];

        $given = null;
        $family = null;

        if (preg_match('/الاسم\s*[:：]\s*(.+?)(?=\n|$|\r)/u', $text, $m)) {
            $given = trim($m[1]);
        }

        if (preg_match('/الشهرة\s*[:：]\s*(.+?)(?=\n|$|\r)/u', $text, $m)) {
            $family = trim($m[1]);
        }

        if ($given !== null && $family !== null && $given !== '' && $family !== '') {
            $out['name'] = $given.' '.$family;
        } elseif ($given !== null && $given !== '') {
            $out['name'] = $given;
        } elseif ($family !== null && $family !== '') {
            $out['name'] = $family;
        }

        if (preg_match('/اسم\s*(?:الاب|الأب)\s*[:：]\s*(.+?)(?=\n|$|\r)/u', $text, $m)) {
            $out['father_name'] = trim($m[1]);
        }

        if (preg_match('/محل\s*الولادة\s*[:：]\s*(.+?)(?=\n|$|\r)/u', $text, $m)) {
            $out['place_of_birth'] = trim($m[1]);
        }

        if (preg_match('/تاريخ\s*الولادة\s*[:：]\s*(.+?)(?=\n|$|\r)/u', $text, $m)) {
            $d = $this->normalizeEasternArabicDigits(trim($m[1]));
            $parsed = $this->normalizeDob($d);
            if ($parsed) {
                $out['dob'] = $parsed;
            }
        }

        $lines = array_values(array_filter(array_map('trim', explode("\n", $text))));
        foreach ($lines as $line) {
            if ($out['id_number'] !== null) {
                break;
            }
            $norm = preg_replace('/\s+/', '', $this->normalizeEasternArabicDigits($line));
            if ($norm === '' || preg_match('/\d{4}[^\d]\d{2}[^\d]\d{2}/', $norm)) {
                continue;
            }
            if (preg_match('/^(\d{8,14})$/', $norm, $m)) {
                $out['id_number'] = $m[1];
            }
        }

        return $out;
    }

    private function normalizeEasternArabicDigits(string $s): string
    {
        $from = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩', '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $to = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($from, $to, $s);
    }

    private function titleLine(string $line): string
    {
        if (preg_match('/\p{Arabic}/u', $line)) {
            return trim($line);
        }

        return Str::title(Str::lower($line));
    }

    private function normalizeDob(string $raw): ?string
    {
        $raw = str_replace(['.', '-'], '/', trim($raw));

        if (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $raw)) {
            try {
                return Carbon::createFromFormat('Y/m/d', $raw)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $raw)->format('Y-m-d');
        } catch (\Throwable) {
            try {
                return Carbon::parse($raw)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
    }
}
