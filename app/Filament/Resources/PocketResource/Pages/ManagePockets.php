<?php

namespace App\Filament\Resources\PocketResource\Pages;

use App\Filament\Resources\PocketResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePockets extends ManageRecords
{
    protected static string $resource = PocketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->successNotificationTitle('Pocket created successfully')
                ->slideOver(),
        ];
    }
}
