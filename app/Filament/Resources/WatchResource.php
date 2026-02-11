<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WatchResource\Pages;
use App\Models\Watch;
use App\Models\Brand;
use App\Models\Dealer;
use BackedEnum;
use Filament\Actions;
use Filament\Forms;
use Filament\Schemas;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filament Resource: Watch (Uhr)
 * 
 * Hauptverwaltung für Uhren-Sammlung mit allen Details
 */
class WatchResource extends Resource
{
    protected static ?string $model = Watch::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Uhren';

    protected static ?string $modelLabel = 'Uhr';

    protected static ?string $pluralModelLabel = 'Uhren';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Schemas\Components\Tabs::make('Uhr Details')
                    ->tabs([
                        // TAB 1: Grunddaten
                        Schemas\Components\Tabs\Tab::make('Grunddaten')
                            ->schema([
                                Forms\Components\Select::make('brand_id')
                                    ->label('Marke')
                                    ->relationship('brand', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Markenname')
                                            ->required(),
                                    ]),

                                Forms\Components\TextInput::make('model')
                                    ->label('Modell')
                                    ->required()
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('reference_number')
                                    ->label('Referenznummer')
                                    ->maxLength(255),

                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'in_besitz' => 'Im Besitz',
                                        'wunschliste' => 'Wunschliste',
                                        'verkauft' => 'Verkauft',
                                    ])
                                    ->default('in_besitz')
                                    ->required(),

                                Forms\Components\Select::make('condition')
                                    ->label('Zustand')
                                    ->options([
                                        'neu' => 'Neu',
                                        'neuwertig' => 'Neuwertig',
                                        'sehr_gut' => 'Sehr gut',
                                        'gut' => 'Gut',
                                        'getragen' => 'Getragen',
                                        'restauriert' => 'Restauriert',
                                        'vintage' => 'Vintage',
                                    ]),

                                Forms\Components\Select::make('condition')
                                    ->label('Zustand')
                                    ->options([
                                        'neu' => 'Neu',
                                        'ungetragen' => 'Ungetragen',
                                        'getragen' => 'Getragen',
                                        'stark_getragen' => 'Stark getragen',
                                    ])
                                    ->default('getragen')
                                    ->required(),
                            ]),

                        // TAB 2: Preise & Werte
                        Schemas\Components\Tabs\Tab::make('Preise')
                            ->schema([
                                Forms\Components\TextInput::make('purchase_price')
                                    ->label('Kaufpreis (€)')
                                    ->numeric()
                                    ->prefix('€'),

                                Forms\Components\DatePicker::make('purchase_date')
                                    ->label('Kaufdatum'),

                                Forms\Components\Select::make('purchase_dealer_id')
                                    ->label('Gekauft bei')
                                    ->relationship('purchaseDealer', 'company_name')
                                    ->searchable()
                                    ->preload(),

                                Forms\Components\TextInput::make('selling_price')
                                    ->label('Verkaufspreis (€)')
                                    ->numeric()
                                    ->prefix('€')
                                    ->visible(fn ($get) => $get('status') === 'verkauft'),

                                Forms\Components\DatePicker::make('selling_date')
                                    ->label('Verkaufsdatum')
                                    ->visible(fn ($get) => $get('status') === 'verkauft'),

                                Forms\Components\Select::make('selling_dealer_id')
                                    ->label('Verkauft an')
                                    ->relationship('sellingDealer', 'company_name')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn ($get) => $get('status') === 'verkauft'),

                                Forms\Components\TextInput::make('current_market_value')
                                    ->label('Aktueller Marktwert (€)')
                                    ->numeric()
                                    ->prefix('€')
                                    ->disabled(),
                            ]),

                        // TAB 3: Technische Details
                        Schemas\Components\Tabs\Tab::make('Technische Details')
                            ->schema([
                                Forms\Components\Select::make('case_material')
                                    ->label('Gehäusematerial')
                                    ->options([
                                        'edelstahl' => 'Edelstahl',
                                        'gold_18k' => 'Gold 18K',
                                        'platin' => 'Platin',
                                        'titan' => 'Titan',
                                        'keramik' => 'Keramik',
                                        'roségold' => 'Roségold',
                                        'weissgold' => 'Weißgold',
                                        'gelbgold' => 'Gelbgold',
                                    ])
                                    ->searchable(),

                                Forms\Components\TextInput::make('case_diameter')
                                    ->label('Gehäusedurchmesser')
                                    ->maxLength(50),

                                Forms\Components\Select::make('movement_type')
                                    ->label('Uhrwerk')
                                    ->options([
                                        'automatik' => 'Automatik',
                                        'handaufzug' => 'Handaufzug',
                                        'quarz' => 'Quarz',
                                    ])
                                    ->searchable(),

                                Forms\Components\TextInput::make('caliber')
                                    ->label('Kaliber')
                                    ->maxLength(100),

                                Forms\Components\Select::make('dial_color')
                                    ->label('Zifferblattfarbe')
                                    ->options([
                                        'schwarz' => 'Schwarz',
                                        'weiss' => 'Weiß',
                                        'blau' => 'Blau',
                                        'grün' => 'Grün',
                                        'silber' => 'Silber',
                                        'grau' => 'Grau',
                                        'braun' => 'Braun',
                                        'champagner' => 'Champagner',
                                    ])
                                    ->searchable(),

                                Forms\Components\TextInput::make('bracelet_material')
                                    ->label('Armbandmaterial')
                                    ->maxLength(100),

                                Forms\Components\TextInput::make('water_resistance')
                                    ->label('Wasserdichtigkeit')
                                    ->maxLength(50),
                            ]),

                        // TAB 4: Dokumentation
                        Schemas\Components\Tabs\Tab::make('Dokumentation')
                            ->schema([
                                Forms\Components\Toggle::make('has_box')
                                    ->label('Box vorhanden'),

                                Forms\Components\Toggle::make('has_papers')
                                    ->label('Papiere vorhanden'),

                                Forms\Components\TextInput::make('serial_number')
                                    ->label('Seriennummer')
                                    ->maxLength(255),

                                Forms\Components\TextInput::make('warranty_until')
                                    ->label('Garantie bis (Jahr)')
                                    ->numeric(),
                            ]),

                        // TAB 5: Notizen
                        Schemas\Components\Tabs\Tab::make('Notizen')
                            ->schema([
                                Forms\Components\Textarea::make('notes')
                                    ->label('Notizen')
                                    ->maxLength(2000)
                                    ->rows(5),

                                Forms\Components\Textarea::make('description')
                                    ->label('Beschreibung (AI generiert)')
                                    ->maxLength(2000)
                                    ->rows(5)
                                    ->disabled(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Bild')
                    ->circular()
                    ->defaultImageUrl(url('/images/watch-placeholder.png')),

                Tables\Columns\TextColumn::make('brand.name')
                    ->label('Marke')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('model')
                    ->label('Modell')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reference_number')
                    ->label('Referenz')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'in_besitz',
                        'warning' => 'wunschliste',
                        'danger' => 'verkauft',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'in_besitz' => 'Im Besitz',
                        'wunschliste' => 'Wunschliste',
                        'verkauft' => 'Verkauft',
                    }),

                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Kaufpreis')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_market_value')
                    ->label('Marktwert')
                    ->money('EUR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('year')
                    ->label('Jahr')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'in_besitz' => 'Im Besitz',
                        'wunschliste' => 'Wunschliste',
                        'verkauft' => 'Verkauft',
                    ]),

                Tables\Filters\SelectFilter::make('brand_id')
                    ->label('Marke')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWatches::route('/'),
            'create' => Pages\CreateWatch::route('/create'),
            'edit' => Pages\EditWatch::route('/{record}/edit'),
        ];
    }
}
