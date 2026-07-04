<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Filament\Resources\VendorResource\RelationManagers;
use App\Models\Vendor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('business_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('vendor_type')
                    ->options([
                        'boutique' => 'Boutique',
                        'street_food' => 'Street food',
                        'restaurant' => 'Restaurant',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('logo_url')
                    ->maxLength(255),
                Forms\Components\TextInput::make('cover_url')
                    ->maxLength(255),
                Forms\Components\TextInput::make('address_text')
                    ->maxLength(255),
                Forms\Components\TextInput::make('latitude')
                    ->numeric(),
                Forms\Components\TextInput::make('longitude')
                    ->numeric(),
                Forms\Components\Select::make('verification_level')
                    ->options([
                        'non_verifie' => 'Non vérifié',
                        'identite_confirmee' => 'Identité confirmée',
                        'verifie' => 'Vérifié',
                    ])
                    ->required()
                    ->default('non_verifie'),
                Forms\Components\TextInput::make('rccm_number')
                    ->maxLength(255),
                Forms\Components\TextInput::make('dfe_number')
                    ->maxLength(255),
                Forms\Components\TextInput::make('rccm_document_url')
                    ->maxLength(255),
                Forms\Components\TextInput::make('cni_document_url')
                    ->maxLength(255),
                Forms\Components\Select::make('rccm_assist_status')
                    ->options([
                        'dossier_recu' => 'Dossier reçu',
                        'depose_cepici' => 'Déposé au CEPICI',
                        'en_attente' => 'En attente',
                        'obtenu' => 'Obtenu',
                    ])
                    ->native(false),
                Forms\Components\TextInput::make('commission_rate')
                    ->required()
                    ->numeric()
                    ->suffix('%')
                    ->default(10),
                Forms\Components\Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('business_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Propriétaire')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor_type')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('verification_level')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'verifie' => 'success',
                        'identite_confirmee' => 'warning',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('commission_rate')
                    ->numeric()
                    ->suffix('%')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('rccm_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('dfe_number')
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
                Tables\Filters\SelectFilter::make('vendor_type')
                    ->options([
                        'boutique' => 'Boutique',
                        'street_food' => 'Street food',
                        'restaurant' => 'Restaurant',
                    ]),
                Tables\Filters\SelectFilter::make('verification_level')
                    ->options([
                        'non_verifie' => 'Non vérifié',
                        'identite_confirmee' => 'Identité confirmée',
                        'verifie' => 'Vérifié',
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
