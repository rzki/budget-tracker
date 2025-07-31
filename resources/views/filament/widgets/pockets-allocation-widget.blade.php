<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Pockets Allocation - {{ $this->getLatestBudgetName() }}
        </x-slot>

        @if($this->getPocketsWithAllocations()->isEmpty())
            <div class="text-center py-12">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                    <x-heroicon-o-wallet class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                </div>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No Pocket Allocations Found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a budget with pocket allocations to see the overview.</p>
            </div>
        @else
            <!-- Summary Stats -->
            <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
                @php $stats = $this->getSummaryStats(); @endphp
                
                <div class="rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
                    <div class="text-xs font-medium text-blue-800 dark:text-blue-200">Total Pockets</div>
                    <div class="text-lg font-bold text-blue-900 dark:text-blue-100">
                        {{ $stats['total_pockets'] }}
                    </div>
                </div>
                
                <div class="rounded-lg bg-green-50 p-3 dark:bg-green-900/20">
                    <div class="text-xs font-medium text-green-800 dark:text-green-200">Total Allocated</div>
                    <div class="text-lg font-bold text-green-900 dark:text-green-100">
                        IDR {{ number_format($stats['total_allocated'], 0, ',', '.') }}
                    </div>
                </div>
                
                <div class="rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
                    <div class="text-xs font-medium text-red-800 dark:text-red-200">Total Spent</div>
                    <div class="text-lg font-bold text-red-900 dark:text-red-100">
                        IDR {{ number_format($stats['total_spent'], 0, ',', '.') }}
                    </div>
                </div>
                
                <div class="rounded-lg bg-purple-50 p-3 dark:bg-purple-900/20">
                    <div class="text-xs font-medium text-purple-800 dark:text-purple-200">Total Balance</div>
                    <div class="text-lg font-bold text-purple-900 dark:text-purple-100">
                        IDR {{ number_format($stats['total_balance'], 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <!-- Pockets Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Pocket Name
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Allocated
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Spent
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Income
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Balance
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Utilization
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-400">
                                Transactions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                        @foreach($this->getPocketsWithAllocations() as $pocket)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $pocket['pocket_name'] }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        IDR {{ number_format($pocket['allocated_amount'], 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <div class="text-sm text-gray-900 dark:text-white">
                                        IDR {{ number_format($pocket['spent'], 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <div class="text-sm text-green-600 dark:text-green-400">
                                        IDR {{ number_format($pocket['income'], 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <div class="text-sm {{ $pocket['balance'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                        IDR {{ number_format($pocket['balance'], 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 dark:bg-gray-700 mr-2">
                                            <div class="h-2 rounded-full {{ $pocket['utilization_percentage'] > 100 ? 'bg-red-500' : ($pocket['utilization_percentage'] > 80 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                                 style="width: {{ min(100, $pocket['utilization_percentage']) }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-600 dark:text-gray-400">
                                            {{ number_format($pocket['utilization_percentage'], 1) }}%
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $pocket['transactions_count'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Quick Actions -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    Showing {{ $this->getPocketsWithAllocations()->count() }} pocket(s) from {{ $this->getLatestBudgetName() }}
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('filament.dashboard.resources.transactions.index') }}" 
                       class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <x-heroicon-o-plus class="h-4 w-4 mr-1" />
                        Add Transaction
                    </a>
                    <a href="{{ route('filament.dashboard.resources.budgets.index') }}" 
                       class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        <x-heroicon-o-cog-6-tooth class="h-4 w-4 mr-1" />
                        Manage Budgets
                    </a>
                </div>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
