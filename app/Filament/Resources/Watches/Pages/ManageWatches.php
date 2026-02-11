<?php

namespace App\Filament\Resources\Watches\Pages;

use App\Filament\Resources\Watches\WatchResource;
use App\Models\WatchImage;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;

class ManageWatches extends ManageRecords
{
    protected static string $resource = WatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Neue Uhr')
                ->icon('heroicon-o-plus')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['user_id'] = auth()->id();
                    return $data;
                })
                ->after(function (Model $record, array $data) {
                    // Bilder aus ai_fetched_data verarbeiten, falls vorhanden
                    if (!empty($data['ai_fetched_data']['downloaded_images'])) {
                        foreach ($data['ai_fetched_data']['downloaded_images'] as $index => $imagePath) {
                            WatchImage::create([
                                'watch_id' => $record->id,
                                'filename' => basename($imagePath),
                                'path' => $imagePath,
                                'source' => 'ai_fetched',
                                'is_primary' => $index === 0, // Erstes Bild wird Hauptbild
                            ]);
                        }
                    }
                }),
        ];
    }
}
