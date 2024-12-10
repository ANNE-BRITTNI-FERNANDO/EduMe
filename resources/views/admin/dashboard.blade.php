<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Admin Dashboard</h2>
            <div class="flex space-x-4">
                <a href="{{ route('admin.approved') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-md shadow-md transition duration-200 ease-in-out">
                    View Approved Products
                </a>
                <a href="{{ route('admin.payouts.index') }}" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md shadow-md transition duration-200 ease-in-out">
                    Manage Payouts
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Products Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pending Products</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Image</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($product->image_path)
                                                <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->product_name }}" class="w-16 h-16 object-cover rounded-lg">
                                            @else
                                                <div class="w-16 h-16 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                                                    <span class="text-gray-400">No Image</span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $product->product_name }}</div>
                                            <div class="text-sm text-gray-500">by {{ $product->user->name ?? 'Unknown' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($product->description, 100) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">${{ number_format($product->price, 2) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $product->category }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <form action="{{ route('product.approve', $product->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded-md hover:bg-green-600 transition-colors">
                                                        Approve
                                                    </button>
                                                </form>
                                                <form action="{{ route('product.reject', $product->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition-colors">
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            No pending products found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
