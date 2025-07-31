<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?string $navigationGroup = 'Budgeting';
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('transactionId')
                    ->default(fn () => (string) Str::uuid7()),
                Forms\Components\Select::make('budget_pocket_id')
                    ->label('Pocket')
                    ->options(
                        \App\Models\BudgetPocket::with(['budget', 'pocket'])
                            ->whereHas('budget', fn($query) => $query->latest()->limit(1))
                            ->get()
                            ->mapWithKeys(fn($bp) => [$bp->id => $bp->pocket->name])
                    )
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'income' => 'Income',
                        'expense' => 'Expense',
                    ])
                    ->required()
                    ->default('expense'),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required(),
                Forms\Components\DatePicker::make('date')->required()->default(now()),
                Forms\Components\TextInput::make('note')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['budgetPocket.pocket'])->orderByDesc('date')->orderByDesc('created_at'))
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('budgetPocket.pocket.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                Tables\Columns\TextColumn::make('note'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('budget_pocket_id')
                    ->label('Pocket')
                    ->options(
                        \App\Models\BudgetPocket::with(['budget', 'pocket'])
                            ->whereHas('budget', fn($query) => $query->latest()->limit(1))
                            ->get()
                            ->mapWithKeys(fn($bp) => [$bp->id => $bp->pocket->name])
                    ),
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'income' => 'Income',
                        'expense' => 'Expense',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTransactions::route('/'),
        ];
    }
}
