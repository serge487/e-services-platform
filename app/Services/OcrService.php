<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

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
        $response = Http::attach(
            'file',
            file_get_contents($file->getRealPath()),
            $file->getClientOriginalName()
        )->post('https://api.ocr.space/parse/image', [
            'apikey'      => $this->apiKey,
            'language'    => 'eng',
            'isOverlayRequired' => false,
        ]);

        if (!$response->successful()) {
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
     * Parse extracted text to get name, ID number, DOB
     */
    private function parseIdText(string $text): array
    {
        $result = [
            'raw_text' => $text,
            'name'     => null,
            'id_number'=> null,
            'dob'      => null,
        ];

        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $line = trim($line);

            // Try to find DOB (format: DD/MM/YYYY or DD-MM-YYYY)
            if (preg_match('/\b(\d{2}[\/\-]\d{2}[\/\-]\d{4})\b/', $line, $matches)) {
                $result['dob'] = $matches[1];
            }

            // Try to find ID number (sequence of digits 8-12 long)
            if (preg_match('/\b(\d{8,12})\b/', $line, $matches)) {
                $result['id_number'] = $matches[1];
            }

            // Try to find name (line with mostly uppercase letters)
            if (preg_match('/^[A-Z\s]{5,}$/', $line) && !$result['name']) {
                $result['name'] = ucwords(strtolower($line));
            }
        }

        return $result;
    }
}