<?php

namespace App\Filament\Resources\Guru\Pages;

use App\Filament\Resources\Guru\GuruResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGuru extends EditRecord
{
    protected static string $resource = GuruResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
