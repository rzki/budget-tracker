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
                            ->mapWithKeys(fn($bp) => [$bp->id => $bp->budget->name . ' - ' . $bp->pocket->name])
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
                Forms\Components\DatePicker::make('date')->required(),
                Forms\Components\TextInput::make('note'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('budgetPocket.pocket.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                //
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
