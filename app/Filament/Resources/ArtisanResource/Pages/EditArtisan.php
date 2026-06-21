<?php

namespace App\Filament\Resources\ArtisanResource\Pages;

use App\Filament\Resources\ArtisanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditArtisan extends EditRecord
{
    protected static string $resource = ArtisanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
