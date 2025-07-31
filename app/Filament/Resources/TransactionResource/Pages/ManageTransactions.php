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
            Actions\CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    // Pre-fill budget_pocket_id from URL parameter
                    if (request()->has('budget_pocket_id') && !isset($data['budget_pocket_id'])) {
                        $data['budget_pocket_id'] = request()->get('budget_pocket_id');
                    }
                    return $data;
                })
                ->fillForm(function (): array {
                    $formData = [];
                    // Pre-fill budget_pocket_id from URL parameter
                    if (request()->has('budget_pocket_id')) {
                        $formData['budget_pocket_id'] = request()->get('budget_pocket_id');
                    }
                    return $formData;
                }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PocketsAllocationWidget::class,
        ];
    }
}
