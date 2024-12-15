<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-4">Delivery Tracking</h2>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="bg-gray-50 p-4 rounded-lg mb-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Tracking Number</p>
                                <p class="text-lg font-bold">{{ $delivery->tracking_number }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Status</p>
                                <p class="text-lg font-bold capitalize">{{ str_replace('_', ' ', $delivery->status) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="relative">
                        <div class="absolute left-1/2 transform -translate-x-1/2 h-full w-0.5 bg-gray-200"></div>
                        
                        @foreach(['pending', 'picked_up', 'in_transit', 'delivered'] as $status)
                            <div class="relative mb-8">
                                <div class="flex items-center">
                                    <div class="flex-1 text-right pr-4">
                                        <h3 class="text-lg font-semibold capitalize">{{ str_replace('_', ' ', $status) }}</h3>
                                    </div>
                                    <div class="w-4 h-4 rounded-full {{ $delivery->status === $status ? 'bg-green-500' : 'bg-gray-200' }} 
                                        transform translate-x-[-8px]"></div>
                                    <div class="flex-1 pl-4">
                                        @if($delivery->status === $status)
                                            <p class="text-sm text-gray-500">
                                                {{ $delivery->updated_at->format('M d, Y H:i') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Delivery Details -->
                    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Sender Details</h3>
                            <p><span class="font-medium">Name:</span> {{ $delivery->sender_name }}</p>
                            <p><span class="font-medium">Phone:</span> {{ $delivery->sender_phone }}</p>
                            <p><span class="font-medium">Address:</span> {{ $delivery->sender_address }}</p>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Receiver Details</h3>
                            <p><span class="font-medium">Name:</span> {{ $delivery->receiver_name }}</p>
                            <p><span class="font-medium">Phone:</span> {{ $delivery->receiver_phone }}</p>
                            <p><span class="font-medium">Address:</span> {{ $delivery->receiver_address }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
