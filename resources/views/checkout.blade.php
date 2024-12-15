<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Checkout</h2>

                    <form method="POST" action="{{ route('checkout.process') }}" class="mt-6 space-y-6">
                        @csrf

                        <div>
                            <x-input-label for="street_address" :value="__('Street Address')" />
                            <x-text-input id="street_address" name="street_address" type="text" class="mt-1 block w-full" :value="old('street_address', auth()->user()->street_address)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('street_address')" />
                        </div>

                        <div>
                            <x-input-label for="city" :value="__('City')" />
                            <x-text-input id="city" name="city" type="text" class="mt-1 block w-full" :value="old('city', auth()->user()->city)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('city')" />
                        </div>

                        <div>
                            <x-input-label for="province" :value="__('Province')" />
                            <x-text-input id="province" name="province" type="text" class="mt-1 block w-full" :value="old('province', auth()->user()->province)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('province')" />
                        </div>

                        <div>
                            <x-input-label for="phone_number" :value="__('Phone Number')" />
                            <x-text-input id="phone_number" name="phone_number" type="text" class="mt-1 block w-full" :value="old('phone_number', auth()->user()->phone_number)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
                        </div>

                        <div class="flex items-center gap-4">
                            <x-primary-button>{{ __('Proceed to Payment') }}</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
