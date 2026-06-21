<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommandeResource\Pages;
use App\Models\Commande;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CommandeResource extends Resource
{
    protected static ?string $model = Commande::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Gestion';

    protected static ?string $navigationLabel = 'Commandes';

    protected static ?string $modelLabel = 'commande';

    protected static ?string $pluralModelLabel = 'commandes';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->where('statut', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getStatutOptions(): array
    {
        return [
            'pending'    => 'En attente',
            'confirmed'  => 'Confirmée',
            'processing' => 'En traitement',
            'shipped'    => 'Expédiée',
            'delivered'  => 'Livrée',
            'cancelled'  => 'Annulée',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Statut commande')
                    ->schema([
                        Forms\Components\Select::make('statut')
                            ->label('Statut')
                            ->options(static::getStatutOptions())
                            ->required()
                            ->native(false),
                    ]),

                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->nullable()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Commande')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('N°')
                            ->formatStateUsing(fn ($state) => '#'.$state),
                        Infolists\Components\TextEntry::make('client.nom_complet')
                            ->label('Client'),
                        Infolists\Components\TextEntry::make('statut')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => static::getStatutOptions()[$state] ?? $state)
                            ->color(fn (string $state): string => match ($state) {
                                'pending'    => 'warning',
                                'confirmed'  => 'primary',
                                'processing' => 'info',
                                'shipped'    => 'info',
                                'delivered'  => 'success',
                                'cancelled'  => 'danger',
                                default      => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('total_ttc')
                            ->label('Total TTC')
                            ->money('MAD'),
                        Infolists\Components\TextEntry::make('ville')
                            ->label('Ville'),
                        Infolists\Components\TextEntry::make('adresse_livraison')
                            ->label('Adresse de livraison')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Créée le')
                            ->dateTime('d/m/Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('N°')
                    ->formatStateUsing(fn ($state) => '#'.$state)
                    ->sortable()
                    ->url(fn (Commande $record): string => static::getUrl('view', ['record' => $record])),

                Tables\Columns\TextColumn::make('client.nom_complet')
                    ->label('Client')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('client', function (Builder $q) use ($search): void {
                            $q->where('nom', 'ilike', "%{$search}%")
                                ->orWhere('prenom', 'ilike', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('total_ttc')
                    ->label('Total TTC')
                    ->money('MAD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::getStatutOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'pending'    => 'warning',
                        'confirmed'  => 'primary',
                        'processing' => 'info',
                        'shipped'    => 'info',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        default      => 'gray',
                    }),

                Tables\Columns\TextColumn::make('ville')
                    ->label('Ville'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options(static::getStatutOptions()),

                Tables\Filters\Filter::make('aujourdhui')
                    ->label('Aujourd\'hui')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today())),

                Tables\Filters\Filter::make('cette_semaine')
                    ->label('Cette semaine')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ])),
            ])
            ->actions([
                Tables\Actions\Action::make('confirmer')
                    ->label('Confirmer')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Commande $record): bool => $record->statut === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmer cette commande ?')
                    ->action(function (Commande $record): void {
                        $record->update(['statut' => 'confirmed']);

                        FilamentNotification::make()
                            ->title('Commande confirmée')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('annuler')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Commande $record): bool => in_array($record->statut, ['pending', 'confirmed'], true))
                    ->requiresConfirmation()
                    ->modalHeading('Annuler cette commande ?')
                    ->modalDescription('Le stock des produits sera restauré.')
                    ->action(function (Commande $record): void {
                        $record->load('lignes.produit');

                        foreach ($record->lignes as $ligne) {
                            if ($ligne->produit) {
                                $ligne->produit->increment('stock', $ligne->quantite);
                            }
                        }

                        $record->update(['statut' => 'cancelled']);

                        FilamentNotification::make()
                            ->title('Commande annulée')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('expedier')
                    ->label('Expédier')
                    ->icon('heroicon-o-truck')
                    ->color('info')
                    ->visible(fn (Commande $record): bool => $record->statut === 'processing')
                    ->requiresConfirmation()
                    ->action(function (Commande $record): void {
                        $record->update(['statut' => 'shipped']);

                        if ($record->livraison) {
                            $record->livraison->update(['statut' => 'in_transit']);
                        }

                        FilamentNotification::make()
                            ->title('Commande expédiée')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('confirmer_selection')
                        ->label('Confirmer la sélection')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records
                                ->filter(fn (Commande $record): bool => $record->statut === 'pending')
                                ->each(fn (Commande $record) => $record->update(['statut' => 'confirmed']));
                        }),

                    Tables\Actions\BulkAction::make('exporter_csv')
                        ->label('Exporter CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records) {
                            return response()->streamDownload(function () use ($records): void {
                                $handle = fopen('php://output', 'w');
                                fputcsv($handle, ['ID', 'Client', 'Total TTC', 'Statut', 'Ville', 'Date'], ';');

                                foreach ($records as $record) {
                                    fputcsv($handle, [
                                        $record->id,
                                        $record->client?->nom_complet,
                                        $record->total_ttc,
                                        $record->statut,
                                        $record->ville,
                                        $record->created_at?->format('d/m/Y H:i'),
                                    ], ';');
                                }

                                fclose($handle);
                            }, 'commandes-'.now()->format('Y-m-d').'.csv');
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommandes::route('/'),
            'create' => Pages\CreateCommande::route('/create'),
            'view' => Pages\ViewCommande::route('/{record}'),
            'edit' => Pages\EditCommande::route('/{record}/edit'),
        ];
    }
}
