<div class="space-y-6">
    <!-- Salary Package Info -->
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
        <h4 class="text-base text-center font-semibold text-indigo-900 dark:text-indigo-100 mb-3">Salary Information</h4>
        <br>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-4">
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-indigo-800 dark:text-indigo-200">Base Salary :</span>
                        <span class="text-sm font-medium text-indigo-900 dark:text-indigo-100">IDR {{ number_format($record->base_salary, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-indigo-800 dark:text-indigo-200">Overtime Hourly Rate :</span>
                        <span class="text-sm font-medium text-indigo-900 dark:text-indigo-100">IDR {{ number_format($hourlyRate, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
    
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Period :</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $record->calculation_period_start->format('d M Y') }} - {{ $record->calculation_period_end->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Work Days :</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $calculation['work_days'] }} days</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary Components Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Fixed Components -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
            <h4 class="text-base font-semibold text-blue-900 dark:text-blue-100 mb-3 text-center">Fixed</h4><br>
            @forelse($record->fixedComponents as $component)
            <div class="flex justify-between items-center py-1">
                <span class="text-sm text-blue-800 dark:text-blue-200">{{ $component->name }}</span>
                <span class="text-sm font-medium text-blue-900 dark:text-blue-100">IDR {{ number_format($component->amount, 0, ',', '.') }}</span>
            </div>
            @empty
            <p class="text-sm text-blue-600 dark:text-blue-300 italic">No fixed components</p>
            @endforelse
            <div class="border-t border-blue-200 dark:border-blue-700 mt-2 pt-2">
                <div class="flex justify-between items-center font-semibold">
                    <span class="text-blue-900 dark:text-blue-100">Total :</span>
                    <span class="text-blue-900 dark:text-blue-100">IDR {{ number_format($calculation['fixed_components'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Relative Components -->
        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
            <h4 class="text-base font-semibold text-green-900 dark:text-green-100 mb-3 text-center">Relative</h4><br>
            @forelse($record->relativeComponents as $component)
                <div class="flex justify-between items-center py-1">
                    <span class="text-sm text-green-800 dark:text-green-200">{{ $component->name }} ({{ $calculation['work_days'] }} days)</span>
                    <span class="text-sm font-medium text-green-900 dark:text-green-100">IDR {{ number_format($component->amount * $calculation['work_days'], 0, ',', '.') }}</span>
                </div>
            @empty
            <p class="text-sm text-green-600 dark:text-green-300 italic">No relative components</p>
            @endforelse
            <div class="border-t border-green-200 dark:border-green-700 mt-2 pt-2">
                <div class="flex justify-between items-center font-semibold">
                    <span class="text-green-900 dark:text-green-100">Total :</span>
                    <span class="text-green-900 dark:text-green-100">IDR {{ number_format($calculation['relative_components'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Overtime & Bonuses -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Overtime -->
        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4">
            <h4 class="text-base font-semibold text-orange-900 dark:text-orange-100 mb-3 text-center">Overtime Summary</h4><br>
            @if($record->overtimes->count() > 0)
                @php
    $overtimesByType = $record->overtimes->groupBy('type');
                @endphp
                
                @foreach($overtimesByType as $type => $overtimes)
                    @php
        $totalHours = $overtimes->sum(fn($ot) => $ot->getTotalHours());
        $totalPayment = $overtimes->sum(fn($ot) => $ot->calculateOvertimePayment());
        $count = $overtimes->count();
                    @endphp
                    <div class="flex justify-between items-center py-1">
                        <span class="text-sm text-orange-800 dark:text-orange-200">
                            {{ ucfirst($type) }} Overtime
                            <span class="text-xs text-orange-600 dark:text-orange-400">({{ $count }}x, {{ number_format($totalHours, 2) }}h total)</span>
                        </span>
                        <span class="text-sm font-medium text-orange-900 dark:text-orange-100">IDR {{ number_format($totalPayment, 0, ',', '.') }}</span>
                    </div>
                @endforeach
            @else
                <p class="text-sm text-orange-600 dark:text-orange-300 italic">No overtime recorded</p>
            @endif
            <div class="border-t border-orange-200 dark:border-orange-700 mt-2 pt-2">
                <div class="flex justify-between items-center font-semibold">
                    <span class="text-orange-900 dark:text-orange-100">Total :</span>
                    <span class="text-orange-900 dark:text-orange-100">IDR {{ number_format($calculation['overtime_amount'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Weekend Overtime Bonus -->
        <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
            <h4 class="text-base font-semibold text-purple-900 dark:text-purple-100 mb-3 text-center">Weekend Overtime Bonus</h4><br>
            @if($calculation['weekend_overtime_bonus'] > 0)
                <p class="text-sm text-purple-800 dark:text-purple-200 mb-2">
                    @php
    $weekendOvertimes = $record->overtimes->where('type', 'weekend');
    $weekendOvertimeCount = $weekendOvertimes->count();
    $weekendOvertimeHours = $weekendOvertimes->sum(fn($ot) => $ot->getTotalHours());
                    @endphp
                    {{ $weekendOvertimeCount }}x Overtime ({{ number_format($weekendOvertimeHours, 2) }}h total)
                </p>
                <div class="flex justify-between items-center font-semibold">
                    <span class="text-purple-900 dark:text-purple-100">Total :</span>
                    <span class="text-purple-900 dark:text-purple-100">IDR {{ number_format($calculation['weekend_overtime_bonus'], 0, ',', '.') }}</span>
                </div>
            @else
                <p class="text-sm text-purple-600 dark:text-purple-300 italic">No weekend overtime bonus</p>
            @endif
        </div>
    </div>

    <!-- Salary Reductions -->
    @if($calculation['total_reductions'] > 0)
        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
            <h4 class="text-base font-semibold text-red-900 dark:text-red-100 mb-3 text-center">Salary Reductions</h4>
            <br>
            @if($calculation['bpjs_reduction'] > 0)
            <div class="flex justify-between items-center py-1">
                <span class="text-sm text-red-800 dark:text-red-200">BPJSTK</span>
                <span class="text-sm font-medium text-red-900 dark:text-red-100">-IDR {{ number_format($calculation['bpjs_reduction'], 0, ',', '.') }}</span>
            </div>
            @endif

            @if($calculation['pph21_reduction'] > 0)
            <div class="flex justify-between items-center py-1">
                <span class="text-sm text-red-800 dark:text-red-200">PPh 21 Employee</span>
                <span class="text-sm font-medium text-red-900 dark:text-red-100">-IDR {{ number_format($calculation['pph21_reduction'], 0, ',', '.') }}</span>
            </div>
            @endif
            
            @if($calculation['jamsostek_reduction'] > 0)
            <div class="flex justify-between items-center py-1">
                <span class="text-sm text-red-800 dark:text-red-200">Jamsostek</span>
                <span class="text-sm font-medium text-red-900 dark:text-red-100">-IDR {{ number_format($calculation['jamsostek_reduction'], 0, ',', '.') }}</span>
            </div>
            @endif

            @if($calculation['meal_budget_cuts'] > 0)
            <div class="flex justify-between items-center py-1">
                <span class="text-sm text-red-800 dark:text-red-200">
                    Meal Budget Cuts
                    <span class="text-xs text-red-600 dark:text-red-400">
                        ({{ $record->sick_days + $record->break_days + $record->late_days }} days)
                    </span>
                </span>
                <span class="text-sm font-medium text-red-900 dark:text-red-100">-IDR {{ number_format($calculation['meal_budget_cuts'], 0, ',', '.') }}</span>
            </div>
            @endif

            @if($calculation['transport_budget_cuts'] > 0)
            <div class="flex justify-between items-center py-1">
                <span class="text-sm text-red-800 dark:text-red-200">
                    Transport Budget Cuts
                    <span class="text-xs text-red-600 dark:text-red-400">
                        ({{ $record->sick_days + $record->break_days }} days)
                    </span>
                </span>
                <span class="text-sm font-medium text-red-900 dark:text-red-100">-IDR {{ number_format($calculation['transport_budget_cuts'], 0, ',', '.') }}</span>
            </div>
            @endif

            <div class="border-t border-red-200 dark:border-red-700 mt-2 pt-2">
                <div class="flex justify-between items-center font-semibold">
                    <span class="text-red-900 dark:text-red-100">Total Reductions:</span>
                    <span class="text-red-900 dark:text-red-100">-IDR {{ number_format($calculation['total_reductions'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    @endif

    <!-- Total Salary -->
    <div class="bg-gray-900 dark:bg-gray-700 rounded-lg p-6">
        <div class="text-center">
            <h3 class="text-xl font-bold text-white mb-2">Total Salary Estimation</h3>
            <p class="text-3xl font-bold text-green-400">IDR {{ number_format($calculation['total_salary'], 0, ',', '.') }}</p>
        </div>
    </div>

    <!-- Disnaker Rules Info -->
    <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
        <h4 class="text-base font-semibold text-yellow-900 dark:text-yellow-100 mb-2">Indonesian Disnaker Overtime Rules</h4>
        <div class="text-sm text-yellow-800 dark:text-yellow-200 space-y-1">
            <p><strong>Weekday Overtime:</strong> 1.5x (1st hour), 2x (additional hours)</p>
            <p><strong>Weekend Overtime:</strong> 2x (1-7 hours), 3x (8th hour), 4x (9+ hours)</p>
            <p><strong>Hourly Rate Formula:</strong> Base Salary ÷ 173</p>
            <p><strong>Weekend Bonus:</strong> Additional meal & transport allowance for each weekend overtime day</p>
        </div>
    </div>
</div>
