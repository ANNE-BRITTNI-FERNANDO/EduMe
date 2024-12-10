<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        <h2 class="mt-4 text-2xl font-bold">Payment Cancelled</h2>
                        <p class="mt-2 text-gray-600">Your payment was cancelled. No charges were made.</p>
                        <a href="/checkout" class="mt-4 inline-block bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Try Again
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
