<?php

namespace App\Filament\Resources\ArtisanResource\Pages;

use App\Filament\Resources\ArtisanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewArtisan extends ViewRecord
{
    protected static string $resource = ArtisanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
