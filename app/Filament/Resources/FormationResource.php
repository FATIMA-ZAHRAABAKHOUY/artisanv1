<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FormationResource\Pages;
use App\Models\Formation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FormationResource extends Resource
{
    protected static ?string $model = Formation::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Formations';

    protected static ?string $navigationLabel = 'Formations';

    protected static ?string $modelLabel = 'formation';

    protected static ?string $pluralModelLabel = 'formations';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount([
                'inscriptions as inscrits_count' => fn (Builder $query) => $query->whereIn('statut', ['inscrit', 'confirme']),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Formation')
                    ->schema([
                        Forms\Components\Select::make('artisan_id')
                            ->label('Artisan')
                            ->relationship('artisan', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user?->nom_complet ?? 'Artisan #'.$record->id)
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('titre')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('date_debut')
                            ->label('Date de début')
                            ->required(),

                        Forms\Components\DatePicker::make('date_fin')
                            ->label('Date de fin')
                            ->required(),

                        Forms\Components\TextInput::make('prix')
                            ->label('Prix')
                            ->numeric()
                            ->prefix('MAD')
                            ->required(),

                        Forms\Components\TextInput::make('places_max')
                            ->label('Places max')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('lieu')
                            ->label('Lieu'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Formation')
                    ->schema([
                        Infolists\Components\TextEntry::make('titre')
                            ->label('Titre'),
                        Infolists\Components\TextEntry::make('artisan.user.nom_complet')
                            ->label('Artisan'),
                        Infolists\Components\TextEntry::make('date_debut')
                            ->label('Date de début')
                            ->date('d/m/Y'),
                        Infolists\Components\TextEntry::make('date_fin')
                            ->label('Date de fin')
                            ->date('d/m/Y'),
                        Infolists\Components\TextEntry::make('prix')
                            ->label('Prix')
                            ->money('MAD'),
                        Infolists\Components\TextEntry::make('places_max')
                            ->label('Places max'),
                        Infolists\Components\TextEntry::make('inscrits_count')
                            ->label('Inscrits'),
                        Infolists\Components\IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                        Infolists\Components\TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titre')
                    ->label('Titre')
                    ->searchable(),

                Tables\Columns\TextColumn::make('artisan.user.nom_complet')
                    ->label('Artisan'),

                Tables\Columns\TextColumn::make('date_debut')
                    ->label('Date de début')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('date_fin')
                    ->label('Date de fin')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('prix')
                    ->label('Prix')
                    ->money('MAD'),

                Tables\Columns\TextColumn::make('places_max')
                    ->label('Places max'),

                Tables\Columns\TextColumn::make('inscrits_count')
                    ->label('Inscrits'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_actif')
                    ->label(fn (Formation $record): string => $record->is_active ? 'Désactiver' : 'Activer')
                    ->icon(fn (Formation $record): string => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Formation $record): string => $record->is_active ? 'danger' : 'success')
                    ->action(fn (Formation $record) => $record->update(['is_active' => ! $record->is_active])),

                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('date_debut', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFormations::route('/'),
            'create' => Pages\CreateFormation::route('/create'),
            'view' => Pages\ViewFormation::route('/{record}'),
            'edit' => Pages\EditFormation::route('/{record}/edit'),
        ];
    }
}
