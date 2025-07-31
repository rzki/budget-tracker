<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OvertimeResource\Pages;
use App\Models\Overtime;
use App\Models\SalaryPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class OvertimeResource extends Resource
{
    protected static ?string $model = Overtime::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Salary';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('overtimeId')
                    ->default(fn () => (string) Str::uuid7()),
                    
                Forms\Components\Select::make('salary_package_id')
                    ->label('Salary Package')
                    ->relationship('salaryPackage', 'name')
                    ->default(fn () => SalaryPackage::orderByDesc('id')->first()?->id)
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                    
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Overtime Type')
                            ->required()
                            ->options([
                                'weekday' => 'Weekday Overtime',
                                'weekend' => 'Weekend Overtime',
                            ])
                            ->native(false)
                            ->reactive(),
                            
                        Forms\Components\TimePicker::make('start_time')
                            ->label('Start Time')
                            ->required()
                            ->seconds()
                            ->reactive(),
                            
                        Forms\Components\TimePicker::make('end_time')
                            ->label('End Time')
                            ->required()
                            ->seconds()
                            ->reactive()
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        $form = request()->input();
                                        $type = $form['type'] ?? null;
                                        $startTime = $form['start_time'] ?? null;
                                        $endTime = $value;
                                        
                                        if ($type === 'weekend' && $startTime && $endTime) {
                                            $start = \Carbon\Carbon::createFromFormat('H:i:s', $startTime);
                                            $end = \Carbon\Carbon::createFromFormat('H:i:s', $endTime);
                                            
                                            if ($end->lt($start)) {
                                                $end->addDay();
                                            }
                                            
                                            $hours = $start->diffInSeconds($end) / 3600; // More precise calculation with seconds
                                            
                                            if ($hours < 4) {
                                                $fail('Weekend overtime requires minimum 4 hours (3 hours after break deduction).');
                                            }
                                        }
                                    };
                                },
                            ]),
                    ]),
                    
                Forms\Components\DatePicker::make('overtime_date')
                    ->label('Overtime Date')
                    ->required()
                    ->default(now())
                    ->columnSpanFull(),
                    
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->placeholder('Optional description of overtime work')
                    ->columnSpanFull(),
                    
                Forms\Components\Placeholder::make('overtime_rules')
                    ->label('Overtime Rules')
                    ->content(fn (Forms\Get $get) => match ($get('type')) {
                        'weekday' => '• Hours calculated from start to end time
• 1.5x hourly rate for 1st hour
• 2x hourly rate for additional hours',
                        'weekend' => '• Minimum 4 hours required for weekend overtime
• Hours calculated from start to end time (minus 1 hour break)
• 2x hourly rate for hours 1-7
• 3x hourly rate for 8th hour  
• 4x hourly rate for 9+ hours
• Additional meal & transport allowance',
                        default => 'Select overtime type to see payment rules'
                    })
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('salaryPackage.name')
                    ->label('Salary Package')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'weekday',
                        'warning' => 'weekend',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'weekday' => 'Weekday',
                        'weekend' => 'Weekend',
                    }),
                    
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Start Time')
                    ->time('H:i:s')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('end_time')
                    ->label('End Time')
                    ->time('H:i:s')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('calculated_hours')
                    ->label('Hours')
                    ->getStateUsing(fn (Overtime $record): string => number_format($record->getTotalHours(), 2))
                    ->sortable(false),
                    
                Tables\Columns\TextColumn::make('overtime_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('salaryPackage.base_salary')
                    ->label('Base Salary')
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('calculated_payment')
                    ->label('Overtime Payment')
                    ->money('IDR')
                    ->state(fn (Overtime $record): float => $record->calculateOvertimePayment()),
                    
                Tables\Columns\TextColumn::make('description')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'weekday' => 'Weekday',
                        'weekend' => 'Weekend',
                    ]),
                    
                Tables\Filters\SelectFilter::make('salary_package_id')
                    ->label('Salary Package')
                    ->relationship('salaryPackage', 'name'),
                    
                Tables\Filters\Filter::make('overtime_date')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Until Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('overtime_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('overtime_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('overtime_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageOvertimes::route('/'),
        ];
    }
}
