<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-6">Delivery Tracking</h2>
                    
                    @foreach ($trackings as $tracking)
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <h3 class="text-lg font-semibold mb-4">Tracking Details</h3>
                                    <div class="space-y-3">
                                        <p><span class="font-medium">Tracking Number:</span> {{ $tracking->tracking_number }}</p>
                                        <p><span class="font-medium">Status:</span> 
                                            <span class="px-2 py-1 rounded-full text-sm
                                                @if($tracking->status === 'delivered') bg-green-100 text-green-800
                                                @elseif($tracking->status === 'in_transit') bg-blue-100 text-blue-800
                                                @else bg-yellow-100 text-yellow-800
                                                @endif">
                                                {{ ucwords(str_replace('_', ' ', $tracking->status)) }}
                                            </span>
                                        </p>
                                        <p><span class="font-medium">Current Location:</span> {{ $tracking->current_location }}</p>
                                        <p><span class="font-medium">Estimated Delivery:</span> {{ $tracking->estimated_delivery_date->format('M d, Y') }}</p>
                                        @if($tracking->actual_delivery_date)
                                            <p><span class="font-medium">Delivered On:</span> {{ $tracking->actual_delivery_date->format('M d, Y') }}</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div>
                                    <h3 class="text-lg font-semibold mb-4">Delivery Information</h3>
                                    <div class="space-y-3">
                                        <p><span class="font-medium">From:</span> {{ $tracking->from_address }}</p>
                                        <p><span class="font-medium">To:</span> {{ $tracking->to_address }}</p>
                                        @if($tracking->delivery_notes)
                                            <p><span class="font-medium">Notes:</span> {{ $tracking->delivery_notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline -->
                            <div class="mt-8">
                                <h3 class="text-lg font-semibold mb-4">Delivery Timeline</h3>
                                <div class="relative">
                                    <div class="absolute left-4 top-0 h-full w-0.5 bg-gray-200"></div>
                                    @foreach ($tracking->updates as $update)
                                        <div class="relative flex items-start mb-6">
                                            <div class="absolute left-0 mt-1.5">
                                                <div class="h-8 w-8 rounded-full border-2 border-indigo-500 bg-white flex items-center justify-center">
                                                    <svg class="h-4 w-4 text-indigo-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-12">
                                                <span class="text-sm text-gray-500">{{ $update->created_at->format('M d, Y h:i A') }}</span>
                                                <p class="mt-1 text-gray-900">{{ $update->description }}</p>
                                                @if($update->location)
                                                    <p class="text-sm text-gray-600">Location: {{ $update->location }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Update Status Form (Only visible to sellers) -->
                            @if(auth()->user()->role === 'seller' && $tracking->seller_id === auth()->id())
                                <div class="mt-8 border-t pt-6">
                                    <h3 class="text-lg font-semibold mb-4">Update Delivery Status</h3>
                                    <form action="{{ route('delivery.tracking.update', $tracking->tracking_number) }}" method="POST" class="space-y-4">
                                        @csrf
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Status</label>
                                            <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                <option value="pending" @if($tracking->status === 'pending') selected @endif>Pending</option>
                                                <option value="picked_up" @if($tracking->status === 'picked_up') selected @endif>Picked Up</option>
                                                <option value="in_transit" @if($tracking->status === 'in_transit') selected @endif>In Transit</option>
                                                <option value="out_for_delivery" @if($tracking->status === 'out_for_delivery') selected @endif>Out for Delivery</option>
                                                <option value="delivered" @if($tracking->status === 'delivered') selected @endif>Delivered</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Current Location</label>
                                            <input type="text" name="current_location" value="{{ $tracking->current_location }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Delivery Notes (Optional)</label>
                                            <textarea name="delivery_notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $tracking->delivery_notes }}</textarea>
                                        </div>
                                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                            Update Status
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
