<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Service: MarketValueCalculator
 * 
 * Berechnet Marktwert aus Preisliste
 * Logik: Median, Max-Preis × Zustandsfaktor
 */
class MarketValueCalculator
{
    /**
     * Zustandsfaktoren für Preisberechnung
     */
    protected array $conditionFactors = [
        'neu' => 1.0,
        'ungetragen' => 0.95,
        'getragen' => 0.9,
        'stark_getragen' => 0.75,
    ];

    /**
     * Marktwert berechnen
     * 
     * @param string $referenceNumber Referenznummer
     * @param string $condition Zustand (neu, ungetragen, getragen, stark_getragen)
     * @param array $prices Array of prices from Perplexity
     * @return array Result with market_value, median, average, etc.
     */
    public function calculateMarketValue(string $referenceNumber, string $condition, array $prices): array
    {
        try {
            if (empty($prices)) {
                return [
                    'success' => false,
                    'error' => 'Keine Preise zum Berechnen vorhanden',
                ];
            }

            Log::info('Calculating market value', [
                'reference_number' => $referenceNumber,
                'condition' => $condition,
                'price_count' => count($prices),
                'raw_prices' => $prices,
            ]);

            // 1. Preise bereinigen (Ausreißer entfernen)
            $cleanPrices = $this->removeOutliers($prices);

            if (empty($cleanPrices)) {
                $cleanPrices = $prices; // Fallback
            }

            // 2. Statistiken berechnen
            $median = $this->calculateMedian($cleanPrices);
            $average = $this->calculateAverage($cleanPrices);
            $maxPrice = max($cleanPrices);
            $minPrice = min($cleanPrices);

            // 3. Zustandsfaktor anwenden
            $conditionFactor = $this->conditionFactors[$condition] ?? 0.9;

            // 4. Marktwert = Höchstpreis × Zustandsfaktor
            $marketValue = round($maxPrice * $conditionFactor, 2);

            $result = [
                'success' => true,
                'market_value' => $marketValue,
                'median' => $median,
                'average' => $average,
                'max_price' => $maxPrice,
                'price_range' => [
                    'min' => $minPrice,
                    'max' => $maxPrice,
                ],
                'comparable_listings' => count($prices),
                'condition' => $condition,
                'condition_factor' => $conditionFactor,
                'all_prices' => $prices,
                'clean_prices' => $cleanPrices,
            ];

            Log::info('Market value calculated', $result);

            return $result;

        } catch (\Exception $e) {
            Log::error('Market value calculation error', [
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
     * Ausreißer entfernen (IQR-Methode)
     * 
     * @param array $prices
     * @return array Bereinigte Preise
     */
    protected function removeOutliers(array $prices): array
    {
        if (count($prices) < 4) {
            return $prices; // Zu wenig Daten
        }

        sort($prices);

        $count = count($prices);
        $q1Index = (int) floor($count * 0.25);
        $q3Index = (int) floor($count * 0.75);

        $q1 = $prices[$q1Index];
        $q3 = $prices[$q3Index];
        $iqr = $q3 - $q1;

        $lowerBound = $q1 - (1.5 * $iqr);
        $upperBound = $q3 + (1.5 * $iqr);

        $cleanPrices = array_filter($prices, function($price) use ($lowerBound, $upperBound) {
            return $price >= $lowerBound && $price <= $upperBound;
        });

        Log::info('Outliers removed', [
            'original_count' => count($prices),
            'clean_count' => count($cleanPrices),
            'q1' => $q1,
            'q3' => $q3,
            'iqr' => $iqr,
            'bounds' => [$lowerBound, $upperBound],
        ]);

        return array_values($cleanPrices);
    }

    /**
     * Median berechnen
     */
    protected function calculateMedian(array $values): float
    {
        sort($values);
        $count = count($values);

        if ($count === 0) {
            return 0;
        }

        $middle = (int) floor($count / 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return $values[$middle];
    }

    /**
     * Durchschnitt berechnen
     */
    protected function calculateAverage(array $values): float
    {
        if (empty($values)) {
            return 0;
        }

        return array_sum($values) / count($values);
    }
}
