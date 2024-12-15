<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-800">{{ $warehouse->name }}</h1>
            <a href="{{ route('warehouses.index') }}" class="text-blue-500 hover:text-blue-600">
                <i class="fas fa-arrow-left mr-2"></i>Back to Warehouses
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Warehouse Details -->
            <div class="col-span-2">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Warehouse Information</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-gray-600">Location</label>
                            <p class="text-gray-800"><i class="fas fa-map-marker-alt mr-2"></i>{{ $warehouse->location }}</p>
                        </div>
                        
                        <div>
                            <label class="text-gray-600">Address</label>
                            <p class="text-gray-800"><i class="fas fa-building mr-2"></i>{{ $warehouse->address }}</p>
                        </div>

                        <div>
                            <label class="text-gray-600">Contact Number</label>
                            <p class="text-gray-800"><i class="fas fa-phone mr-2"></i>{{ $warehouse->contact_number }}</p>
                        </div>

                        <div>
                            <label class="text-gray-600">Operating Hours</label>
                            <p class="text-gray-800">
                                <i class="fas fa-clock mr-2"></i>
                                {{ \Carbon\Carbon::parse($warehouse->opening_time)->format('g:i A') }} - 
                                {{ \Carbon\Carbon::parse($warehouse->closing_time)->format('g:i A') }}
                            </p>
                        </div>

                        <div>
                            <label class="text-gray-600">Pickup Status</label>
                            @if($warehouse->pickup_available)
                                <p class="text-green-600"><i class="fas fa-check-circle mr-2"></i>Pickup Available</p>
                            @else
                                <p class="text-red-600"><i class="fas fa-times-circle mr-2"></i>No Pickup Available</p>
                            @endif
                        </div>
                    </div>

                    @can('manage-warehouses')
                    <div class="mt-6 flex space-x-4">
                        <a href="{{ route('warehouses.edit', $warehouse) }}" 
                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">
                            <i class="fas fa-edit mr-2"></i>Edit Warehouse
                        </a>
                        <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this warehouse?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">
                                <i class="fas fa-trash mr-2"></i>Delete Warehouse
                            </button>
                        </form>
                    </div>
                    @endcan
                </div>
            </div>

            <!-- Map -->
            <div class="col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Location Map</h2>
                    <div id="map" style="width: 100%; height: 400px; border-radius: 0.5rem;" class="border border-gray-200"></div>
                    <div id="map-error" class="text-red-500 mt-2 hidden"></div>
                </div>
            </div>
        </div>

        <!-- Pending Deliveries Section (For Sellers) -->
        @if(Auth::user()->role === 'seller' && count($pendingDeliveries) > 0)
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Pending Deliveries</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Order ID
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pendingDeliveries as $product)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $product->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $product->cartItems->first()->order->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending Delivery
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Ready for Pickup Section (For Buyers) -->
        @if(Auth::user()->role === 'buyer' && count($readyForPickup) > 0)
        <div class="mt-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Items Ready for Pickup</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Order ID
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Items
                                </th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($readyForPickup as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">#{{ $order->id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $order->cartItems->count() }} items</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Ready for Pickup
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        // Initialize the map only after the Google Maps script is loaded
        window.initMap = function() {
            try {
                console.log('Initializing map...');
                const mapDiv = document.getElementById('map');
                const errorDiv = document.getElementById('map-error');
                
                if (!mapDiv) {
                    throw new Error('Map container not found');
                }

                // Specific coordinates for Andheri East, Mumbai
                const warehouseLocation = {
                    lat: 19.1136,
                    lng: 72.8697
                };

                console.log('Creating map with location:', warehouseLocation);
                
                const map = new google.maps.Map(mapDiv, {
                    center: warehouseLocation,
                    zoom: 15,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    mapTypeControl: true,
                    streetViewControl: true,
                    fullscreenControl: true
                });

                console.log('Map created, adding marker...');
                
                const marker = new google.maps.Marker({
                    position: warehouseLocation,
                    map: map,
                    title: '{{ $warehouse->name }}',
                    animation: google.maps.Animation.DROP
                });

                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div style="padding: 10px;">
                            <h3 style="font-weight: bold; font-size: 16px; margin-bottom: 8px;">{{ $warehouse->name }}</h3>
                            <p style="margin-bottom: 4px;"><i class="fas fa-map-marker-alt" style="margin-right: 8px;"></i>{{ $warehouse->address }}</p>
                            <p style="margin-bottom: 4px;"><i class="fas fa-phone" style="margin-right: 8px;"></i>{{ $warehouse->contact_number }}</p>
                            <p><i class="fas fa-clock" style="margin-right: 8px;"></i>{{ \Carbon\Carbon::parse($warehouse->opening_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($warehouse->closing_time)->format('g:i A') }}</p>
                        </div>
                    `
                });

                // Open info window by default
                infoWindow.open(map, marker);

                marker.addListener('click', () => {
                    infoWindow.open(map, marker);
                });

                // Hide error message if map loads successfully
                errorDiv.classList.add('hidden');
                console.log('Map initialization complete');

            } catch (error) {
                console.error('Map initialization error:', error);
                const errorDiv = document.getElementById('map-error');
                errorDiv.textContent = 'Error loading map: ' + error.message;
                errorDiv.classList.remove('hidden');
            }
        };
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMap" 
            async defer
            onerror="document.getElementById('map-error').textContent = 'Failed to load Google Maps'; document.getElementById('map-error').classList.remove('hidden');"></script>
    @endpush
</x-app-layout>
