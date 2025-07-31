<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use App\Filament\Resources\TransactionResource\Widgets\PocketsAllocationWidget;
use Filament\Actions;
use Filament\Forms;
use Filament\Resources\Pages\ManageRecords;

class ManageTransactions extends ManageRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->slideOver()
                ->successNotificationTitle('Transaction created successfully'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PocketsAllocationWidget::class,
        ];
    }
}
