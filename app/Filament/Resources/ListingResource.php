<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ListingResource\Pages;
use App\Models\Listing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ListingResource extends Resource
{
    protected static ?string $model = Listing::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('vendor_id')
                    ->relationship('vendor', 'business_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'produit' => 'Produit',
                        'plat_du_jour' => 'Plat du jour',
                        'menu_item' => 'Menu item',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->suffix('XOF'),
                Forms\Components\TextInput::make('sale_price')
                    ->label('Prix promo (vente flash)')
                    ->numeric()
                    ->suffix('XOF')
                    ->rules(['lt:price'])
                    ->requiredWith('sale_ends_at'),
                Forms\Components\DateTimePicker::make('sale_ends_at')
                    ->label('Fin de la vente flash')
                    ->requiredWith('sale_price'),
                Forms\Components\TextInput::make('currency')
                    ->required()
                    ->maxLength(3)
                    ->default('XOF'),
                Forms\Components\TextInput::make('stock_quantity')
                    ->numeric()
                    ->helperText('Laisser vide pour les plats du jour'),
                Forms\Components\DateTimePicker::make('available_from')
                    ->helperText('Utile pour plat_du_jour / street_food'),
                Forms\Components\DateTimePicker::make('available_until'),
                Forms\Components\FileUpload::make('photo_urls')
                    ->multiple()
                    ->image()
                    ->directory('listings')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('display_number')
                    ->numeric()
                    ->helperText('Pour les commandes vocales en TikTok Live, ex: "l\'article numéro 3"'),
                Forms\Components\TextInput::make('promo_code')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor.business_name')
                    ->label('Vendeur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('XOF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Vente flash')
                    ->money('XOF')
                    ->placeholder('—')
                    ->description(fn (Listing $record) => $record->sale_ends_at?->isFuture()
                        ? 'Jusqu\'au '.$record->sale_ends_at->format('d/m H:i')
                        : ($record->sale_ends_at ? 'Terminée' : null)),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->numeric()
                    ->sortable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('display_number')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('available_from')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('available_until')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('promo_code')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'produit' => 'Produit',
                        'plat_du_jour' => 'Plat du jour',
                        'menu_item' => 'Menu item',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListListings::route('/'),
            'create' => Pages\CreateListing::route('/create'),
            'edit' => Pages\EditListing::route('/{record}/edit'),
        ];
    }
}
