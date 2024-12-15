<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="text-2xl font-bold mb-4">Schedule a Delivery</h2>

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('deliveries.store') }}" class="space-y-6">
                        @csrf
                        
                        <!-- Sender Information -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Sender Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input type="text" name="sender_name" value="{{ old('sender_name') }}" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input type="tel" name="sender_phone" value="{{ old('sender_phone') }}" 
                                        placeholder="+94XXXXXXXXX"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Address</label>
                                    <textarea name="sender_address" rows="2" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('sender_address') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Receiver Information -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Receiver Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Name</label>
                                    <input type="text" name="receiver_name" value="{{ old('receiver_name') }}" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input type="tel" name="receiver_phone" value="{{ old('receiver_phone') }}" 
                                        placeholder="+94XXXXXXXXX"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Address</label>
                                    <textarea name="receiver_address" rows="2" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('receiver_address') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Package Information -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-4">Package Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Package Description</label>
                                    <textarea name="package_description" rows="2" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('package_description') }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                                    <input type="number" step="0.01" name="package_weight" value="{{ old('package_weight') }}" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Delivery Type</label>
                                    <select name="delivery_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                        <option value="standard" {{ old('delivery_type') === 'standard' ? 'selected' : '' }}>Standard Delivery</option>
                                        <option value="express" {{ old('delivery_type') === 'express' ? 'selected' : '' }}>Express Delivery</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Schedule Delivery
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
