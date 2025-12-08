<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DocumentScannerService
{
    /**
     * Gemini API Key from .env
     */
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('SCANNER_API_KEY');
    }

    /**
     * Scan document image and extract worker data using Gemini 1.5 Flash
     */
    public function scanDocument($imagePath): array
    {
        // Read image and convert to base64
        $imageData = file_get_contents($imagePath);
        $base64Image = base64_encode($imageData);
        
        // Detect mime type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $imagePath);
        finfo_close($finfo);

        $prompt = 'Проанализируй это фото документа (паспорт или ID карта) и извлеки данные о человеке.

Верни ТОЛЬКО JSON без markdown, без ```json, просто чистый JSON в таком формате:
{
    "first_name": "Имя на латинице, ",
    "last_name": "Фамилия на латинице", 
    "dob": "YYYY-MM-DD",
    "gender": "male или female",
    "nationality": "Страна на английском"
}

Важно:
- Имя и фамилию пиши на латинице с большой буквы, но не всё имя и фамилию большими буквами
- Дату рождения в формате YYYY-MM-DD
- Пол: male для мужчин, female для женщин
- Если поле не найдено, поставь null';

        try {
            $response = Http::timeout(30)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$this->apiKey}",
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data' => $base64Image
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            );

            if (!$response->successful()) {
                return ['error' => 'Gemini API error: ' . $response->body()];
            }

            $result = $response->json();
            
            // Extract text from Gemini response
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
            
            if (!$text) {
                return ['error' => 'No response from Gemini'];
            }

            // Clean up response - remove markdown code blocks if present
            $text = preg_replace('/```json\s*/', '', $text);
            $text = preg_replace('/```\s*/', '', $text);
            $text = trim($text);

            // Parse JSON
            $data = json_decode($text, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'error' => 'Failed to parse Gemini response',
                    'raw_text' => $text
                ];
            }

            return [
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'dob' => $data['dob'] ?? null,
                'gender' => $data['gender'] ?? null,
                'nationality' => $data['nationality'] ?? null,
            ];

        } catch (\Exception $e) {
            return ['error' => 'Error: ' . $e->getMessage()];
        }
    }
}

/*
 * ============================================
 * OLD GOOGLE VISION IMPLEMENTATION (COMMENTED)
 * ============================================
 * 
 * use Google\Cloud\Vision\V1\ImageAnnotatorClient;
 * 
 * protected $credentialsPath;
 * 
 * public function __construct()
 * {
 *     $this->credentialsPath = storage_path('app/google-vision-key.json');
 * }
 * 
 * public function scanDocument($imagePath): array
 * {
 *     $imageAnnotator = new ImageAnnotatorClient([
 *         'credentials' => $this->credentialsPath
 *     ]);
 *     
 *     $image = file_get_contents($imagePath);
 *     $response = $imageAnnotator->textDetection($image);
 *     $texts = $response->getTextAnnotations();
 *     $fullText = $texts[0]->getDescription();
 *     
 *     return $this->parseDocumentText($fullText);
 * }
 * 
 * ... parsing logic was here ...
 */
