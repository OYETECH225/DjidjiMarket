<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public const STATUSES = [
        'en_attente_paiement' => 'En attente de paiement',
        'paiement_sequestre' => 'Paiement séquestré',
        'confirmee' => 'Confirmée',
        'en_preparation' => 'En préparation',
        'cherche_livreur' => 'Recherche livreur',
        'livreur_assigne' => 'Livreur assigné',
        'recuperee' => 'Récupérée',
        'en_livraison' => 'En livraison',
        'livree' => 'Livrée',
        'paiement_libere' => 'Paiement libéré',
        'litige_ouvert' => 'Litige ouvert',
        'annulee' => 'Annulée',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->relationship('client', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('vendor_id')
                    ->relationship('vendor', 'business_name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('courier_id')
                    ->relationship('courier', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('status')
                    ->options(self::STATUSES)
                    ->required()
                    ->default('en_attente_paiement'),
                Forms\Components\TextInput::make('delivery_latitude')
                    ->numeric(),
                Forms\Components\TextInput::make('delivery_longitude')
                    ->numeric(),
                Forms\Components\TextInput::make('delivery_address_text')
                    ->maxLength(255),
                Forms\Components\TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->suffix('XOF'),
                Forms\Components\TextInput::make('delivery_fee')
                    ->required()
                    ->numeric()
                    ->suffix('XOF')
                    ->default(0),
                Forms\Components\TextInput::make('commission_amount')
                    ->required()
                    ->numeric()
                    ->suffix('XOF')
                    ->default(0),
                Forms\Components\Select::make('source')
                    ->options([
                        'app' => 'App',
                        'web' => 'Web',
                        'tiktok_live' => 'TikTok Live',
                        'lien_vendeur' => 'Lien vendeur',
                    ])
                    ->required()
                    ->default('app'),
                Forms\Components\TextInput::make('promo_code_used')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor.business_name')
                    ->label('Vendeur')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('courier.name')
                    ->label('Livreur')
                    ->placeholder('—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::STATUSES[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'livree', 'paiement_libere' => 'success',
                        'litige_ouvert', 'annulee' => 'danger',
                        'en_attente_paiement' => 'gray',
                        default => 'warning',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('source')
                    ->badge(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('XOF')
                    ->sortable(),
                Tables\Columns\TextColumn::make('delivery_fee')
                    ->money('XOF')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->money('XOF')
                    ->sortable()
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
                Tables\Filters\SelectFilter::make('status')
                    ->options(self::STATUSES),
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'app' => 'App',
                        'web' => 'Web',
                        'tiktok_live' => 'TikTok Live',
                        'lien_vendeur' => 'Lien vendeur',
                    ]),
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
            RelationManagers\ItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
