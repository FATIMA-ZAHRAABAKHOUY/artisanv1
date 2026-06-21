<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProduitResource\Pages;
use App\Models\Produit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProduitResource extends Resource
{
    protected static ?string $model = Produit::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationLabel = 'Produits';

    protected static ?string $modelLabel = 'produit';

    protected static ?string $pluralModelLabel = 'produits';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Produit')
                    ->schema([
                        Forms\Components\Select::make('artisan_id')
                            ->label('Artisan')
                            ->relationship('artisan', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user?->nom_complet ?? 'Artisan #'.$record->id)
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('categorie_id')
                            ->label('Catégorie')
                            ->relationship('categorie', 'nom')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('nom')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('prix')
                            ->label('Prix')
                            ->numeric()
                            ->prefix('MAD')
                            ->required(),

                        Forms\Components\TextInput::make('stock')
                            ->label('Stock')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('artisan.user.nom_complet')
                    ->label('Artisan')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('artisan.user', function (Builder $q) use ($search): void {
                            $q->where('nom', 'ilike', "%{$search}%")
                                ->orWhere('prenom', 'ilike', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('categorie.nom')
                    ->label('Catégorie'),

                Tables\Columns\TextColumn::make('prix')
                    ->label('Prix')
                    ->money('MAD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'danger',
                        $state <= 5  => 'warning',
                        default      => 'success',
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),

                Tables\Filters\Filter::make('rupture_stock')
                    ->label('Rupture de stock')
                    ->query(fn (Builder $query): Builder => $query->where('stock', 0)),

                Tables\Filters\Filter::make('stock_faible')
                    ->label('Stock faible')
                    ->query(fn (Builder $query): Builder => $query->where('stock', '<=', 5)->where('stock', '>', 0)),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_actif')
                    ->label(fn (Produit $record): string => $record->is_active ? 'Désactiver' : 'Activer')
                    ->icon(fn (Produit $record): string => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Produit $record): string => $record->is_active ? 'danger' : 'success')
                    ->action(fn (Produit $record) => $record->update(['is_active' => ! $record->is_active])),

                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProduits::route('/'),
            'create' => Pages\CreateProduit::route('/create'),
            'edit' => Pages\EditProduit::route('/{record}/edit'),
        ];
    }
}
