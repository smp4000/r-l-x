<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service: PerplexityWatchFetcher
 * 
 * Nutzt Perplexity AI API für Web-Suche nach Uhrendaten
 * Vorteil: Hat Zugriff auf aktuelle Web-Daten
 */
class PerplexityWatchFetcher
{
    /**
     * API Key (User-spezifisch oder aus .env)
     */
    protected ?string $apiKey;

    /**
     * API Endpoint
     */
    protected string $endpoint = 'https://api.perplexity.ai/chat/completions';

    /**
     * Model
     */
    protected string $model = 'llama-3.1-sonar-large-128k-online';

    /**
     * Constructor
     * 
     * @param string|null $userApiKey User-spezifischer API Key (optional)
     */
    public function __construct(?string $userApiKey = null)
    {
        $this->apiKey = $userApiKey ?? config('services.perplexity.api_key');
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
                'error' => 'Perplexity API Key nicht konfiguriert',
            ];
        }

        try {
            // Prompt erstellen
            $prompt = $this->buildDataPrompt($brand, $referenceNumber, $model);

            Log::info('Perplexity API Request', [
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
                    'return_images' => true,
                    'return_related_questions' => false,
                    'search_recency_filter' => 'year',
                    'temperature' => 0.2,
                    'top_p' => 0.9,
                    'max_tokens' => 2000,
                ]);

            if (!$response->successful()) {
                Log::error('Perplexity API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'API-Fehler: ' . $response->status(),
                ];
            }

            $result = $response->json();

            Log::info('Perplexity API Response', ['result' => $result]);

            // Parse response
            $parsedData = $this->parseResponse($result);

            return [
                'success' => true,
                'data' => $parsedData,
                'raw_response' => $result,
            ];

        } catch (\Exception $e) {
            Log::error('Perplexity Fetch Error', [
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
     * Marktpreise einer Uhr abrufen
     * 
     * @param string $referenceNumber Referenznummer
     * @param string|null $model Modell
     * @param string $condition Zustand (neu, ungetragen, getragen, stark_getragen)
     * @return array Array of prices [12000, 13500, 14000, ...]
     */
    public function fetchMarketPrices(string $referenceNumber, ?string $model, string $condition): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Perplexity API Key nicht konfiguriert');
        }

        try {
            // Prompt für Preisermittlung
            $prompt = $this->buildPricePrompt($referenceNumber, $model, $condition);

            Log::info('Perplexity Market Prices Request', [
                'prompt' => $prompt,
            ]);

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
                            'content' => 'Du bist ein Experte für Luxusuhren-Marktpreise. Antworte NUR mit einem JSON-Array von Euro-Preisen ohne zusätzliche Erklärungen. Format: [12000, 13500, 14000]',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'return_images' => false,
                    'return_related_questions' => false,
                    'search_recency_filter' => 'week',
                    'temperature' => 0.1,
                    'top_p' => 0.9,
                    'max_tokens' => 1000,
                ]);

            if (!$response->successful()) {
                Log::error('Perplexity Price API Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('API-Fehler: ' . $response->status());
            }

            $result = $response->json();
            
            Log::info('Perplexity Market Prices Response', ['result' => $result]);

            // Parse prices from response
            $prices = $this->parsePricesResponse($result);

            return $prices;

        } catch (\Exception $e) {
            Log::error('Perplexity Market Prices Error', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
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
Suche im Web nach technischen Daten für diese Luxusuhr:
{$brandText}
Referenznummer: {$referenceNumber}
{$modelText}

Gib die Daten als JSON zurück mit diesen Feldern (falls vorhanden):
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
  "delivery_scope": "Lieferumfang",
  "image_urls": ["https://...jpg", "https://...jpg"]
}

WICHTIG: Antworte NUR mit dem JSON-Objekt ohne zusätzliche Erklärungen.
PROMPT;
    }

    /**
     * Prompt für Marktpreise bauen
     */
    protected function buildPricePrompt(string $referenceNumber, ?string $model, string $condition): string
    {
        $modelText = $model ? " ({$model})" : '';
        $conditionMap = [
            'neu' => 'neu/ungetragen',
            'ungetragen' => 'ungetragen',
            'getragen' => 'getragen/good condition',
            'stark_getragen' => 'stark getragen/heavily used',
        ];
        $conditionText = $conditionMap[$condition] ?? 'getragen';

        return <<<PROMPT
Suche auf Chrono24, WatchCharts, Watchbase und anderen Luxusuhren-Marktplätzen nach aktuellen Verkaufspreisen für:
Referenznummer: {$referenceNumber}{$modelText}
Zustand: {$conditionText}

Gib NUR ein JSON-Array mit den gefundenen Preisen in Euro zurück (8-15 Preise).
Format: [12000, 13500, 14200, 15000, 13800, 14500, 15200, 13900]

WICHTIG: Nur das Array, keine Erklärungen.
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
            Log::warning('Perplexity response is not valid JSON', ['content' => $content]);
            return [];
        }

        return $data;
    }

    /**
     * Preise aus Response parsen
     */
    protected function parsePricesResponse(array $result): array
    {
        $choices = $result['choices'] ?? [];
        if (empty($choices)) {
            return [];
        }

        $content = $choices[0]['message']['content'] ?? '';
        
        // Try to extract JSON array from markdown code blocks
        if (preg_match('/```(?:json)?\s*(\[.*?\])\s*```/s', $content, $matches)) {
            $content = $matches[1];
        }

        $prices = json_decode($content, true);

        if (!is_array($prices)) {
            Log::warning('Perplexity prices response is not valid JSON array', ['content' => $content]);
            return [];
        }

        // Filter out non-numeric values
        return array_values(array_filter($prices, fn($price) => is_numeric($price) && $price > 0));
    }
}
