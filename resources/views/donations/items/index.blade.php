<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Donation Center') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Available Donations</h3>
                        <a href="{{ route('donations.items.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Donate Items
                        </a>
                    </div>

                    @if($donations->isEmpty())
                        <p class="text-gray-500 text-center py-4">No donations available at the moment.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($donations as $donation)
                                <div class="border rounded-lg p-4 shadow-sm">
                                    @if($donation->images)
                                        <div class="mb-4">
                                            @php
                                                $images = json_decode($donation->images);
                                                $firstImage = $images[0] ?? null;
                                            @endphp
                                            @if($firstImage)
                                                <img src="{{ Storage::url($firstImage) }}" alt="{{ $donation->item_name }}" class="w-full h-48 object-cover rounded">
                                            @endif
                                        </div>
                                    @endif
                                    <h4 class="font-semibold text-lg mb-2">{{ $donation->item_name }}</h4>
                                    <p class="text-sm text-gray-600 mb-2">Category: {{ ucfirst($donation->category) }}</p>
                                    <p class="text-sm text-gray-600 mb-2">Condition: {{ ucfirst(str_replace('_', ' ', $donation->condition)) }}</p>
                                    <p class="text-sm text-gray-600 mb-4">Quantity: {{ $donation->quantity }}</p>
                                    <p class="text-sm text-gray-700 mb-4">{{ Str::limit($donation->description, 100) }}</p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-500">
                                            By {{ $donation->user->name }}
                                        </span>
                                        <a href="{{ route('donations.request', $donation->id) }}" class="bg-green-500 hover:bg-green-700 text-white text-sm font-bold py-2 px-4 rounded">
                                            Request
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $donations->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
