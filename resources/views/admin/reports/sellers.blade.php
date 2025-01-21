<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <!-- Total Sellers -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Sellers</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($total_sellers) }}</div>
                                        <div class="ml-2 flex items-baseline text-sm font-semibold text-green-600 dark:text-green-400">
                                            {{ number_format($active_sellers) }} active
                                        </div>
                                    </dd>
                                    <dd class="mt-1 text-sm text-gray-500">
                                        {{ number_format(($active_sellers / $total_sellers) * 100, 1) }}% activity rate
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Sellers -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">New Sellers</dt>
                                    <dd class="flex items-baseline">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($new_sellers) }}</div>
                                        @if($sellers_growth != 0)
                                            <div class="ml-2 flex items-baseline text-sm font-semibold {{ $sellers_growth > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                <svg class="self-center flex-shrink-0 h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                    @if($sellers_growth > 0)
                                                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                    @else
                                                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                    @endif
                                                </svg>
                                                <span class="sr-only">{{ $sellers_growth > 0 ? 'Increased' : 'Decreased' }} by</span>
                                                {{ abs(number_format($sellers_growth, 1)) }}%
                                            </div>
                                        @endif
                                    </dd>
                                    <dd class="mt-1 text-sm text-gray-500">
                                        Avg {{ number_format($new_sellers / max(1, $days_in_period), 1) }} new sellers per day
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Seller Performance -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Seller Performance</dt>
                                    <dd class="mt-1">
                                        <div class="text-2xl font-semibold text-gray-900 dark:text-white">
                                            {{ number_format($avg_seller_rating, 1) }}
                                            <span class="text-sm text-yellow-500">★</span>
                                        </div>
                                        <div class="mt-1 text-sm text-gray-500">
                                            {{ number_format($total_seller_reviews) }} total reviews
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue Insights -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Revenue Insights</dt>
                                    <dd class="mt-1">
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Revenue</div>
                                        <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ $avg_revenue_per_seller }}
                                        </div>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Analysis Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Seller Activity Trends -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Seller Activity Analysis</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Highly Active Sellers</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ number_format($seller_engagement['highly_active']) }}
                                    <span class="text-sm text-gray-500">({{ number_format(($seller_engagement['highly_active'] / $total_sellers) * 100, 1) }}%)</span>
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Moderately Active Sellers</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ number_format($seller_engagement['moderately_active']) }}
                                    <span class="text-sm text-gray-500">({{ number_format(($seller_engagement['moderately_active'] / $total_sellers) * 100, 1) }}%)</span>
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Average Products per Seller</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($avg_products_per_seller, 1) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Average Order Value</span>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Average Order Value</div>
                                <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ $avg_order_value }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Performance Metrics</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Response Rate</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($seller_metrics['response_rate'], 1) }}%</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Order Fulfillment Rate</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($seller_metrics['fulfillment_rate'], 1) }}%</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">Customer Satisfaction</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($seller_metrics['satisfaction_rate'], 1) }}%</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-500">On-time Delivery Rate</span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($seller_metrics['ontime_delivery_rate'], 1) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rating Distribution -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Rating Distribution</h2>
                    <div class="space-y-4">
                        @foreach($rating_distribution as $rating)
                        <div>
                            <div class="flex items-center">
                                <span class="text-sm font-medium text-gray-600 w-16">{{ $rating->rating }} Stars</span>
                                <div class="flex-1 mx-4">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $rating->percentage }}%"></div>
                                    </div>
                                </div>
                                <span class="text-sm text-gray-600">{{ number_format($rating->count) }}</span>
                                <span class="text-sm text-gray-500 w-16 text-right">({{ number_format($rating->percentage, 1) }}%)</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Daily New Sellers Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Daily New Sellers</h2>
                    <div class="h-64">
                        <canvas id="dailyNewSellersChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Seller Performance -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top Sellers -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Performing Sellers</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Seller</th>
                                        <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sales</th>
                                        <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Revenue</th>
                                        <th class="px-6 py-3 bg-gray-50 dark:bg-gray-800 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rating</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($top_sellers as $seller)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $seller->name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $seller->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">
                                                {{ number_format($seller->sales) }} total
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $seller->formatted_revenue }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                @if($seller->rating)
                                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($seller->rating, 1) }}</span>
                                                    <svg class="h-4 w-4 text-yellow-400 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">({{ number_format($seller->total_ratings) }})</span>
                                                @else
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">No ratings</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">
                                            No sellers found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Reviews -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Reviews</h3>
                        <div class="space-y-4">
                            @foreach($recent_reviews as $review)
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-1">
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="h-4 w-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                @endfor
                                            </div>
                                            <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">{{ $review->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">{{ $review->comment }}</p>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            by {{ $review->buyer->name }}
                                        </div>
                                    </div>
                                    <div class="ml-4 text-right">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $review->seller->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            Seller Avg: {{ number_format($review->seller_avg_rating, 1) }} ★
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize charts when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Daily New Sellers Chart
            const dailyNewSellersCtx = document.getElementById('dailyNewSellersChart').getContext('2d');
            new Chart(dailyNewSellersCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($daily_new_sellers->pluck('date')) !!},
                    datasets: [{
                        label: 'New Sellers',
                        data: {!! json_encode($daily_new_sellers->pluck('count')) !!},
                        borderColor: '#6366F1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        fill: true
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
                                precision: 0
                            }
                        }
                    }
                }
            });
        });
    </script>
    @endpush
</x-app-layout>
