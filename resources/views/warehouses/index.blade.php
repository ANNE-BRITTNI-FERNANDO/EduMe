<x-app-layout>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Warehouses</h1>
            @can('manage-warehouses')
            <a href="{{ route('warehouses.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Add New Warehouse
            </a>
            @endcan
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($warehouses as $warehouse)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ $warehouse->name }}</h2>
                <p class="text-gray-600 mb-2"><i class="fas fa-map-marker-alt mr-2"></i>{{ $warehouse->location }}</p>
                <p class="text-gray-600 mb-2"><i class="fas fa-phone mr-2"></i>{{ $warehouse->contact_number }}</p>
                <p class="text-gray-600 mb-4">
                    <i class="fas fa-clock mr-2"></i>
                    {{ \Carbon\Carbon::parse($warehouse->opening_time)->format('g:i A') }} - 
                    {{ \Carbon\Carbon::parse($warehouse->closing_time)->format('g:i A') }}
                </p>
                
                <div class="flex justify-between items-center mt-4">
                    <a href="{{ route('warehouses.show', $warehouse) }}" 
                       class="text-blue-500 hover:text-blue-600">
                        View Details
                    </a>
                    @if($warehouse->pickup_available)
                    <span class="bg-green-100 text-green-800 text-sm px-3 py-1 rounded-full">
                        Pickup Available
                    </span>
                    @endif
                </div>

                @can('manage-warehouses')
                <div class="flex justify-end mt-4 space-x-2">
                    <a href="{{ route('warehouses.edit', $warehouse) }}" 
                       class="text-yellow-500 hover:text-yellow-600">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('warehouses.destroy', $warehouse) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this warehouse?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-600">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
                @endcan
            </div>
            @endforeach
        </div>

        <div class="mt-8">
            <a href="{{ route('warehouses.map') }}" 
               class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded inline-flex items-center">
                <i class="fas fa-map-marked-alt mr-2"></i>
                View on Map
            </a>
        </div>
    </div>
</x-app-layout>
