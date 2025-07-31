<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Pages\Dashboard as BaseDashboard;

final class Dashboard extends BaseDashboard
{
    protected function getHeaderActions(): array
    {
        return [
            Action::make('new_transaction')
                ->label('New Transaction')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->url(route('filament.dashboard.resources.transactions.index') . '?action=create')
                ->button(),
        ];
    }
}
