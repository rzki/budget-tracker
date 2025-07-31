<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalaryPackageResource\Pages;
use App\Filament\Resources\SalaryPackageResource\RelationManagers;
use App\Models\SalaryPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class SalaryPackageResource extends Resource
{
    protected static ?string $model = SalaryPackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Salary';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('salaryPackageId')
                    ->default(fn () => (string) Str::uuid7()),
                    
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                    
                Forms\Components\Textarea::make('description')
                    ->maxLength(500)
                    ->columnSpanFull(),
                    
                Forms\Components\TextInput::make('base_salary')
                    ->label('Base Salary')
                    ->required()
                    ->numeric()
                    ->prefix('IDR')
                    ->step(1000)
                    ->columnSpanFull(),
                    
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\DatePicker::make('calculation_period_start')
                            ->label('Period Start (21st)')
                            ->required()
                            ->default(now()->day(21)->format('Y-m-d')),
                            
                        Forms\Components\DatePicker::make('calculation_period_end')
                            ->label('Period End (20th next month)')
                            ->required()
                            ->default(now()->addMonth()->day(20)->format('Y-m-d')),
                    ]),
                    
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                    
                Forms\Components\Section::make('Salary Components')
                    ->schema([
                        Forms\Components\Repeater::make('salaryComponents')
                            ->relationship('salaryComponents')
                            ->schema([
                                Forms\Components\Hidden::make('salaryComponentId')
                                    ->default(fn () => (string) Str::uuid7()),
                                    
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->placeholder('e.g., Base Salary, Phone Credit, Meal, Transport'),
                                            
                                        Forms\Components\Select::make('type')
                                            ->required()
                                            ->options([
                                                'fixed' => 'Fixed Amount',
                                                'relative' => 'Per Work Day',
                                            ]),
                                            
                                        Forms\Components\TextInput::make('amount')
                                            ->required()
                                            ->numeric()
                                            ->prefix('IDR')
                                            ->step(1000),
                                    ]),
                                    
                                Forms\Components\Textarea::make('description')
                                    ->placeholder('Optional description')
                                    ->columnSpanFull(),
                                    
                                Forms\Components\Toggle::make('is_active')
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->columns(1)
                            ->defaultItems(0)
                            ->addActionLabel('Add Salary Component')
                            ->collapsible(),
                    ])
                    ->collapsible(),
                    
                Forms\Components\Section::make('Salary Reductions')
                    ->description('Jamsostek, BPJS, PPh21, and attendance-based deductions')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('jamsostek_reduction')
                                    ->label('Jamsostek Reduction')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('IDR')
                                    ->placeholder('0'),
                                Forms\Components\TextInput::make('bpjs_reduction')
                                    ->label('BPJSTK Reduction')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('IDR')
                                    ->placeholder('0'),
                                    
                                Forms\Components\TextInput::make('pph21_reduction')
                                    ->label('PPh 21 Reduction')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('IDR')
                                    ->placeholder('0'),
                            ]),
                            
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('sick_days')
                                    ->label('Sick Days')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('days')
                                    ->helperText('Cuts meal & transport budget'),
                                    
                                Forms\Components\TextInput::make('break_days')
                                    ->label('Break/Leave Days')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('days')
                                    ->helperText('Cuts meal & transport budget'),
                                    
                                Forms\Components\TextInput::make('late_days')
                                    ->label('Late Days')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('days')
                                    ->helperText('Cuts meal budget only'),
                            ]),
                            
                        Forms\Components\Textarea::make('reduction_notes')
                            ->label('Reduction Notes')
                            ->placeholder('Optional notes about reductions')
                            ->columnSpanFull()
                            ->rows(2),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('base_salary')
                    ->label('Base Salary')
                    ->money('IDR')
                    ->formatStateUsing(fn ($state) => 'IDR '. number_format($state, 0, ',', '.'))
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('calculation_period_start')
                    ->label('Period Start')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('calculation_period_end')
                    ->label('Period End')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_salary')
                    ->label('Total Salary')
                    ->money('IDR')
                    ->getStateUsing(function (SalaryPackage $record) {
                        $calculation = $record->getTotalSalaryEstimation();
                        return is_array($calculation) ? ($calculation['total_salary'] ?? 0) : $calculation;
                    })
                    ->formatStateUsing(fn ($state) => 'IDR '. number_format($state, 0, ',', '.')),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('viewCalculation')
                    ->label('View Calculation')
                    ->icon('heroicon-o-calculator')
                    ->color('info')
                    ->modalHeading(fn (SalaryPackage $record) => "Salary Calculation: {$record->name}")
                    ->modalContent(function (SalaryPackage $record) {
                        $calculation = $record->getTotalSalaryEstimation();
                        $hourlyRate = $record->getOvertimeHourlyRate();
                        
                        return view('filament.salary-calculation', [
                            'record' => $record,
                            'calculation' => $calculation,
                            'hourlyRate' => $hourlyRate,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ManageSalaryPackages::route('/'),
        ];
    }
}
