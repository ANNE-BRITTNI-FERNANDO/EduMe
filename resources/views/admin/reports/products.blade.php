<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Date Filter -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg mb-6">
                <div class="p-6">
                    <form action="{{ route('admin.reports.products') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                        <div>
                            <x-input-label for="period" value="Time Period" />
                            <select id="period" name="period" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="today" {{ request('period') === 'today' ? 'selected' : '' }}>Today</option>
                                <option value="yesterday" {{ request('period') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                                <option value="last7days" {{ request('period') === 'last7days' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="last30days" {{ request('period') === 'last30days' ? 'selected' : '' }}>Last 30 Days</option>
                                <option value="thisMonth" {{ request('period') === 'thisMonth' ? 'selected' : '' }}>This Month</option>
                                <option value="lastMonth" {{ request('period') === 'lastMonth' ? 'selected' : '' }}>Last Month</option>
                                <option value="custom" {{ request('period') === 'custom' ? 'selected' : '' }}>Custom Range</option>
                            </select>
                        </div>

                        <div id="customDateInputs" class="flex gap-4" style="{{ request('period') === 'custom' ? '' : 'display: none;' }}">
                            <div>
                                <x-input-label for="start_date" value="Start Date" />
                                <x-text-input id="start_date" type="date" name="start_date" class="mt-1 block" value="{{ request('start_date') }}" />
                            </div>
                            <div>
                                <x-input-label for="end_date" value="End Date" />
                                <x-text-input id="end_date" type="date" name="end_date" class="mt-1 block" value="{{ request('end_date') }}" />
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <x-primary-button>Filter</x-primary-button>
                            <a href="{{ route('admin.reports.download', ['type' => 'products']) }}?{{ http_build_query(request()->query()) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Download Report
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Total Products -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Products</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($total_products) }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Products -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">New Products</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($new_products) }}</div>
                                        <div class="ml-2 flex items-baseline text-sm font-semibold {{ $products_growth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $products_growth >= 0 ? '+' : '' }}{{ number_format($products_growth, 1) }}%
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sold Products -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Sold Products</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($sold_products) }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-pink-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Revenue</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">LKR {{ number_format($total_revenue, 2) }}</div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Daily New Products -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">New Products Trend</h3>
                    <div id="daily-products-chart"></div>
                </div>

                <!-- Category Distribution -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Category Distribution</h3>
                    <div id="category-distribution-chart"></div>
                </div>
            </div>

            <!-- Recent Products -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Products</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Listed By</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($recent_products as $product)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $product->product_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $product->category }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">LKR {{ number_format($product->price, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($product->is_sold)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sold</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Available</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $product->user->name }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show/hide custom date inputs based on period selection
            const periodSelect = document.getElementById('period');
            const customDateInputs = document.getElementById('customDateInputs');
            
            periodSelect.addEventListener('change', function() {
                customDateInputs.style.display = this.value === 'custom' ? 'flex' : 'none';
            });

            // Daily New Products Chart
            if (@json($daily_new_products->count()) > 0) {
                const dailyData = @json($daily_new_products->pluck('count'));
                const dailyDates = @json($daily_new_products->pluck('date'));
                
                const dailyOptions = {
                    series: [{
                        name: 'New Products',
                        data: dailyData
                    }],
                    chart: {
                        type: 'area',
                        height: 350,
                        toolbar: {
                            show: false
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth'
                    },
                    xaxis: {
                        type: 'datetime',
                        categories: dailyDates
                    },
                    tooltip: {
                        x: {
                            format: 'dd MMM yyyy'
                        }
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.7,
                            opacityTo: 0.9,
                            stops: [0, 100]
                        }
                    }
                };

                const dailyChart = new ApexCharts(document.querySelector("#daily-products-chart"), dailyOptions);
                dailyChart.render();
            } else {
                document.querySelector("#daily-products-chart").innerHTML = '<div class="text-center py-4 text-gray-500">No data available for the selected period</div>';
            }

            // Category Distribution Chart
            if (@json($category_distribution->count()) > 0) {
                const categoryData = @json($category_distribution->pluck('count'));
                const categoryLabels = @json($category_distribution->pluck('category'));
                
                const categoryOptions = {
                    series: categoryData,
                    chart: {
                        type: 'donut',
                        height: 350
                    },
                    labels: categoryLabels,
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };

                const categoryChart = new ApexCharts(document.querySelector("#category-distribution-chart"), categoryOptions);
                categoryChart.render();
            } else {
                document.querySelector("#category-distribution-chart").innerHTML = '<div class="text-center py-4 text-gray-500">No data available for the selected period</div>';
            }
        });
    </script>
    @endpush
</x-app-layout>
