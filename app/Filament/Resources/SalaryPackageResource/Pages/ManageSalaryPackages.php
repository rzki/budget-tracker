<?php

namespace App\Filament\Resources\SalaryPackageResource\Pages;

use App\Filament\Resources\SalaryPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSalaryPackages extends ManageRecords
{
    protected static string $resource = SalaryPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->successNotificationTitle('Salary package created successfully'),
        ];
    }
}
