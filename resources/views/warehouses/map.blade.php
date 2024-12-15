<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-800">Warehouse Locations</h1>
            <a href="{{ route('warehouses.index') }}" class="text-blue-500 hover:text-blue-600">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div id="map" style="width: 100%; height: 600px; border-radius: 0.5rem;" class="border border-gray-200"></div>
            <div id="map-error" class="text-red-500 mt-2 hidden"></div>
        </div>

        <!-- List of warehouses below the map -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($warehouses as $warehouse)
            <div class="bg-white rounded-lg shadow-md p-4 hover:shadow-lg transition-shadow cursor-pointer warehouse-card" 
                 data-warehouse-id="{{ $warehouse->id }}"
                 onclick="centerMapOnWarehouse({{ $warehouse->id }})">
                <h3 class="text-lg font-semibold text-gray-800">{{ $warehouse->name }}</h3>
                <p class="text-gray-600"><i class="fas fa-map-marker-alt mr-2"></i>{{ $warehouse->location }}</p>
                <p class="text-gray-600"><i class="fas fa-phone mr-2"></i>{{ $warehouse->contact_number }}</p>
                <p class="text-gray-600">
                    <i class="fas fa-clock mr-2"></i>
                    {{ \Carbon\Carbon::parse($warehouse->opening_time)->format('g:i A') }} - 
                    {{ \Carbon\Carbon::parse($warehouse->closing_time)->format('g:i A') }}
                </p>
            </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
    <script>
        let map;
        let markers = {};
        let infoWindows = {};

        // Warehouse coordinates
        const warehouseLocations = {
            @foreach($warehouses as $warehouse)
                {{ $warehouse->id }}: {
                    position: { 
                        lat: {{ 
                            $warehouse->location === 'Colombo' ? '6.9271' : 
                            ($warehouse->location === 'Kandy' ? '7.2906' : 
                            ($warehouse->location === 'Galle' ? '6.0535' : 
                            ($warehouse->location === 'Jaffna' ? '9.6615' : 
                            ($warehouse->location === 'Negombo' ? '7.2081' : 
                            ($warehouse->location === 'Batticaloa' ? '7.7170' :
                            ($warehouse->location === 'Anuradhapura' ? '8.3114' :
                            ($warehouse->location === 'Trincomalee' ? '8.5874' :
                            ($warehouse->location === 'Matara' ? '5.9549' :
                            ($warehouse->location === 'Kurunegala' ? '7.4818' :
                            ($warehouse->location === 'Ratnapura' ? '6.6980' :
                            ($warehouse->location === 'Badulla' ? '6.9934' : '6.9271'))))))))))) }},
                        lng: {{ 
                            $warehouse->location === 'Colombo' ? '79.8612' : 
                            ($warehouse->location === 'Kandy' ? '80.6337' : 
                            ($warehouse->location === 'Galle' ? '80.2210' : 
                            ($warehouse->location === 'Jaffna' ? '80.0255' : 
                            ($warehouse->location === 'Negombo' ? '79.8383' :
                            ($warehouse->location === 'Batticaloa' ? '81.7000' :
                            ($warehouse->location === 'Anuradhapura' ? '80.4037' :
                            ($warehouse->location === 'Trincomalee' ? '81.2152' :
                            ($warehouse->location === 'Matara' ? '80.5550' :
                            ($warehouse->location === 'Kurunegala' ? '80.3609' :
                            ($warehouse->location === 'Ratnapura' ? '80.3992' :
                            ($warehouse->location === 'Badulla' ? '81.0550' : '79.8612'))))))))))) }}
                    },
                    name: '{{ $warehouse->name }}',
                    location: '{{ $warehouse->location }}',
                    address: '{{ $warehouse->address }}',
                    contact: '{{ $warehouse->contact_number }}',
                    hours: '{{ \Carbon\Carbon::parse($warehouse->opening_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($warehouse->closing_time)->format('g:i A') }}'
                },
            @endforeach
        };

        function initMap() {
            try {
                console.log('Initializing map...');
                const mapDiv = document.getElementById('map');
                const errorDiv = document.getElementById('map-error');
                
                if (!mapDiv) {
                    throw new Error('Map container not found');
                }

                // Center map on Sri Lanka
                const sriLankaCenter = { lat: 7.8731, lng: 80.7718 };
                
                map = new google.maps.Map(mapDiv, {
                    center: sriLankaCenter,
                    zoom: 7,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    mapTypeControl: true,
                    streetViewControl: true,
                    fullscreenControl: true
                });

                // Add markers for each warehouse
                Object.entries(warehouseLocations).forEach(([id, data]) => {
                    // Create marker
                    const marker = new google.maps.Marker({
                        position: data.position,
                        map: map,
                        title: data.name,
                        animation: google.maps.Animation.DROP
                    });

                    // Create info window
                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div style="padding: 10px;">
                                <h3 style="font-weight: bold; font-size: 16px; margin-bottom: 8px;">${data.name}</h3>
                                <p style="margin-bottom: 4px;"><i class="fas fa-map-marker-alt" style="margin-right: 8px;"></i>${data.address}</p>
                                <p style="margin-bottom: 4px;"><i class="fas fa-phone" style="margin-right: 8px;"></i>${data.contact}</p>
                                <p><i class="fas fa-clock" style="margin-right: 8px;"></i>${data.hours}</p>
                            </div>
                        `
                    });

                    // Add click listener
                    marker.addListener('click', () => {
                        Object.values(infoWindows).forEach(iw => iw.close());
                        infoWindow.open(map, marker);
                        highlightWarehouseCard(id);
                    });

                    markers[id] = marker;
                    infoWindows[id] = infoWindow;
                });

                errorDiv.classList.add('hidden');
                console.log('Map initialization complete');

            } catch (error) {
                console.error('Map initialization error:', error);
                const errorDiv = document.getElementById('map-error');
                if (errorDiv) {
                    errorDiv.textContent = 'Error loading map: ' + error.message;
                    errorDiv.classList.remove('hidden');
                }
            }
        }

        window.gm_authFailure = function() {
            console.error('Google Maps authentication failed');
            const errorDiv = document.getElementById('map-error');
            if (errorDiv) {
                errorDiv.textContent = 'Google Maps authentication failed. Please check your API key.';
                errorDiv.classList.remove('hidden');
            }
        };

        function centerMapOnWarehouse(warehouseId) {
            const warehouse = warehouseLocations[warehouseId];
            if (warehouse && map) {
                map.setCenter(warehouse.position);
                map.setZoom(15);
                Object.values(infoWindows).forEach(iw => iw.close());
                infoWindows[warehouseId].open(map, markers[warehouseId]);
                highlightWarehouseCard(warehouseId);
            }
        }

        function highlightWarehouseCard(warehouseId) {
            document.querySelectorAll('.warehouse-card').forEach(card => {
                card.classList.remove('ring-2', 'ring-blue-500');
            });
            const selectedCard = document.querySelector(`.warehouse-card[data-warehouse-id="${warehouseId}"]`);
            if (selectedCard) {
                selectedCard.classList.add('ring-2', 'ring-blue-500');
                selectedCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google.maps_api_key') }}&callback=initMap" 
            async defer
            onerror="console.error('Failed to load Google Maps script'); document.getElementById('map-error').textContent = 'Failed to load Google Maps'; document.getElementById('map-error').classList.remove('hidden');"></script>
    @endpush
</x-app-layout>
