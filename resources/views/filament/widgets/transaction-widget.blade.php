<div class="fi-wi-transaction bg-white rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="fi-wi-transaction-header flex items-center justify-between gap-3 px-6 py-4">
        <h3 class="fi-wi-transaction-heading text-base font-semibold leading-6 text-gray-950 dark:text-white">
            Pocket Overview - {{ $this->getLatestBudgetName() }}
        </h3>
    </div>

    <div class="fi-wi-transaction-content p-6 pt-0">
        @if ($this->getPocketCards()->isEmpty())
            <div class="text-center py-12">
                <div
                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                    <x-heroicon-o-wallet class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                </div>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No Pockets Found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a budget with pockets.
                </p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($this->getPocketCards() as $pocket)
                    <div
                        class="relative overflow-hidden rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                        <!-- Status indicator -->
                        <div class="absolute right-2 top-2">
                            @if ($pocket['status'] === 'danger')
                                <div class="h-3 w-3 rounded-full bg-red-500"></div>
                            @elseif($pocket['status'] === 'warning')
                                <div class="h-3 w-3 rounded-full bg-yellow-500"></div>
                            @else
                                <div class="h-3 w-3 rounded-full bg-green-500"></div>
                            @endif
                        </div>

                        <!-- Pocket name -->
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white pr-6">
                            {{ $pocket['name'] }}
                        </h4>

                        <!-- Amounts -->
                        <div class="mt-3 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Allocated:</span>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    ${{ number_format($pocket['allocated_amount'], 2) }}
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Balance:</span>
                                <span
                                    class="font-medium {{ $pocket['balance'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                    ${{ number_format($pocket['balance'], 2) }}
                                </span>
                            </div>
                        </div>

                        <!-- Progress bar -->
                        <div class="mt-4">
                            <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400 mb-1">
                                <span>Spent</span>
                                <span>{{ number_format($pocket['spent_percentage'], 1) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                <div class="h-2 rounded-full transition-all duration-300 {{ $pocket['spent_percentage'] > 100 ? 'bg-red-500' : ($pocket['spent_percentage'] > 80 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                    style="width: {{ min(100, $pocket['spent_percentage']) }}%"></div>
                            </div>
                        </div>

                        <!-- Quick action link -->
                        <div class="mt-4">
                            <a href="{{ route('filament.admin.resources.transactions.index', ['tableFilters[budget_pocket_id][value]' => $pocket['id']]) }}"
                                class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium">
                                View Transactions →
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Summary stats -->
            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                    <div class="text-sm font-medium text-blue-800 dark:text-blue-200">Total Allocated</div>
                    <div class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                        ${{ number_format($this->getPocketCards()->sum('allocated_amount'), 2) }}
                    </div>
                </div>

                <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                    <div class="text-sm font-medium text-green-800 dark:text-green-200">Total Balance</div>
                    <div class="text-2xl font-bold text-green-900 dark:text-green-100">
                        ${{ number_format($this->getPocketCards()->sum('balance'), 2) }}
                    </div>
                </div>

                <div class="rounded-lg bg-purple-50 p-4 dark:bg-purple-900/20">
                    <div class="text-sm font-medium text-purple-800 dark:text-purple-200">Active Pockets</div>
                    <div class="text-2xl font-bold text-purple-900 dark:text-purple-100">
                        {{ $this->getPocketCards()->count() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
