<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service: WatchDataFetcher (OpenAI GPT-4o Fallback)
 * 
 * Fallback wenn Perplexity nicht konfiguriert ist
 * Nachteil: Keine aktuelle Web-Suche, nur Training-Daten
 */
class WatchDataFetcher
{
    /**
     * API Key (User-spezifisch oder aus .env)
     */
    protected ?string $apiKey;

    /**
     * API Endpoint
     */
    protected string $endpoint = 'https://api.openai.com/v1/chat/completions';

    /**
     * Model
     */
    protected string $model = 'gpt-4o';

    /**
     * Constructor
     * 
     * @param string|null $userApiKey User-spezifischer API Key (optional)
     */
    public function __construct(?string $userApiKey = null)
    {
        $this->apiKey = $userApiKey ?? config('services.openai.api_key');
    }

    /**
     * Prüfen ob Service konfiguriert ist
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Technische Daten einer Uhr abrufen
     * 
     * @param string|null $brand Markenname (optional - wird automatisch erkannt)
     * @param string $referenceNumber Referenznummer
     * @param string|null $model Modellname (optional)
     * @return array ['success' => bool, 'data' => array, 'raw_response' => array, 'error' => string]
     */
    public function fetchWatchData(?string $brand, string $referenceNumber, ?string $model = null): array
    {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'error' => 'OpenAI API Key nicht konfiguriert',
            ];
        }

        try {
            // Prompt erstellen
            $prompt = $this->buildDataPrompt($brand, $referenceNumber, $model);

            Log::info('OpenAI API Request', [
                'prompt' => $prompt,
                'model' => $this->model,
            ]);

            // API-Aufruf
            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->endpoint, [
                    'model' => $this->model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Du bist ein Experte für Luxusuhren. Antworte immer in sauberem JSON-Format ohne zusätzliche Erklärungen.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.2,
                    'max_tokens' => 2000,
                ]);

            if (!$response->successful()) {
                Log::error('OpenAI API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'API-Fehler: ' . $response->status(),
                ];
            }

            $result = $response->json();

            Log::info('OpenAI API Response', ['result' => $result]);

            // Parse response
            $parsedData = $this->parseResponse($result);

            return [
                'success' => true,
                'data' => $parsedData,
                'raw_response' => $result,
            ];

        } catch (\Exception $e) {
            Log::error('OpenAI Fetch Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Prompt für technische Daten bauen
     */
    protected function buildDataPrompt(?string $brand, string $referenceNumber, ?string $model): string
    {
        $brandText = $brand ? "Marke: {$brand}" : 'Marke: unbekannt (bitte automatisch erkennen)';
        $modelText = $model ? "Modell: {$model}" : '';

        return <<<PROMPT
Gib mir technische Daten für diese Luxusuhr:
{$brandText}
Referenznummer: {$referenceNumber}
{$modelText}

Gib die Daten als JSON zurück mit diesen Feldern (falls dir bekannt):
{
  "brand": "Markenname",
  "model": "Modellname",
  "case_material": "Gehäusematerial",
  "case_diameter": 40.5,
  "case_height": 12.5,
  "bezel_material": "Lünettenmaterial",
  "crystal_type": "Glastyp",
  "water_resistance": "Wasserdichtigkeit",
  "dial_color": "Zifferblattfarbe",
  "dial_numerals": "Zahlen auf Zifferblatt",
  "bracelet_material": "Armbandmaterial",
  "bracelet_color": "Armbandfarbe",
  "clasp_material": "Schließenmaterial",
  "clasp_type": "Schließentyp",
  "movement_type": "Aufzugsart",
  "caliber": "Kaliber",
  "base_caliber": "Basiskaliber",
  "power_reserve": 48,
  "jewels": 31,
  "frequency": "Frequenz",
  "functions": ["Datum", "Chronograph"],
  "gender": "Herrenuhr",
  "description": "Kurze Beschreibung",
  "delivery_scope": "Lieferumfang"
}

WICHTIG: Antworte NUR mit dem JSON-Objekt ohne zusätzliche Erklärungen.
Falls du die Marke nicht kennst basierend auf der Referenznummer, gib null zurück.
PROMPT;
    }

    /**
     * Response parsen
     */
    protected function parseResponse(array $result): array
    {
        $choices = $result['choices'] ?? [];
        if (empty($choices)) {
            return [];
        }

        $content = $choices[0]['message']['content'] ?? '';
        
        // Try to extract JSON from markdown code blocks
        if (preg_match('/```(?:json)?\s*(\{.*?\})\s*```/s', $content, $matches)) {
            $content = $matches[1];
        }

        $data = json_decode($content, true);

        if (!is_array($data)) {
            Log::warning('OpenAI response is not valid JSON', ['content' => $content]);
            return [];
        }

        return $data;
    }
}
