<div class="p-4">
    @if($valuations->isEmpty())
        <div class="text-center py-8 text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <p class="mt-2 text-sm">Noch keine Bewertungen vorhanden</p>
            <p class="text-xs text-gray-400 mt-1">Nutzen Sie "Wert ermitteln" um eine erste Bewertung zu erstellen</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($valuations as $valuation)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 bg-white dark:bg-gray-800">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ number_format($valuation->estimated_value, 0, ',', '.') }} €
                                </span>
                                <span class="text-xs px-2 py-1 rounded-full {{ $valuation->source === 'manual' ? 'bg-gray-100 text-gray-600' : 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300' }}">
                                    @if($valuation->source === 'manual')
                                        ✍️ Manuell
                                    @elseif($valuation->source === 'perplexity_ai')
                                        🤖 Perplexity AI
                                    @else
                                        🌐 Chrono24 API
                                    @endif
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-sm text-gray-600 dark:text-gray-400">
                                @if($valuation->median_price)
                                    <div>
                                        <span class="font-medium">Median:</span> {{ number_format($valuation->median_price, 0, ',', '.') }} €
                                    </div>
                                @endif
                                @if($valuation->average_price)
                                    <div>
                                        <span class="font-medium">Durchschnitt:</span> {{ number_format($valuation->average_price, 0, ',', '.') }} €
                                    </div>
                                @endif
                                @if($valuation->price_range)
                                    <div class="col-span-2">
                                        <span class="font-medium">Preisspanne:</span> 
                                        {{ number_format($valuation->price_range['min'] ?? 0, 0, ',', '.') }} € - 
                                        {{ number_format($valuation->price_range['max'] ?? 0, 0, ',', '.') }} €
                                    </div>
                                @endif
                                <div>
                                    <span class="font-medium">Angebote:</span> {{ $valuation->comparable_listings }}
                                </div>
                            </div>

                            @if($valuation->notes)
                                <details class="mt-3">
                                    <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700">📋 Details anzeigen</summary>
                                    <pre class="mt-2 text-xs bg-gray-50 dark:bg-gray-900 p-2 rounded overflow-x-auto">{{ $valuation->notes }}</pre>
                                </details>
                            @endif
                        </div>

                        <div class="text-right text-xs text-gray-500 dark:text-gray-400 ml-4">
                            <div>{{ $valuation->valuated_at->format('d.m.Y') }}</div>
                            <div>{{ $valuation->valuated_at->format('H:i') }} Uhr</div>
                            <div class="mt-1 text-gray-400">{{ $valuation->valuated_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
