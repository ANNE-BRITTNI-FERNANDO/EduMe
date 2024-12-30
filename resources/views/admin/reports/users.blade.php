<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                User Analytics Report
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Total Users -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Users</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($total_users) }}
                                </p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Users -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">New Users</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($new_users) }}
                                </p>
                            </div>
                            <div class="p-3 bg-green-100 rounded-full">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="text-sm font-medium {{ $user_growth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $user_growth >= 0 ? '+' : '' }}{{ number_format($user_growth, 1) }}%
                            </span>
                            <span class="text-sm text-gray-500"> vs previous period</span>
                        </div>
                    </div>
                </div>

                <!-- Active Users -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Active Users</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($active_users) }}
                                </p>
                            </div>
                            <div class="p-3 bg-purple-100 rounded-full">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="text-sm font-medium {{ $active_users_growth >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $active_users_growth >= 0 ? '+' : '' }}{{ number_format($active_users_growth, 1) }}%
                            </span>
                            <span class="text-sm text-gray-500"> vs previous period</span>
                        </div>
                    </div>
                </div>
            </div>

                        <!-- Charts Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                            <!-- Daily New Users -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">New Users Trend</h3>
                                <div id="daily-users-chart"></div>
                            </div>
            
                            <!-- User Engagement -->
                            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">User Engagement</h3>
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Engagement Rate</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($engagement_rate, 1) }}%</p>
                                        <p class="text-sm text-gray-500">of total users made a purchase</p>
                                    </div>
                                    <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Active Users</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($total_active) }}</p>
                                        <p class="text-sm text-gray-500">users who made at least one purchase</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">Recent Activity</p>
                                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($period_active) }}</p>
                                        <p class="text-sm text-gray-500">users active in selected period</p>
                                    </div>
                                </div>
                            </div>
                        </div>
            
                        @push('scripts')
                        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.41.0/dist/apexcharts.min.js"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                // Daily New Users Chart
                                if (@json($daily_new_users->count()) > 0) {
                                    const dailyData = @json($daily_new_users->pluck('count'));
                                    const dailyDates = @json($daily_new_users->pluck('date'));
                                    
                                    const dailyOptions = {
                                        series: [{
                                            name: 'New Users',
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
            
                                    const dailyChart = new ApexCharts(document.querySelector("#daily-users-chart"), dailyOptions);
                                    dailyChart.render();
                                } else {
                                    document.querySelector("#daily-users-chart").innerHTML = '<div class="text-center py-4 text-gray-500">No data available for the selected period</div>';
                                }
                            });
                        </script>
                        @endpush

</x-app-layout>
