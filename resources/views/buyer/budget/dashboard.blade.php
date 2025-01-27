<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Budget Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if($budgetTracking)
                <!-- Current Budget Overview -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Current Budget Overview</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Total Budget -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Total Budget</h4>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    LKR {{ number_format($budgetTracking->total_amount, 2) }}
                                </p>
                            </div>
                            <!-- Spent Amount -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Spent Amount</h4>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    LKR {{ number_format($budgetTracking->spent_amount, 2) }}
                                </p>
                            </div>
                            <!-- Remaining Amount -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Remaining Amount</h4>
                                <p class="text-2xl font-bold text-{{ $budgetTracking->getBudgetStatus() === 'critical' ? 'red' : ($budgetTracking->getBudgetStatus() === 'warning' ? 'yellow' : 'green') }}-500 dark:text-{{ $budgetTracking->getBudgetStatus() === 'critical' ? 'red' : ($budgetTracking->getBudgetStatus() === 'warning' ? 'yellow' : 'green') }}-400">
                                    LKR {{ number_format($budgetTracking->remaining_amount, 2) }}
                                </p>
                            </div>
                        </div>

                        <!-- Budget Progress Bar -->
                        <div class="mt-6">
                            @php
                                $percentageSpent = ($budgetTracking->spent_amount / $budgetTracking->total_amount) * 100;
                                $progressColor = $budgetTracking->getBudgetStatus() === 'critical' ? 'red' : ($budgetTracking->getBudgetStatus() === 'warning' ? 'yellow' : 'green');
                            @endphp
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Budget Utilization</span>
                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ number_format($percentageSpent, 1) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                <div class="bg-{{ $progressColor }}-500 h-2.5 rounded-full" style="width: {{ $percentageSpent }}%"></div>
                            </div>
                        </div>

                        <!-- Cycle Information -->
                        <div class="mt-6 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Cycle Period</h4>
                                    <p class="text-gray-900 dark:text-gray-100">
                                        {{ $budgetTracking->cycle_start_date->format('M d, Y') }} - {{ $budgetTracking->cycle_end_date->format('M d, Y') }}
                                    </p>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Next Modification</h4>
                                    <p class="text-gray-900 dark:text-gray-100">
                                        {{ $budgetTracking->canModifyBudget() ? 'Available Now' : 'In ' . $budgetTracking->getTimeUntilNextModification() }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Budget Form -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        {{ $budgetTracking ? 'Update Budget' : 'Set Budget' }}
                    </h3>
                    
                    @if($budgetTracking && !$budgetTracking->canModifyBudget())
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-4">
                            <span class="block sm:inline">You can modify your budget again in {{ $budgetTracking->getTimeUntilNextModification() }}.</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ $budgetTracking ? route('buyer.budget.update') : route('buyer.budget.set') }}" class="space-y-6">
                        @csrf
                        @if($budgetTracking)
                            @method('PUT')
                        @endif

                        <div class="mb-4">
                            <label for="budget_amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Budget Amount (LKR)
                            </label>
                            <input
                                type="number"
                                name="budget_amount"
                                id="budget_amount"
                                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-indigo-500 focus:ring-indigo-500"
                                value="{{ old('budget_amount', $budgetTracking ? $budgetTracking->total_amount : '') }}"
                                min="100"
                                required
                            >
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Minimum amount: LKR 100</p>
                        </div>
                        <div>
                            <label for="cycle_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cycle Type</label>
                            <select name="cycle_type" id="cycle_type" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                                <option value="monthly" {{ old('cycle_type', $budgetTracking ? $budgetTracking->cycle_type : '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                <option value="yearly" {{ old('cycle_type', $budgetTracking ? $budgetTracking->cycle_type : '') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                            </select>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Choose between monthly or yearly budget cycles</p>
                        </div>
                        <button type="submit" 
                            class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                            {{ $budgetTracking && !$budgetTracking->canModifyBudget() ? 'disabled' : '' }}>
                            {{ $budgetTracking ? 'Update Budget' : 'Create Budget' }}
                        </button>
                    </form>
                </div>
            </div>

            @if($budgetTracking)
                <!-- Recommended Products Section -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <div class="space-y-4">
                            <!-- Filters Section -->
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Recommended Products Within Budget</h3>
                                <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
                                    <!-- Search Input -->
                                    <div class="flex-1 sm:flex-none">
                                        <form action="{{ route('buyer.budget.index') }}" method="GET" class="flex gap-2">
                                            @if(request('category'))
                                                <input type="hidden" name="category" value="{{ request('category') }}">
                                            @endif
                                            <input type="text" 
                                                name="search" 
                                                placeholder="Search products..." 
                                                value="{{ request('search') }}"
                                                class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 w-full sm:w-64">
                                            <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                                Search
                                            </button>
                                        </form>
                                    </div>
                                    <!-- Category Filter -->
                                    <div class="flex items-center">
                                        <label for="category-filter" class="sr-only">Filter by Category</label>
                                        <select id="category-filter" 
                                            name="category" 
                                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                            onchange="window.location.href = this.value ? '{{ route('buyer.budget.index') }}?category=' + encodeURIComponent(this.value) + '{{ request('search') ? '&search=' . request('search') : '' }}' : '{{ route('buyer.budget.index') }}{{ request('search') ? '?search=' . request('search') : '' }}'">
                                            <option value="">All Categories</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                                    {{ $category }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Active Filters -->
                            @if(request('category') || request('search'))
                                <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <span>Active filters:</span>
                                    @if(request('category'))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100">
                                            Category: {{ request('category') }}
                                            <a href="{{ route('buyer.budget.index') }}{{ request('search') ? '?search=' . request('search') : '' }}" class="ml-1 text-indigo-600 hover:text-indigo-500">×</a>
                                        </span>
                                    @endif
                                    @if(request('search'))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-800 dark:text-indigo-100">
                                            Search: {{ request('search') }}
                                            <a href="{{ route('buyer.budget.index') }}{{ request('category') ? '?category=' . request('category') : '' }}" class="ml-1 text-indigo-600 hover:text-indigo-500">×</a>
                                        </span>
                                    @endif
                                    <a href="{{ route('buyer.budget.index') }}" class="text-indigo-600 hover:text-indigo-500 text-xs">(Clear all)</a>
                                </div>
                            @endif

                            <!-- Results or Empty State -->
                            @if($recommendedProducts->isEmpty() && $recommendedBundles->isEmpty())
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-8 text-center">
                                    <div class="text-gray-500 dark:text-gray-400 space-y-3">
                                        @if(request('search') && request('category'))
                                            <p class="text-lg">No items found matching "{{ request('search') }}" in the "{{ request('category') }}" category within your budget of LKR {{ number_format($budgetTracking->remaining_amount, 2) }}.</p>
                                        @elseif(request('search'))
                                            <p class="text-lg">No items found matching "{{ request('search') }}" within your budget of LKR {{ number_format($budgetTracking->remaining_amount, 2) }}.</p>
                                        @elseif(request('category'))
                                            <p class="text-lg">No items found in the "{{ request('category') }}" category within your budget of LKR {{ number_format($budgetTracking->remaining_amount, 2) }}.</p>
                                        @else
                                            <p class="text-lg">No items found within your budget of LKR {{ number_format($budgetTracking->remaining_amount, 2) }}.</p>
                                        @endif
                                        <p>Try adjusting your filters or check back later for new items.</p>
                                    </div>
                                </div>
                            @else
                                <!-- Products Section -->
                                @if($recommendedProducts->isNotEmpty())
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recommended Products</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                                        @foreach($recommendedProducts as $product)
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                @if($product->image_path)
                                                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->product_name }}" class="w-full h-48 object-cover rounded-lg mb-4">
                                                @endif
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $product->product_name }}</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ Str::limit($product->description, 100) }}</p>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100">LKR {{ number_format($product->price, 2) }}</span>
                                                    <a href="{{ route('product.show', $product) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                        View Details
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-6 mb-8">
                                        {{ $recommendedProducts->links() }}
                                    </div>
                                @endif

                                <!-- Bundles Section -->
                                @if($recommendedBundles->isNotEmpty())
                                    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Recommended Bundles</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach($recommendedBundles as $bundle)
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                                @if($bundle->bundle_image)
                                                    <img src="{{ asset('storage/' . $bundle->bundle_image) }}" alt="{{ $bundle->bundle_name }}" class="w-full h-48 object-cover rounded-lg mb-4">
                                                @endif
                                                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $bundle->bundle_name }}</h3>
                                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">{{ Str::limit($bundle->description, 100) }}</p>
                                                <div class="flex justify-between items-center">
                                                    <span class="text-lg font-bold text-gray-900 dark:text-gray-100">LKR {{ number_format($bundle->price, 2) }}</span>
                                                    <a href="{{ route('bundles.show', $bundle) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                                        View Bundle
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-6">
                                        {{ $recommendedBundles->links() }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Budget History -->
            @if($budgetHistory->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-lg">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Budget History</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cycle</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Budget</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Spent Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Utilization</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($budgetHistory as $history)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ $history->cycle_start_date->format('M d, Y') }} - {{ $history->cycle_end_date->format('M d, Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                LKR {{ number_format($history->total_budget, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                LKR {{ number_format($history->spent_amount, 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                {{ number_format(($history->spent_amount / $history->total_budget) * 100, 1) }}%
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $history->status === 'active' ? 'bg-green-100 text-green-800' : 
                                                       ($history->status === 'completed' ? 'bg-gray-100 text-gray-800' : 
                                                       'bg-yellow-100 text-yellow-800') }}">
                                                    {{ ucfirst($history->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
