<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Gestion';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $modelLabel = 'utilisateur';

    protected static ?string $pluralModelLabel = 'utilisateurs';

    protected static ?int $navigationSort = 4;

    public static function getRoleOptions(): array
    {
        return [
            'admin'     => 'Administrateur',
            'client'    => 'Client',
            'artisan'   => 'Artisan',
            'livreur'   => 'Livreur',
            'apprenant' => 'Apprenant',
        ];
    }

    public static function getStatutOptions(): array
    {
        return [
            'actif'    => 'Actif',
            'suspendu' => 'Suspendu',
            'inactif'  => 'Inactif',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations')
                    ->schema([
                        Forms\Components\TextInput::make('nom')
                            ->label('Nom')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('prenom')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\TextInput::make('password')
                            ->label('Mot de passe')
                            ->password()
                            ->revealable()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),

                        Forms\Components\Select::make('role')
                            ->label('Rôle')
                            ->options(static::getRoleOptions())
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('statut')
                            ->label('Statut')
                            ->options(static::getStatutOptions())
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('telephone')
                            ->label('Téléphone')
                            ->tel(),

                        Forms\Components\TextInput::make('ville')
                            ->label('Ville'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Utilisateur')
                    ->schema([
                        Infolists\Components\TextEntry::make('nom_complet')
                            ->label('Nom complet'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('role')
                            ->label('Rôle')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => static::getRoleOptions()[$state] ?? $state),
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
                        Infolists\Components\TextEntry::make('telephone')
                            ->label('Téléphone')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('ville')
                            ->label('Ville')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Inscrit le')
                            ->date('d/m/Y'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom_complet')
                    ->label('Nom complet')
                    ->searchable(['nom', 'prenom'])
                    ->sortable(['nom', 'prenom']),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::getRoleOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'admin'     => 'danger',
                        'artisan'   => 'warning',
                        'client'    => 'info',
                        'livreur'   => 'primary',
                        'apprenant' => 'success',
                        default     => 'gray',
                    }),

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

                Tables\Columns\TextColumn::make('ville')
                    ->label('Ville'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Rôle')
                    ->options(static::getRoleOptions()),

                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options(static::getStatutOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('suspendre')
                    ->label('Suspendre')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (User $record): bool => $record->statut === 'actif')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->update(['statut' => 'suspendu']);

                        FilamentNotification::make()
                            ->title('Utilisateur suspendu')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('activer')
                    ->label('Activer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (User $record): bool => $record->statut !== 'actif')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $record->update(['statut' => 'actif']);

                        FilamentNotification::make()
                            ->title('Utilisateur activé')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
