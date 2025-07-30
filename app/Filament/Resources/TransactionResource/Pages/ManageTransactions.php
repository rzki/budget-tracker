<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Filament\Widgets\PocketsAllocationWidget;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTransactions extends ManageRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PocketsAllocationWidget::class,
        ];
    }
}
