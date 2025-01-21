<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
            User Analytics Report
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Key Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Total Users -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</h3>
                                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($total_users) }}</p>
                            </div>
                            <div class="p-2 bg-blue-50 rounded-lg">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Users -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">New Users</h3>
                                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($new_users) }}</p>
                                <p class="mt-2 text-sm {{ $user_growth >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $user_growth >= 0 ? '+' : '' }}{{ number_format($user_growth, 1) }}% vs previous period
                                </p>
                            </div>
                            <div class="p-2 bg-green-50 rounded-lg">
                                <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Users -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Users</h3>
                                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($active_users) }}</p>
                                <p class="mt-2 text-sm {{ $active_users_growth >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                    {{ $active_users_growth >= 0 ? '+' : '' }}{{ number_format($active_users_growth, 1) }}% vs previous period
                                </p>
                            </div>
                            <div class="p-2 bg-purple-50 rounded-lg">
                                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Activity Breakdown -->
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Activity Breakdown</h4>
                            <div class="mt-3 space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Buyers</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($activity_breakdown['buyers']) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Sellers</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($activity_breakdown['sellers']) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Cart Users</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($activity_breakdown['cart_users']) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Rating Activity</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($activity_breakdown['raters']) }}</span>
                                </div>
                            </div>
                            <p class="mt-3 text-xs text-gray-500">
                                Note: Users may be counted in multiple categories if they performed different types of activities
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- New Users Trend -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">New Users Trend</h3>
                        <div class="h-72" id="daily-users-chart"></div>
                    </div>
                </div>

                <!-- User Engagement -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">User Engagement</h3>
                        <div class="space-y-6">
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Engagement Rate</h4>
                                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($engagement_rate, 1) }}%</p>
                                <p class="mt-1 text-sm text-gray-500">of total users engaged with the platform</p>
                            </div>
                            <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">Buyer/Seller Ratio</h4>
                                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($activity_breakdown['buyers']) }}/{{ number_format($activity_breakdown['sellers']) }}</p>
                                <p class="mt-1 text-sm text-gray-500">buyers to sellers on the platform</p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400">User Interactions</h4>
                                <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white">{{ number_format($activity_breakdown['cart_users'] + $activity_breakdown['raters']) }}</p>
                                <p class="mt-1 text-sm text-gray-500">users with cart or rating activity</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (@json($daily_new_users->count()) > 0) {
                const options = {
                    series: [{
                        name: 'New Users',
                        data: @json($daily_new_users->pluck('count'))
                    }],
                    chart: {
                        type: 'area',
                        height: '100%',
                        toolbar: {
                            show: false
                        },
                        fontFamily: 'inherit'
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    xaxis: {
                        type: 'datetime',
                        categories: @json($daily_new_users->pluck('date')),
                        labels: {
                            style: {
                                colors: '#6B7280',
                                fontSize: '12px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#6B7280',
                                fontSize: '12px'
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
                            stops: [0, 90, 100]
                        }
                    },
                    colors: ['#3B82F6']
                };

                const chart = new ApexCharts(document.querySelector("#daily-users-chart"), options);
                chart.render();
            } else {
                document.querySelector("#daily-users-chart").innerHTML = 
                    '<div class="flex items-center justify-center h-full text-gray-500">No data available for the selected period</div>';
            }
        });
    </script>
    @endpush
</x-app-layout>
