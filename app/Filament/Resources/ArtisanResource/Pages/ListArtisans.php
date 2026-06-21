<?php

namespace App\Filament\Resources\ArtisanResource\Pages;

use App\Filament\Resources\ArtisanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListArtisans extends ListRecords
{
    protected static string $resource = ArtisanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
