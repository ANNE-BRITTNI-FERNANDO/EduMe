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
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Products & Bundles</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($total_products + $total_bundles) }}</div>
                                        <div class="ml-2 text-sm text-gray-500">
                                            ({{ number_format($total_products) }} Products, {{ number_format($total_bundles) }} Bundles)
                                        </div>
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
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">New Items</dt>
                                    <dd class="flex flex-col">
                                        <div class="flex items-baseline">
                                            <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($new_products + $new_bundles) }}</div>
                                            <div class="ml-2 flex items-baseline text-sm font-semibold">
                                                <span class="text-gray-500">(Products: </span>
                                                <span class="{{ $products_growth >= 0 ? 'text-green-600' : 'text-red-600' }} mx-1">
                                                    {{ $products_growth >= 0 ? '+' : '' }}{{ number_format($products_growth, 1) }}%
                                                </span>
                                                <span class="text-gray-500">)</span>
                                            </div>
                                        </div>
                                        <div class="flex items-baseline mt-1">
                                            <span class="text-gray-500">Bundles: </span>
                                            <span class="{{ $bundles_growth >= 0 ? 'text-green-600' : 'text-red-600' }} ml-1">
                                                {{ $bundles_growth >= 0 ? '+' : '' }}{{ number_format($bundles_growth, 1) }}%
                                            </span>
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sold Items -->
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
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Sold Items</dt>
                                    <dd class="flex flex-col">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($sold_products + $sold_bundles) }}</div>
                                        <div class="text-sm text-gray-500">
                                            Products: {{ number_format($sold_products) }}<br>
                                            Bundles: {{ number_format($sold_bundles) }}
                                        </div>
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
                                    <dd class="flex flex-col">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                            LKR {{ number_format($total_product_revenue + $total_bundle_revenue, 2) }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Products: LKR {{ number_format($total_product_revenue, 2) }}<br>
                                            Bundles: LKR {{ number_format($total_bundle_revenue, 2) }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Daily New Items -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Daily New Items</h3>
                        <div id="daily-items-chart"></div>
                    </div>
                </div>

                <!-- Category Distribution -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Category Distribution</h3>
                        <div id="category-chart"></div>
                    </div>
                </div>
            </div>

            <!-- Recent Items -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Products -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Products</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Product</th>
                                        <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Seller</th>
                                        <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($recent_products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $product->product_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product->user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">LKR {{ number_format($product->price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $product->is_sold ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $product->is_sold ? 'Sold' : 'Available' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Bundles -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Bundles</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Bundle</th>
                                        <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Seller</th>
                                        <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($recent_bundles as $bundle)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $bundle->bundle_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $bundle->user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">LKR {{ number_format($bundle->price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $bundle->is_sold ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $bundle->is_sold ? 'Sold' : 'Available' }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
            <script>
                // Daily Items Chart
                var dailyItemsOptions = {
                    series: [{
                        name: 'Products',
                        data: @json($daily_new_products->pluck('count')),
                    }, {
                        name: 'Bundles',
                        data: @json($daily_new_bundles->pluck('count')),
                    }],
                    chart: {
                        type: 'line',
                        height: 350,
                        toolbar: {
                            show: false
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    xaxis: {
                        categories: @json($daily_new_products->pluck('date')),
                        labels: {
                            style: {
                                colors: '#6B7280'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#6B7280'
                            }
                        }
                    },
                    legend: {
                        position: 'top'
                    },
                    grid: {
                        borderColor: '#E5E7EB'
                    }
                };

                var dailyItemsChart = new ApexCharts(document.querySelector("#daily-items-chart"), dailyItemsOptions);
                dailyItemsChart.render();

                // Category Distribution Chart
                var categoryOptions = {
                    series: @json($category_distribution->pluck('count')),
                    labels: @json($category_distribution->pluck('category')),
                    chart: {
                        type: 'donut',
                        height: 350,
                        toolbar: {
                            show: false
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            colors: '#6B7280'
                        }
                    },
                    stroke: {
                        show: false
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            return opts.w.config.series[opts.seriesIndex] + ' (' + Math.round(val) + '%)'
                        }
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '50%'
                            }
                        }
                    },
                    colors: ['#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899']
                };

                var categoryChart = new ApexCharts(document.querySelector("#category-chart"), categoryOptions);
                categoryChart.render();

                // Date range picker functionality
                document.getElementById('period').addEventListener('change', function() {
                    var customInputs = document.getElementById('customDateInputs');
                    customInputs.style.display = this.value === 'custom' ? 'flex' : 'none';
                });
            </script>
            @endpush

</x-app-layout>
