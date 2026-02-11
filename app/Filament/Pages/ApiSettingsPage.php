<?php

namespace App\Filament\Pages;

use App\Models\UserApiSetting;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

/**
 * Filament Page: API-Einstellungen
 * 
 * User kann seine API-Keys für Perplexity, OpenAI und Google konfigurieren
 */
class ApiSettingsPage extends Page
{
    use InteractsWithSchemas;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-key';

    protected string $view = 'filament.pages.api-settings-page';

    protected static ?string $navigationLabel = 'API-Einstellungen';

    protected static ?string $title = 'API-Einstellungen';

    protected static ?int $navigationSort = 99;

    protected static UnitEnum | string | null $navigationGroup = 'Einstellungen';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = auth()->user()->apiSettings;

        $this->fillSchemas([
            'form' => [
                'perplexity_api_key' => $settings?->perplexity_api_key,
                'openai_api_key' => $settings?->openai_api_key,
                'google_search_api_key' => $settings?->google_search_api_key,
                'google_search_engine_id' => $settings?->google_search_engine_id,
            ],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('KI für Datenabfrage & Marktwerte')
                    ->description('Mindestens einen der folgenden API-Keys konfigurieren. **Perplexity** wird bevorzugt (hat Web-Zugriff), OpenAI dient als Fallback.')
                    ->schema([
                        TextInput::make('perplexity_api_key')
                            ->label('Perplexity AI API Key')
                            ->placeholder('pplx-...')
                            ->helperText('🌐 **Empfohlen:** Hat Zugriff auf aktuelle Web-Daten für Marktpreise und technische Details. [API Key beantragen](https://www.perplexity.ai/settings/api)')
                            ->password()
                            ->revealable()
                            ->columnSpanFull(),

                        TextInput::make('openai_api_key')
                            ->label('OpenAI API Key (Fallback)')
                            ->placeholder('sk-...')
                            ->helperText('⚠️ Fallback-Option: Basiert nur auf Training-Daten (keine aktuelle Web-Suche). [API Key erstellen](https://platform.openai.com/api-keys)')
                            ->password()
                            ->revealable()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false),

                Section::make('Google Custom Search für Bildsuche')
                    ->description('Optional: Für automatische Bildsuche wenn Perplexity keine Bilder liefert.')
                    ->schema([
                        TextInput::make('google_search_api_key')
                            ->label('Google Custom Search API Key')
                            ->placeholder('AIza...')
                            ->helperText('[API Key erstellen](https://console.cloud.google.com/apis/credentials)')
                            ->password()
                            ->revealable()
                            ->columnSpanFull(),

                        TextInput::make('google_search_engine_id')
                            ->label('Google Search Engine ID (CX)')
                            ->placeholder('a1b2c3d4e5f6g...')
                            ->helperText('[Search Engine erstellen](https://programmablesearchengine.google.com/)')
                            ->password()
                            ->revealable()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(true),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->getSchemaState('form');

        $settings = auth()->user()->apiSettings()->firstOrNew([
            'user_id' => auth()->id(),
        ]);

        $settings->fill($data);
        $settings->save();

        Notification::make()
            ->success()
            ->title('✅ API-Einstellungen gespeichert')
            ->body('Ihre API-Keys wurden erfolgreich aktualisiert.')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Speichern')
                ->submit('save')
                ->icon('heroicon-o-check'),
        ];
    }
}
