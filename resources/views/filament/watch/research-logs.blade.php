<div class="p-4">
    @if($logs->isEmpty())
        <div class="text-center py-8 text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p class="mt-2 text-sm">Noch keine Research-Logs vorhanden</p>
        </div>
    @else
        <div class="space-y-3 max-h-[600px] overflow-y-auto">
            @foreach($logs as $log)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 bg-white dark:bg-gray-800 {{ $log->success ? 'border-l-4 border-l-green-500' : 'border-l-4 border-l-red-500' }}">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-2">
                            @if($log->success)
                                <span class="text-green-500">✓</span>
                            @else
                                <span class="text-red-500">✗</span>
                            @endif
                            <span class="font-medium text-sm">
                                @if($log->source === 'perplexity_ai')
                                    🤖 Perplexity AI
                                @elseif($log->source === 'python_script')
                                    🐍 Python Calculator
                                @else
                                    🌐 Chrono24 Scraper
                                @endif
                            </span>
                            <span class="text-xs text-gray-500">{{ $log->execution_time }}s</span>
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $log->processed_at->format('d.m.Y H:i:s') }}
                        </div>
                    </div>

                    @if($log->error_message)
                        <div class="text-xs bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 p-2 rounded mb-2">
                            <strong>Fehler:</strong> {{ $log->error_message }}
                        </div>
                    @endif

                    <details class="mt-2">
                        <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">📥 Request</summary>
                        <pre class="mt-1 text-xs bg-gray-50 dark:bg-gray-900 p-2 rounded overflow-x-auto max-h-40">{{ json_encode($log->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>

                    @if($log->response_data)
                        <details class="mt-2">
                            <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">📤 Response</summary>
                            <pre class="mt-1 text-xs bg-gray-50 dark:bg-gray-900 p-2 rounded overflow-x-auto max-h-40">{{ json_encode($log->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>
                    @endif

                    @if($log->processed_result)
                        <details class="mt-2">
                            <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700 dark:hover:text-gray-300">⚙️ Processed Result</summary>
                            <pre class="mt-1 text-xs bg-gray-50 dark:bg-gray-900 p-2 rounded overflow-x-auto max-h-40">{{ json_encode($log->processed_result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </details>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
