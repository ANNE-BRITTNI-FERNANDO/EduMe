<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium text-white">
                            {{ __('Profile Information') }}
                        </h2>

                        <p class="mt-1 text-sm text-white">
                            {{ __("Update your account's profile information and delivery details.") }}
                        </p>
                    </header>

                    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                        @csrf
                    </form>

                    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
                        @csrf
                        @method('patch')

                        <div class="mt-6 space-y-6">
                            <div>
                                <x-input-label for="name" :value="__('Name')" class="text-gray-700"/>
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" class="text-gray-700"/>
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <div>
                                        <p class="text-sm mt-2 text-white">
                                            {{ __('Your email address is unverified.') }}

                                            <button form="send-verification" class="underline text-sm text-white hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                {{ __('Click here to re-send the verification email.') }}
                                            </button>
                                        </p>

                                        @if (session('status') === 'verification-link-sent')
                                            <p class="mt-2 font-medium text-sm text-white">
                                                {{ __('A new verification link has been sent to your email address.') }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div>
                                <x-input-label for="phone" :value="__('Phone')" class="text-gray-700"/>
                                <x-text-input id="phone" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $user->phone)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                            </div>

                            <div>
                                <x-input-label for="address" :value="__('Address')" class="text-gray-700"/>
                                <textarea id="address" name="address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3" required>{{ old('address', $user->address) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('address')" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-white">Province</label>
                                <select name="province" id="province" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">Select Province</option>
                                    @foreach(App\Services\LocationService::getAllProvinces() as $province)
                                        <option value="{{ $province }}" {{ old('province', $user->province) === $province ? 'selected' : '' }}>{{ $province }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('province')" />
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-white">District</label>
                                <select name="location" id="location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">Select District</option>
                                    @foreach(App\Services\LocationService::getCityProvinceMap() as $district => $province)
                                        <option value="{{ $district }}" 
                                                data-province="{{ $province }}"
                                                {{ old('location', $user->location) === $district ? 'selected' : '' }}
                                                class="province-{{ $province }}">
                                            {{ $district }} District
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('location')" />
                                <p class="mt-1 text-sm text-gray-500">Select your district for delivery</p>
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Save Profile') }}</x-primary-button>

                                @if (session('status') === 'profile-updated')
                                    <p
                                        x-data="{ show: true }"
                                        x-show="show"
                                        x-transition
                                        x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-white"
                                    >{{ __('Saved.') }}</p>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const provinceSelect = document.getElementById('province');
            const districtSelect = document.getElementById('location');
            
            // Store all district options for filtering
            const allDistrictOptions = Array.from(districtSelect.options).slice(1);
            
            // When province is selected, filter districts
            provinceSelect.addEventListener('change', function() {
                const selectedProvince = this.value;
                
                // Reset district select
                districtSelect.innerHTML = '<option value="">Select District</option>';
                
                // Filter districts based on selected province
                const filteredDistricts = selectedProvince 
                    ? allDistrictOptions.filter(opt => opt.getAttribute('data-province') === selectedProvince)
                    : allDistrictOptions;
                    
                // Add filtered districts to select
                filteredDistricts.forEach(opt => districtSelect.add(opt.cloneNode(true)));
            });
            
            // When district is selected, update province
            districtSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value) {
                    const province = selectedOption.getAttribute('data-province');
                    provinceSelect.value = province;
                }
            });
            
            // Initialize province on page load if district is selected
            if (districtSelect.value) {
                const selectedOption = districtSelect.options[districtSelect.selectedIndex];
                if (selectedOption.value) {
                    provinceSelect.value = selectedOption.getAttribute('data-province');
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
