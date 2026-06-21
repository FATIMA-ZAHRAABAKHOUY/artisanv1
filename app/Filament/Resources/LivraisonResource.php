<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LivraisonResource\Pages;
use App\Models\Livraison;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LivraisonResource extends Resource
{
    protected static ?string $model = Livraison::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Gestion';

    protected static ?string $navigationLabel = 'Livraisons';

    protected static ?string $modelLabel = 'livraison';

    protected static ?string $pluralModelLabel = 'livraisons';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()
            ->whereNull('livreur_id')
            ->whereNotIn('statut', ['delivered', 'failed'])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getStatutOptions(): array
    {
        return [
            'assigned'   => 'Assignée',
            'in_transit' => 'En transit',
            'delivered'  => 'Livrée',
            'failed'     => 'Échouée',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Livraison')
                    ->schema([
                        Forms\Components\Select::make('commande_id')
                            ->label('Commande')
                            ->relationship('commande', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => 'Commande #'.$record->id)
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('livreur_id')
                            ->label('Livreur')
                            ->options(
                                User::query()->where('role', 'livreur')->where('statut', 'actif')
                                    ->get()
                                    ->mapWithKeys(fn (User $user) => [$user->id => $user->nom_complet])
                            )
                            ->searchable(),

                        Forms\Components\Textarea::make('adresse')
                            ->label('Adresse')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('date_livraison_prev')
                            ->label('Date de livraison prévue'),

                        Forms\Components\Select::make('statut')
                            ->label('Statut')
                            ->options(static::getStatutOptions())
                            ->native(false),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('commande_id')
                    ->label('Commande')
                    ->formatStateUsing(fn ($state) => 'Commande #'.$state)
                    ->sortable(),

                Tables\Columns\TextColumn::make('commande.client.nom_complet')
                    ->label('Client'),

                Tables\Columns\TextColumn::make('commande.ville')
                    ->label('Ville'),

                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::getStatutOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'assigned'   => 'gray',
                        'in_transit' => 'warning',
                        'delivered'  => 'success',
                        'failed'     => 'danger',
                        default      => 'gray',
                    }),

                Tables\Columns\TextColumn::make('livreur.nom_complet')
                    ->label('Livreur')
                    ->placeholder('Non assigné'),

                Tables\Columns\TextColumn::make('date_livraison_prev')
                    ->label('Livraison prévue')
                    ->date('d/m/Y')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('date_livree')
                    ->label('Livrée le')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options(static::getStatutOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('assigner_livreur')
                    ->label('Assigner livreur')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->visible(fn (Livraison $record): bool => $record->livreur_id === null)
                    ->form([
                        Forms\Components\Select::make('livreur_id')
                            ->label('Livreur')
                            ->options(
                                User::query()->where('role', 'livreur')->where('statut', 'actif')
                                    ->get()
                                    ->mapWithKeys(fn (User $user) => [$user->id => $user->nom_complet])
                            )
                            ->required()
                            ->searchable(),

                        Forms\Components\DatePicker::make('date_livraison_prev')
                            ->label('Date de livraison prévue'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Notes (N° suivi, transporteur…)')
                            ->rows(2),
                    ])
                    ->action(function (Livraison $record, array $data): void {
                        $record->update([
                            'livreur_id'          => $data['livreur_id'],
                            'date_livraison_prev' => $data['date_livraison_prev'] ?? null,
                            'notes'               => $data['notes'] ?? $record->notes,
                            'statut'              => 'assigned',
                        ]);

                        FilamentNotification::make()
                            ->title('Livreur assigné')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('confirmer_livraison')
                    ->label('Confirmer livraison')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Livraison $record): bool => in_array($record->statut, ['assigned', 'in_transit'], true))
                    ->requiresConfirmation()
                    ->action(function (Livraison $record): void {
                        $record->update([
                            'statut'      => 'delivered',
                            'date_livree' => now(),
                        ]);

                        FilamentNotification::make()
                            ->title('Livraison confirmée')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLivraisons::route('/'),
            'create' => Pages\CreateLivraison::route('/create'),
            'edit' => Pages\EditLivraison::route('/{record}/edit'),
        ];
    }
}
