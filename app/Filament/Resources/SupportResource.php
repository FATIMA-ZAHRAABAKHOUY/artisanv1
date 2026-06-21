<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupportResource\Pages;
use App\Models\Notification;
use App\Models\Support;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupportResource extends Resource
{
    protected static ?string $model = Support::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Configuration';

    protected static ?string $navigationLabel = 'Support';

    protected static ?string $modelLabel = 'ticket support';

    protected static ?string $pluralModelLabel = 'tickets support';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->where('statut', 'ouvert')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getStatutOptions(): array
    {
        return [
            'ouvert'   => 'Ouvert',
            'en_cours' => 'En cours',
            'resolu'   => 'Résolu',
            'ferme'    => 'Fermé',
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Ticket support')
                    ->schema([
                        Infolists\Components\TextEntry::make('id')
                            ->label('N°')
                            ->formatStateUsing(fn ($state) => '#'.$state),
                        Infolists\Components\TextEntry::make('user.nom_complet')
                            ->label('Client'),
                        Infolists\Components\TextEntry::make('objet')
                            ->label('Objet'),
                        Infolists\Components\TextEntry::make('statut')
                            ->label('Statut')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => static::getStatutOptions()[$state] ?? $state)
                            ->color(fn (string $state): string => match ($state) {
                                'ouvert'   => 'danger',
                                'en_cours' => 'warning',
                                'resolu'   => 'success',
                                'ferme'    => 'gray',
                                default    => 'gray',
                            }),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Créé le')
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.nom_complet')
                    ->label('Client'),

                Tables\Columns\TextColumn::make('objet')
                    ->label('Objet')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::getStatutOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'ouvert'   => 'danger',
                        'en_cours' => 'warning',
                        'resolu'   => 'success',
                        'ferme'    => 'gray',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options(static::getStatutOptions()),
            ])
            ->actions([
                Tables\Actions\Action::make('marquer_en_cours')
                    ->label('Marquer en cours')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn (Support $record): bool => $record->statut === 'ouvert')
                    ->action(function (Support $record): void {
                        $record->update(['statut' => 'en_cours']);

                        FilamentNotification::make()
                            ->title('Ticket marqué en cours')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('resoudre')
                    ->label('Résoudre')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Support $record): bool => in_array($record->statut, ['ouvert', 'en_cours'], true))
                    ->requiresConfirmation()
                    ->action(function (Support $record): void {
                        $record->update(['statut' => 'resolu']);

                        Notification::envoyer(
                            $record->user_id,
                            'support_resolu',
                            'Ticket support résolu',
                            "Votre demande « {$record->objet} » a été résolue.",
                            ['support_id' => $record->id]
                        );

                        FilamentNotification::make()
                            ->title('Ticket résolu')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSupports::route('/'),
            'view' => Pages\ViewSupport::route('/{record}'),
        ];
    }
}
