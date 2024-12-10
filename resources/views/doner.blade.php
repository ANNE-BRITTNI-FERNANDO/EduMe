<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h2 class="text-2xl font-semibold mb-4">Welcome Donor!</h2>
                <div class="space-y-4">
                    <a href="{{ route('productlisting') }}" class="block bg-indigo-600 text-white p-4 rounded">Browse Products to Donate</a>
                    <a href="{{ route('shop.bundles') }}" class="block bg-indigo-600 text-white p-4 rounded">Browse Bundles to Donate</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>