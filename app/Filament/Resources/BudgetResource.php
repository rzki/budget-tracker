<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Budget;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\BudgetResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BudgetResource\RelationManagers;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 1;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('budgetId')
                    ->default(fn () => (string) Str::uuid7()),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('amount')
                    ->required()
                    ->numeric()
                    ->reactive()
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('budgetPockets')
                    ->label('Budget >< Pockets')
                    ->relationship('budgetPockets')
                    ->schema([
                        Forms\Components\Select::make('pocket_id')
                            ->relationship('pocket', 'name')
                            ->createOptionForm([
                                Forms\Components\Hidden::make('pocketId')
                                    ->default(fn () => (string) Str::uuid7()),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ])
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->disableOptionWhen(function ($value, $state, callable $get) {
                                // Get all currently selected pocket IDs in this repeater
                                $budgetPockets = $get('../../budgetPockets') ?? [];
                                $selectedPocketIds = collect($budgetPockets)
                                    ->pluck('pocket_id')
                                    ->filter()
                                    ->toArray();
                                
                                // Don't disable the current item's selection
                                $currentPocketId = $get('pocket_id');
                                if ($value == $currentPocketId) {
                                    return false;
                                }
                                
                                // Disable if this pocket is already selected in another row
                                return in_array($value, $selectedPocketIds);
                            }),
                        Forms\Components\TextInput::make('allocated_amount')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $budgetPockets = $get('budgetPockets') ?? [];
                                $total = collect($budgetPockets)->sum('allocated_amount');
                                $set('total_allocated', $total);
                                
                                $budgetAmount = $get('amount') ?? 0;
                                $remaining = $budgetAmount - $total;
                                $set('remaining_balance', $remaining);
                            }),
                    ])
                    ->reorderable()
                    ->reorderableWithDragAndDrop(false)
                    ->reorderableWithButtons()
                    ->columnSpanFull()
                    ->createItemButtonLabel('Add Pocket')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $budgetPockets = $get('budgetPockets') ?? [];
                        $total = collect($budgetPockets)->sum('allocated_amount');
                        $set('total_allocated', $total);
                        
                        $budgetAmount = $get('amount') ?? 0;
                        $remaining = $budgetAmount - $total;
                        $set('remaining_balance', $remaining);
                    }),
                Forms\Components\TextInput::make('total_allocated')
                    ->label('Total Allocated')
                    ->numeric()
                    ->disabled()
                    ->live()
                    ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, callable $get) {
                        $budgetPockets = $get('budgetPockets') ?? [];
                        $total = collect($budgetPockets)->sum('allocated_amount');
                        $component->state($total);
                    })
                    ->formatStateUsing(fn ($state) => 'Rp. ' . number_format($state ?? 0, 0, '', '.'))
                    ->columnSpan(1),
                Forms\Components\TextInput::make('remaining_balance')
                    ->label('Remaining Balance')
                    ->numeric()
                    ->disabled()
                    ->live()
                    ->afterStateHydrated(function (Forms\Components\TextInput $component, $state, callable $get) {
                        $budgetAmount = $get('amount') ?? 0;
                        $budgetPockets = $get('budgetPockets') ?? [];
                        $totalAllocated = collect($budgetPockets)->sum('allocated_amount');
                        $remaining = $budgetAmount - $totalAllocated;
                        $component->state($remaining);
                    })
                    ->formatStateUsing(fn ($state) => 'Rp. ' . number_format($state ?? 0, 0, '', '.'))
                    ->columnSpan(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Total Budget')
                    ->numeric()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => 'IDR ' . number_format($state, 0, ',', '.')),
                Tables\Columns\TextColumn::make('allocated_amount')
                    ->label('Allocated')
                    ->numeric()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->budgetPockets()->sum('allocated_amount');
                    })
                    ->formatStateUsing(fn ($state) => 'IDR ' . number_format($state ?? 0, 0, ',', '.')),
                Tables\Columns\TextColumn::make('unallocated_amount')
                    ->label('Unallocated')
                    ->numeric()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        $totalBudget = $record->amount;
                        $allocatedAmount = $record->budgetPockets()->sum('allocated_amount');
                        return $totalBudget - $allocatedAmount;
                    })
                    ->formatStateUsing(fn ($state) => 'IDR ' . number_format($state ?? 0, 0, ',', '.'))
                    ->color(fn ($state) => $state < 0 ? 'danger' : ($state > 0 ? 'success' : 'light')),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->successNotificationTitle('Budget updated successfully'),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('Budget deleted successfully')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->successNotificationTitle('Selected budgets deleted successfully'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBudgets::route('/'),
        ];
    }
}
