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

                                        <!-- Revenue Trend Chart -->
                                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Revenue Trends</h2>
                        <div class="h-80">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>

                    <!-- Sales by Location Chart -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6 mb-6">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Sales by Location</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="h-80">
                                <canvas id="locationPieChart"></canvas>
                            </div>
                            <div class="h-80">
                                <canvas id="locationBarChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Trend Table -->

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Revenue Trend Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($revenue_trend->pluck('date')->map(function($date) { 
                    return \Carbon\Carbon::parse($date)->format('M d'); 
                })) !!},
                datasets: [{
                    label: 'Revenue (LKR)',
                    data: {!! json_encode($revenue_trend->pluck('revenue')) !!},
                    borderColor: '#4F46E5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'LKR ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Location Sales Charts
        const locationData = {!! json_encode($location_sales->map(function($item) {
            return [
                'location' => $item['location'],
                'revenue' => $item['total'],
                'orders' => $item['count']
            ];
        })) !!};

        // Pie Chart for Revenue Distribution
        const pieCtx = document.getElementById('locationPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: locationData.map(item => item.location),
                datasets: [{
                    data: locationData.map(item => item.revenue),
                    backgroundColor: [
                        '#4F46E5', '#10B981', '#F59E0B', '#EF4444',
                        '#6366F1', '#8B5CF6', '#EC4899', '#14B8A6'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    title: {
                        display: true,
                        text: 'Revenue Distribution by Location'
                    }
                }
            }
        });

        // Bar Chart for Orders by Location
        const barCtx = document.getElementById('locationBarChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: locationData.map(item => item.location),
                datasets: [{
                    label: 'Number of Orders',
                    data: locationData.map(item => item.orders),
                    backgroundColor: '#4F46E5',
                    borderColor: '#4338CA',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Orders by Location'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
    @endpush

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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ ucfirst($order->type) }} #{{ $order->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $order->user ? $order->user->name : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        LKR {{ number_format($order->price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $order->updated_at->format('M d, Y') }}
                                    </td>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Set default font color based on theme
        const isDarkMode = document.documentElement.classList.contains('dark');
        const fontColor = isDarkMode ? '#D1D5DB' : '#374151';
        
        Chart.defaults.color = fontColor;
        Chart.defaults.borderColor = isDarkMode ? '#374151' : '#E5E7EB';

        // Revenue Trend Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($revenue_trend->pluck('date')->map(function($date) {
                    return \Carbon\Carbon::parse($date)->format('M d');
                })) !!},
                datasets: [{
                    label: 'Revenue (LKR)',
                    data: {!! json_encode($revenue_trend->pluck('revenue')) !!},
                    borderColor: '#4F46E5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'LKR ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Location Bar Chart
        const locationCtx = document.getElementById('locationChart').getContext('2d');
        new Chart(locationCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($location_sales->pluck('location')) !!},
                datasets: [{
                    label: 'Revenue',
                    data: {!! json_encode($location_sales->pluck('total')) !!},
                    backgroundColor: '#4F46E5',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Revenue by Location'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'LKR ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Location Pie Chart
        const locationPieCtx = document.getElementById('locationPieChart').getContext('2d');
        new Chart(locationPieCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($location_sales->pluck('location')) !!},
                datasets: [{
                    data: {!! json_encode($location_sales->pluck('count')) !!},
                    backgroundColor: [
                        '#4F46E5', '#7C3AED', '#EC4899', '#F59E0B', '#10B981',
                        '#6366F1', '#8B5CF6', '#F43F5E', '#FBBF24', '#34D399'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    title: {
                        display: true,
                        text: 'Order Distribution'
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>