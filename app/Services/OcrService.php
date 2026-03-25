<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class OcrService
{
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.ocr_space_api_key');
    }

    /**
     * Extract text from ID image using OCR.space API
     */
    public function extractFromId(UploadedFile $file): array
    {
        if (empty($this->apiKey)) {
            return [];
        }

        $response = Http::attach(
            'file',
            file_get_contents($file->getRealPath()),
            $file->getClientOriginalName()
        )->post('https://api.ocr.space/parse/image', [
            'apikey'             => $this->apiKey,
            'language'           => 'eng',
            'isOverlayRequired'  => false,
        ]);

        if (! $response->successful()) {
            return [];
        }

        $data = $response->json();

        if ($data['IsErroredOnProcessing'] ?? true) {
            return [];
        }

        $extractedText = $data['ParsedResults'][0]['ParsedText'] ?? '';

        return $this->parseIdText($extractedText);
    }

    /**
     * Parse extracted text (Lebanese national ID and similar layouts).
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

        foreach ($lines as $i => $line) {
            $norm = preg_replace('/\s+/', ' ', $line);

            if ($result['dob'] === null && preg_match('/\b(\d{2}[\/\-\.]\d{2}[\/\-\.]\d{4})\b/', $line, $m)) {
                $result['dob'] = $this->normalizeDob($m[1]);
            }

            if ($result['id_number'] === null && preg_match('/\b(\d{8,12})\b/', $line, $m)) {
                $result['id_number'] = $m[1];
            }

            if (preg_match('/^(place|lieu|birth|naiss)\s*(of\s*)?(birth)?\s*[:\-]?\s*(.+)$/iu', $norm, $m)) {
                $val = trim($m[4] ?? '');
                if ($val !== '' && strlen($val) > 2) {
                    $result['place_of_birth'] = Str::title(strtolower($val));
                }
            }

            if (preg_match('/^(father|nom\s*du\s*p[eè]re|father\'?s\s*name)\s*[:\-]?\s*(.+)$/iu', $norm, $m)) {
                $val = trim($m[2] ?? '');
                if ($val !== '' && strlen($val) > 2) {
                    $result['father_name'] = Str::title(strtolower($val));
                }
            }

            if ($result['name'] === null && preg_match('/^[A-Z][A-Z\s\-\']{4,}$/u', $line) && ! preg_match('/^\d/', $line)) {
                $result['name'] = Str::title(strtolower($line));
            }
        }

        if ($result['name'] === null) {
            foreach ($lines as $line) {
                if (preg_match('/^[A-Za-z\s\-\']{5,}$/', $line) && ! str_contains(strtolower($line), 'republic')) {
                    $result['name'] = Str::title(strtolower($line));
                    break;
                }
            }
        }

        return $result;
    }

    private function normalizeDob(string $raw): ?string
    {
        $raw = str_replace(['.', '-'], '/', $raw);

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
