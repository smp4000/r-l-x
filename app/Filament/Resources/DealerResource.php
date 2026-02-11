<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DealerResource\Pages;
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
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Filament Resource: Dealer (Kontakte: Händler, Juweliere, Privatpersonen)
 * 
 * CRM für Kauf- und Verkaufskontakte
 */
class DealerResource extends Resource
{
    protected static ?string $model = Dealer::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationLabel = 'Kontakte';

    protected static ?string $modelLabel = 'Kontakt';

    protected static ?string $pluralModelLabel = 'Kontakte';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Schemas\Components\Section::make('Kontaktinformationen')
                    ->schema([
                        Forms\Components\TextInput::make('company_name')
                            ->label('Firmenname')
                            ->placeholder('z.B. Chronext AG')
                            ->prefixIcon('heroicon-o-building-office-2')
                            ->reactive()
                            ->afterStateUpdated(fn ($state, $set) => $state ? $set('name', $state) : null)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('firstname')
                            ->label('Vorname')
                            ->placeholder('Max')
                            ->prefixIcon('heroicon-o-user')
                            ->visible(fn ($get) => empty($get('company_name')))
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('lastname')
                            ->label('Nachname')
                            ->placeholder('Mustermann')
                            ->prefixIcon('heroicon-o-user')
                            ->required(fn ($get) => empty($get('company_name')))
                            ->reactive()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                if (!$get('company_name')) {
                                    $firstname = $get('firstname');
                                    $set('name', trim(($firstname ? $firstname . ' ' : '') . $state));
                                }
                            })
                            ->visible(fn ($get) => empty($get('company_name')))
                            ->columnSpan(1),

                        Forms\Components\Hidden::make('name'),

                        Forms\Components\TextInput::make('email')
                            ->label('E-Mail')
                            ->placeholder('info@beispiel.de')
                            ->email()
                            ->prefixIcon('heroicon-o-envelope')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->placeholder('+49 123 4567890')
                            ->tel()
                            ->prefixIcon('heroicon-o-phone')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Schemas\Components\Section::make('Adresse')
                    ->schema([
                        Forms\Components\TextInput::make('street')
                            ->label('Straße')
                            ->placeholder('Musterstraße')
                            ->prefixIcon('heroicon-o-map')
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('street_number')
                            ->label('Nr.')
                            ->placeholder('123')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('zip')
                            ->label('PLZ')
                            ->placeholder('12345')
                            ->prefixIcon('heroicon-o-map-pin')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('city')
                            ->label('Stadt')
                            ->placeholder('Berlin')
                            ->prefixIcon('heroicon-o-building-office')
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('country')
                            ->label('Land')
                            ->default('Deutschland')
                            ->prefixIcon('heroicon-o-flag')
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Schemas\Components\Section::make('Typ & Eigenschaften')
                    ->schema([
                        Forms\Components\Select::make('contact_type')
                            ->label('Kontakttyp')
                            ->options([
                                'dealer' => '🏪 Händler',
                                'jeweler' => '💎 Juwelier',
                                'private_person' => '👤 Privatperson',
                            ])
                            ->default('dealer')
                            ->required()
                            ->native(false)
                            ->columnSpan(2),

                        Forms\Components\Toggle::make('is_buyer')
                            ->label('Kauft Uhren an')
                            ->helperText('Dieser Kontakt kauft Uhren an')
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_seller')
                            ->label('Verkauft Uhren')
                            ->helperText('Dieser Kontakt verkauft Uhren')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Schemas\Components\Section::make('CRM')
                    ->schema([
                        Forms\Components\TagsInput::make('tags')
                            ->label('Tags')
                            ->placeholder('Tag hinzufügen...')
                            ->helperText('z.B. "Vertrauenswürdig", "Schnelle Abwicklung", "Gute Preise"')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notizen')
                            ->rows(4)
                            ->placeholder('Zusätzliche Informationen zu diesem Kontakt...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Dealer $record): string => $record->company_name ? 'Firma' : 'Privat'),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-Mail')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('contact_type')
                    ->label('Typ')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'dealer' => '🏪 Händler',
                        'jeweler' => '💎 Juwelier',
                        'private_person' => '👤 Privat',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'dealer' => 'success',
                        'jeweler' => 'warning',
                        'private_person' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('city')
                    ->label('Stadt')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('purchases_count')
                    ->label('Gekauft')
                    ->counts('purchases')
                    ->badge()
                    ->color('primary')
                    ->tooltip('Anzahl gekaufter Uhren'),

                Tables\Columns\TextColumn::make('sales_count')
                    ->label('Verkauft')
                    ->counts('sales')
                    ->badge()
                    ->color('success')
                    ->tooltip('Anzahl verkaufter Uhren'),

                Tables\Columns\IconColumn::make('is_buyer')
                    ->label('Käufer')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_seller')
                    ->label('Verkäufer')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('contact_type')
                    ->label('Kontakttyp')
                    ->options([
                        'dealer' => '🏪 Händler',
                        'jeweler' => '💎 Juwelier',
                        'private_person' => '👤 Privatperson',
                    ]),

                Tables\Filters\TernaryFilter::make('is_buyer')
                    ->label('Nur Käufer')
                    ->placeholder('Alle')
                    ->trueLabel('Ja')
                    ->falseLabel('Nein'),

                Tables\Filters\TernaryFilter::make('is_seller')
                    ->label('Nur Verkäufer')
                    ->placeholder('Alle')
                    ->trueLabel('Ja')
                    ->falseLabel('Nein'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->recordActions([
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
            ])
            ->toolbarActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDealers::route('/'),
            'create' => Pages\CreateDealer::route('/create'),
            'edit' => Pages\EditDealer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id())
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }
}
