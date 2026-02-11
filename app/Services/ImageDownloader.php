<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service: ImageDownloader
 * 
 * Sucht und läd Bilder von Uhren herunter
 * - Google Custom Search API
 * - Webseiten-Scraping von Perplexity Sources
 */
class ImageDownloader
{
    /**
     * API Keys
     */
    protected ?string $googleApiKey;
    protected ?string $googleEngineId;

    /**
     * Constructor
     */
    public function __construct(?string $userApiKey = null, ?string $userEngineId = null)
    {
        $this->googleApiKey = $userApiKey ?? config('services.google.search_api_key');
        $this->googleEngineId = $userEngineId ?? config('services.google.search_engine_id');
    }

    /**
     * Prüfen ob Google Custom Search konfiguriert ist
     */
    public function isConfigured(): bool
    {
        return !empty($this->googleApiKey) && !empty($this->googleEngineId);
    }

    /**
     * Bilder via Google Custom Search suchen
     * 
     * @param string $query Suchbegriff
     * @param int $limit Anzahl Ergebnisse
     * @return array Array of image URLs
     */
    public function searchImages(string $query, int $limit = 5): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Google Custom Search nicht konfiguriert');
        }

        try {
            Log::info('Google Image Search', ['query' => $query, 'limit' => $limit]);

            $response = Http::timeout(30)->get('https://www.googleapis.com/customsearch/v1', [
                'key' => $this->googleApiKey,
                'cx' => $this->googleEngineId,
                'q' => $query,
                'searchType' => 'image',
                'num' => min($limit, 10), // Max 10 per request
                'imgSize' => 'large',
                'safe' => 'off',
            ]);

            if (!$response->successful()) {
                Log::error('Google Image Search Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \Exception('Google API Fehler: ' . $response->status());
            }

            $result = $response->json();
            $items = $result['items'] ?? [];

            $imageUrls = [];
            foreach ($items as $item) {
                if (isset($item['link'])) {
                    $imageUrls[] = $item['link'];
                }
            }

            Log::info('Google Image Search Results', ['count' => count($imageUrls)]);

            return $imageUrls;

        } catch (\Exception $e) {
            Log::error('Image Search Error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Bilder von Webseiten extrahieren (Scraping)
     * 
     * @param array $sourceUrls Array of URLs from Perplexity sources
     * @param string $brand Markenname
     * @param string $model Modellname
     * @return array Array of ['url' => '...', 'source_url' => '...', 'alt' => '...', 'estimated_size' => 'large|medium|small']
     */
    public function extractImagesFromUrls(array $sourceUrls, string $brand, string $model): array
    {
        $foundImages = [];

        foreach (array_slice($sourceUrls, 0, 5) as $sourceUrl) {
            try {
                Log::info('Extracting images from URL', ['url' => $sourceUrl]);

                $response = Http::timeout(15)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    ])
                    ->get($sourceUrl);

                if (!$response->successful()) {
                    Log::warning('Failed to fetch URL', ['url' => $sourceUrl, 'status' => $response->status()]);
                    continue;
                }

                $html = $response->body();

                // Extract images using regex (simplified)
                preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches);

                if (empty($matches[1])) {
                    continue;
                }

                foreach ($matches[1] as $imageUrl) {
                    // Make absolute URL
                    if (str_starts_with($imageUrl, '//')) {
                        $imageUrl = 'https:' . $imageUrl;
                    } elseif (str_starts_with($imageUrl, '/')) {
                        $parsed = parse_url($sourceUrl);
                        $imageUrl = $parsed['scheme'] . '://' . $parsed['host'] . $imageUrl;
                    }

                    // Filter: Only actual image URLs
                    if (!preg_match('/\.(jpg|jpeg|png|webp)(\?|$)/i', $imageUrl)) {
                        continue;
                    }

                    // Filter: Skip tiny images
                    if (str_contains($imageUrl, 'icon') || str_contains($imageUrl, 'logo') || str_contains($imageUrl, 'thumb')) {
                        continue;
                    }

                    // Estimate size based on URL patterns
                    $estimatedSize = 'medium';
                    if (str_contains($imageUrl, 'large') || str_contains($imageUrl, 'original') || str_contains($imageUrl, '1200') || str_contains($imageUrl, '1920')) {
                        $estimatedSize = 'large';
                    } elseif (str_contains($imageUrl, 'small') || str_contains($imageUrl, 'thumb') || str_contains($imageUrl, '150') || str_contains($imageUrl, '200')) {
                        $estimatedSize = 'small';
                    }

                    $foundImages[] = [
                        'url' => $imageUrl,
                        'source_url' => $sourceUrl,
                        'alt' => '',
                        'estimated_size' => $estimatedSize,
                    ];

                    // Limit per source
                    if (count($foundImages) >= 15) {
                        break 2;
                    }
                }

            } catch (\Exception $e) {
                Log::warning('Image extraction failed for URL', [
                    'url' => $sourceUrl,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Total images extracted', ['count' => count($foundImages)]);

        // Prioritize large images
        usort($foundImages, function($a, $b) {
            $sizeOrder = ['large' => 3, 'medium' => 2, 'small' => 1];
            return ($sizeOrder[$b['estimated_size']] ?? 0) <=> ($sizeOrder[$a['estimated_size']] ?? 0);
        });

        return $foundImages;
    }

    /**
     * Ausgewählte Bilder herunterladen
     * 
     * @param array $images Array of image data from extractImagesFromUrls
     * @param string $brand Markenname
     * @param string $refNumber Referenznummer
     * @return array Array of ['filename' => '...', 'path' => '...']
     */
    public function downloadSelectedImages(array $images, string $brand, string $refNumber): array
    {
        $downloaded = [];

        foreach ($images as $index => $imageData) {
            try {
                $imageUrl = $imageData['url'];

                // Download image
                $response = Http::timeout(15)->get($imageUrl);

                if (!$response->successful()) {
                    Log::warning('Image download failed', ['url' => $imageUrl]);
                    continue;
                }

                // Validate Content-Type
                $contentType = $response->header('Content-Type');
                if (!str_contains($contentType, 'image/')) {
                    Log::warning('Invalid content type', ['url' => $imageUrl, 'type' => $contentType]);
                    continue;
                }

                // Get extension
                $extension = 'jpg';
                if (str_contains($contentType, 'png')) {
                    $extension = 'png';
                } elseif (str_contains($contentType, 'webp')) {
                    $extension = 'webp';
                }

                // Generate filename
                $filename = Str::slug($brand) . '-' . $refNumber . '-' . ($index + 1) . '.' . $extension;
                $path = 'watch-images/' . $filename;

                // Save to storage
                Storage::disk('public')->put($path, $response->body());

                $downloaded[] = [
                    'filename' => $filename,
                    'path' => $path,
                ];

                Log::info('Image downloaded', ['path' => $path]);

            } catch (\Exception $e) {
                Log::error('Image download error', [
                    'url' => $imageData['url'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $downloaded;
    }
}
