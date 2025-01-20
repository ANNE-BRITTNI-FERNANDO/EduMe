<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                Sales & Revenue Report
            </h2>
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ $start_date->format('M d, Y') }} - {{ $end_date->format('M d, Y') }}
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Total Revenue -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Revenue</h3>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">LKR {{ number_format($total_revenue, 2) }}</p>
                        <p class="mt-2 text-sm {{ $revenue_growth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $revenue_growth >= 0 ? '+' : '' }}{{ number_format($revenue_growth, 1) }}%
                            <span class="text-gray-600 dark:text-gray-400">vs previous period</span>
                        </p>
                    </div>
                </div>

                <!-- Total Orders -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Orders</h3>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($total_orders) }}</p>
                        <p class="mt-2 text-sm {{ $orders_growth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $orders_growth >= 0 ? '+' : '' }}{{ number_format($orders_growth, 1) }}%
                            <span class="text-gray-600 dark:text-gray-400">vs previous period</span>
                        </p>
                    </div>
                </div>

                <!-- Average Order Value -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Average Order Value</h3>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">
                            LKR {{ $total_orders > 0 ? number_format($total_revenue / $total_orders, 2) : '0.00' }}
                        </p>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            Calculated from {{ number_format($total_orders) }} orders
                        </p>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Revenue Trend -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Revenue Trend</h3>
                    <div id="revenue-trend-chart"></div>
                </div>

                <!-- Category Sales -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sales by Category</h3>
                    <div id="category-sales-chart"></div>
                </div>

                <!-- Payment Methods -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Payment Methods</h3>
                    <div id="payment-methods-chart"></div>
                </div>

                <!-- Daily Sales Distribution -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Daily Sales Distribution</h3>
                    <div id="daily-sales-chart"></div>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Orders</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Order ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Customer</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($recent_orders as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $order->order_number }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $order->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">LKR {{ number_format($order->total_amount, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($order->status === 'completed') bg-green-100 text-green-800 
                                            @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $order->created_at->format('M d, Y') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.min.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Debug data
            console.log('Revenue Data:', @json($revenue_trend));
            console.log('Category Data:', @json($category_sales));
            console.log('Payment Data:', @json($payment_methods));
            console.log('Daily Data:', @json($daily_sales));

            // Check if elements exist
            console.log('Revenue Chart Element:', document.querySelector("#revenue-trend-chart"));
            console.log('Category Chart Element:', document.querySelector("#category-sales-chart"));
            console.log('Payment Chart Element:', document.querySelector("#payment-methods-chart"));
            console.log('Daily Chart Element:', document.querySelector("#daily-sales-chart"));

            // Only initialize charts if we have data
            if (@json($revenue_trend->count()) > 0) {
                const revenueData = @json($revenue_trend->pluck('revenue'));
                const revenueDates = @json($revenue_trend->pluck('date'));
                
                const revenueOptions = {
                    series: [{
                        name: 'Revenue',
                        data: revenueData
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
                        categories: revenueDates
                    },
                    yaxis: {
                        labels: {
                            formatter: function(value) {
                                return 'LKR ' + value.toFixed(2);
                            }
                        }
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

                const revenueChart = new ApexCharts(document.querySelector("#revenue-trend-chart"), revenueOptions);
                revenueChart.render();
            } else {
                document.querySelector("#revenue-trend-chart").innerHTML = '<div class="text-center py-4 text-gray-500">No revenue data available for the selected period</div>';
            }

            if (@json($category_sales->count()) > 0) {
                const categoryData = @json($category_sales->pluck('total'));
                const categoryLabels = @json($category_sales->pluck('category'));
                
                const categoryOptions = {
                    series: categoryData,
                    chart: {
                        type: 'pie',
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

                const categoryChart = new ApexCharts(document.querySelector("#category-sales-chart"), categoryOptions);
                categoryChart.render();
            } else {
                document.querySelector("#category-sales-chart").innerHTML = '<div class="text-center py-4 text-gray-500">No category data available for the selected period</div>';
            }

            if (@json($payment_methods->count()) > 0) {
                const paymentData = @json($payment_methods->pluck('count'));
                const paymentLabels = @json($payment_methods->pluck('payment_method'));
                
                const paymentOptions = {
                    series: paymentData,
                    chart: {
                        type: 'donut',
                        height: 350
                    },
                    labels: paymentLabels,
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

                const paymentChart = new ApexCharts(document.querySelector("#payment-methods-chart"), paymentOptions);
                paymentChart.render();
            } else {
                document.querySelector("#payment-methods-chart").innerHTML = '<div class="text-center py-4 text-gray-500">No payment method data available for the selected period</div>';
            }

            if (@json($daily_sales->count()) > 0) {
                const dailyData = @json($daily_sales->pluck('count'));
                const dailyLabels = @json($daily_sales->pluck('day'));
                
                const dailyOptions = {
                    series: [{
                        name: 'Orders',
                        data: dailyData
                    }],
                    chart: {
                        type: 'bar',
                        height: 350
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 4,
                            horizontal: false,
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: dailyLabels
                    }
                };

                const dailyChart = new ApexCharts(document.querySelector("#daily-sales-chart"), dailyOptions);
                dailyChart.render();
            } else {
                document.querySelector("#daily-sales-chart").innerHTML = '<div class="text-center py-4 text-gray-500">No daily sales data available for the selected period</div>';
            }
        });
    </script>
@endpush
