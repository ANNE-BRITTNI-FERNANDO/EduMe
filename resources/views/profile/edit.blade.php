<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="min-h-screen bg-cover bg-center bg-no-repeat bg-fixed" style="background-image: linear-gradient(to bottom, rgba(17, 24, 39, 0.7), rgba(17, 24, 39, 0.9)), url('https://images.unsplash.com/photo-1432888498266-38ffec3eaf0a?q=80&w=2074&auto=format&fit=crop'); background-attachment: fixed;">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center">
                        <svg class="w-8 h-8 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <h2 class="text-3xl font-bold text-white">Profile Settings</h2>
                    </div>
                </div>

                <!-- Profile Information -->
                <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-xl rounded-2xl overflow-hidden mb-6 transition-all duration-300 hover:shadow-2xl">
                    <div class="p-8">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <!-- Update Password -->
                <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-xl rounded-2xl overflow-hidden mb-6 transition-all duration-300 hover:shadow-2xl">
                    <div class="p-8">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <!-- Delete Account -->
                <div class="bg-white/95 dark:bg-gray-800/95 backdrop-blur-md shadow-xl rounded-2xl overflow-hidden transition-all duration-300 hover:shadow-2xl">
                    <div class="p-8">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Define the districts by province mapping
        const districtsByProvince = {
            'Western': ['Colombo', 'Gampaha', 'Kalutara'],
            'Central': ['Kandy', 'Matale', 'Nuwara Eliya'],
            'Southern': ['Galle', 'Matara', 'Hambantota'],
            'Northern': ['Jaffna', 'Kilinochchi', 'Mannar', 'Mullaitivu', 'Vavuniya'],
            'Eastern': ['Trincomalee', 'Batticaloa', 'Ampara'],
            'North Western': ['Kurunegala', 'Puttalam'],
            'North Central': ['Anuradhapura', 'Polonnaruwa'],
            'Uva': ['Badulla', 'Monaragala'],
            'Sabaragamuwa': ['Ratnapura', 'Kegalle']
        };

        document.addEventListener('DOMContentLoaded', function() {
            const provinceSelect = document.getElementById('province');
            const locationSelect = document.getElementById('location');
            const currentLocation = locationSelect.value;
            
            function updateLocations() {
                const selectedProvince = provinceSelect.value;
                locationSelect.innerHTML = '<option value="">Select District</option>';
                
                if (selectedProvince) {
                    const districts = districtsByProvince[selectedProvince] || [];
                    districts.forEach(district => {
                        const option = document.createElement('option');
                        option.value = district;
                        option.textContent = district;
                        if (district === currentLocation) {
                            option.selected = true;
                        }
                        locationSelect.appendChild(option);
                    });
                }
            }

            // Add event listener for province change
            provinceSelect.addEventListener('change', updateLocations);

            // Set initial districts if province is selected
            if (provinceSelect.value) {
                updateLocations();
            }
        });
    </script>
    @endpush
</x-app-layout>
