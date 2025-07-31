<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Pocket Overview - {{ $this->getLatestBudgetName() }}
        </x-slot>

        @if($this->getPocketCards()->isEmpty())
            <div class="text-center py-12">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                    <x-heroicon-o-wallet class="h-6 w-6 text-gray-600 dark:text-gray-400" />
                </div>
                <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No Pockets Found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a budget with pockets.</p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($this->getPocketCards() as $pocket)
                    <div class="relative overflow-hidden rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                        <!-- Status indicator -->
                        <div class="absolute right-3 top-3">
                            @if($pocket['status'] === 'danger')
                                <div class="h-3 w-3 rounded-full bg-red-500" title="Overspent"></div>
                            @elseif($pocket['status'] === 'warning')
                                <div class="h-3 w-3 rounded-full bg-yellow-500" title="Low balance"></div>
                            @else
                                <div class="h-3 w-3 rounded-full bg-green-500" title="Good balance"></div>
                            @endif
                        </div>

                        <!-- Pocket name -->
                        <h4 class="text-lg text-center font-semibold text-gray-900 dark:text-white pr-6 mb-4">
                            {{ $pocket['name'] }}<br>
                        </h4>

                        <!-- Amounts -->
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Allocated:</span>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    IDR {{ number_format($pocket['allocated_amount'], 0, ',', '.') }}
                                </span>
                            </div>
                            
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Spent:</span>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    IDR {{ number_format($pocket['spent'], 0, ',', '.') }}
                                </span>
                            </div>
                            
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Balance:</span>
                                <span class="font-medium {{ $pocket['balance'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                    IDR {{ number_format($pocket['balance'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <!-- Progress bar -->
                        <div class="mb-4">
                            <div class="flex justify-between items-center text-xs text-gray-600 dark:text-gray-400 mb-2">
                                <span>Spent</span>
                                <span>{{ number_format($pocket['spent_percentage'], 1) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                <div class="h-2 rounded-full transition-all duration-300 {{ $pocket['spent_percentage'] > 100 ? 'bg-red-500' : ($pocket['spent_percentage'] > 80 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                     style="width: {{ min(100, $pocket['spent_percentage']) }}%"></div>
                            </div>
                        </div>

                        <!-- Quick actions -->
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between gap-2">
                                <a href="{{ route('filament.dashboard.resources.transactions.index', ['tableFilters' => ['budget_pocket_id' => ['value' => $pocket['id']]]]) }}" 
                                   class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-medium flex items-center gap-1">
                                    <x-heroicon-o-eye class="h-3 w-3" />
                                    View
                                </a>
                                
                                <a href="{{ route('filament.dashboard.resources.transactions.index', ['action' => 'create','budget_pocket_id' => $pocket['id']]) }}" 
                                   class="inline-flex items-center gap-1 rounded-md bg-blue-600 px-2 py-1 text-xs font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition-colors">
                                    <x-heroicon-o-plus class="h-3 w-3" />
                                    New
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
