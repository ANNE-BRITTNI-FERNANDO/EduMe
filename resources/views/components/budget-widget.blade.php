@props(['budget' => null, 'tracking' => null])

@if(auth()->check() && auth()->user()->role === 'buyer')
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-4 mb-6 transition-all duration-300 hover:shadow-xl border border-blue-100 dark:border-gray-700">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Current Budget -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Budget</h3>
                <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                    LKR {{ $budget ? number_format($budget->total_budget, 2) : '0.00' }}
                </p>
                <p class="text-xs text-gray-600 dark:text-gray-400">{{ $budget ? ucfirst($budget->cycle_type) : 'Set your budget' }}</p>
            </div>

            <!-- Remaining Amount -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 rounded-lg p-4">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Remaining</h3>
                <p class="text-2xl font-bold {{ $tracking && $tracking->remaining_amount > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    LKR {{ $tracking ? number_format($tracking->remaining_amount, 2) : '0.00' }}
                </p>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Until {{ $tracking ? $tracking->cycle_end_date->format('M d') : 'N/A' }}
                </p>
            </div>

            <!-- Quick Actions -->
            <div class="flex flex-col justify-center items-center bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-600 rounded-lg p-4">
                <button onclick="window.location.href='{{ route('buyer.budget.dashboard') }}'" 
                        class="w-full px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm rounded-lg transition duration-300 mb-2">
                    Manage Budget
                </button>
                <button onclick="toggleSetBudgetModal()" 
                        class="w-full px-4 py-2 border border-indigo-600 text-indigo-600 hover:bg-indigo-50 text-sm rounded-lg transition duration-300">
                    Quick Set Budget
                </button>
            </div>
        </div>
    </div>

    <!-- Set Budget Modal -->
    <div id="setBudgetModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Set Your Budget</h3>
                <form action="{{ route('buyer.budget.set') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Budget Amount (LKR)</label>
                        <input type="number" name="total_budget" step="0.01" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Budget Cycle</label>
                        <select name="cycle_type" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="toggleSetBudgetModal()"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">
                            Save Budget
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleSetBudgetModal() {
            const modal = document.getElementById('setBudgetModal');
            modal.classList.toggle('hidden');
        }
    </script>
@endif
