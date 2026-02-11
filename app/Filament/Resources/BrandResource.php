<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
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
use UnitEnum;

/**
 * Filament Resource: Brand (Marke)
 * 
 * Verwaltung von Uhrenmarken
 */
class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Marken';

    protected static ?string $modelLabel = 'Marke';

    protected static ?string $pluralModelLabel = 'Marken';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Markenname')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255)
                    ->placeholder('z.B. Rolex')
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->directory('brand-logos')
                    ->disk('public')
                    ->visibility('public')
                    ->imageEditor()
                    ->helperText('Empfohlen: Quadratisches Format, PNG mit transparentem Hintergrund')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('country')
                    ->label('Herkunftsland')
                    ->maxLength(255)
                    ->placeholder('z.B. Schweiz'),

                Forms\Components\TextInput::make('founded_year')
                    ->label('Gründungsjahr')
                    ->numeric()
                    ->minValue(1500)
                    ->maxValue(date('Y'))
                    ->placeholder('z.B. 1905'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktiv')
                    ->helperText('Inaktive Marken werden nicht in der Auswahlliste angezeigt')
                    ->default(true)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular()
                    ->defaultImageUrl('/images/brand-placeholder.svg'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('country')
                    ->label('Land')
                    ->searchable()
                    ->icon('heroicon-m-flag'),

                Tables\Columns\TextColumn::make('founded_year')
                    ->label('Gründungsjahr')
                    ->sortable(),

                Tables\Columns\TextColumn::make('watches_count')
                    ->label('Uhren')
                    ->counts('watches')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Erstellt')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Aktiv',
                        0 => 'Inaktiv',
                    ]),

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
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
