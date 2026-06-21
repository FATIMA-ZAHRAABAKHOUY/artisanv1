<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArtisanResource\Pages;
use App\Models\Artisan;
use App\Models\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArtisanResource extends Resource
{
    protected static ?string $model = Artisan::class;

    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';

    protected static ?string $navigationGroup = 'Gestion';

    protected static ?string $navigationLabel = 'Artisans';

    protected static ?string $modelLabel = 'artisan';

    protected static ?string $pluralModelLabel = 'artisans';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->where('is_verified', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getStatutOptions(): array
    {
        return [
            'actif'     => 'Actif',
            'suspendu'  => 'Suspendu',
            'inactif'   => 'Inactif',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Profil artisan')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Utilisateur')
                            ->relationship(
                                'user',
                                'email',
                                fn (Builder $query) => $query->where('role', 'artisan')
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->nom_complet.' ('.$record->email.')')
                            ->searchable(['nom', 'prenom', 'email'])
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('specialite')
                            ->label('Spécialité')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('bio')
                            ->label('Biographie')
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('statut')
                            ->label('Statut')
                            ->options(static::getStatutOptions())
                            ->required()
                            ->native(false),

                        Forms\Components\Toggle::make('is_verified')
                            ->label('Vérifié'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Artisan')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.nom_complet')
                            ->label('Nom complet'),
                        Infolists\Components\TextEntry::make('specialite')
                            ->label('Spécialité'),
                        Infolists\Components\TextEntry::make('user.ville')
                            ->label('Ville'),
                        Infolists\Components\IconEntry::make('is_verified')
                            ->label('Vérifié')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('statut')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => static::getStatutOptions()[$state] ?? $state)
                            ->color(fn (string $state): string => match ($state) {
                                'actif'    => 'success',
                                'suspendu' => 'danger',
                                'inactif'  => 'warning',
                                default    => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('note_moyenne')
                            ->label('Note moyenne')
                            ->formatStateUsing(fn ($state) => $state.' /5'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Date d\'adhésion')
                            ->date('d/m/Y'),
                        Infolists\Components\TextEntry::make('bio')
                            ->label('Biographie')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.nom_complet')
                    ->label('Nom')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('user', function (Builder $q) use ($search): void {
                            $q->where('nom', 'ilike', "%{$search}%")
                                ->orWhere('prenom', 'ilike', "%{$search}%");
                        });
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->join('users', 'artisans.user_id', '=', 'users.id')
                            ->orderBy('users.nom', $direction)
                            ->select('artisans.*');
                    }),

                Tables\Columns\TextColumn::make('specialite')
                    ->label('Spécialité')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.ville')
                    ->label('Ville'),

                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Vérifié')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::getStatutOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'actif'    => 'success',
                        'suspendu' => 'danger',
                        'inactif'  => 'warning',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('note_moyenne')
                    ->label('Note')
                    ->suffix(' /5')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Adhésion')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('Vérification')
                    ->trueLabel('Vérifiés')
                    ->falseLabel('En attente'),

                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options(static::getStatutOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('valider')
                    ->label('Valider')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Artisan $record): bool => ! $record->is_verified)
                    ->requiresConfirmation()
                    ->modalHeading('Valider cet artisan ?')
                    ->action(function (Artisan $record): void {
                        $record->update([
                            'is_verified' => true,
                            'statut'      => 'actif',
                        ]);

                        Notification::envoyer(
                            $record->user_id,
                            'artisan_verifie',
                            'Compte artisan validé',
                            'Votre profil artisan a été validé. Vous pouvez désormais vendre vos produits sur la plateforme.',
                            ['artisan_id' => $record->id]
                        );

                        FilamentNotification::make()
                            ->title('Artisan validé')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('suspendre')
                    ->label('Suspendre')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (Artisan $record): bool => $record->statut === 'actif')
                    ->requiresConfirmation()
                    ->modalHeading('Suspendre cet artisan ?')
                    ->action(function (Artisan $record): void {
                        $record->update(['statut' => 'suspendu']);

                        FilamentNotification::make()
                            ->title('Artisan suspendu')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArtisans::route('/'),
            'create' => Pages\CreateArtisan::route('/create'),
            'view' => Pages\ViewArtisan::route('/{record}'),
            'edit' => Pages\EditArtisan::route('/{record}/edit'),
        ];
    }
}
