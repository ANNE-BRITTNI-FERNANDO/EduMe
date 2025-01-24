
<x-guest-layout>
    <!-- Full Screen Background Image with Overlay and Padding -->
    <div class="min-h-screen">
        <div class="fixed inset-0 w-full h-full bg-cover bg-center bg-no-repeat" style="background-image: url('/images/child.jpg');">
            <!-- Semi-transparent Overlay for readability -->
            <div class="absolute inset-0 bg-black opacity-40"></div>

            <!-- Navbar -->
            <nav class="fixed top-0 inset-x-0 bg-gray-900 p-4 shadow-lg z-50">
                <div class="flex items-center justify-between max-w-6xl mx-auto px-4">
                    <!-- Brand Name or Logo -->
                    <a href="/" class="text-2xl font-bold text-white">EduME</a>

                    <!-- Navbar Buttons with Circular Borders -->
                    <div class="space-x-4 flex items-center">
                        <a href="/" class="text-white hover:text-gray-200 px-4 py-2 rounded-full border border-white">
                            Home
                        </a>
                        <a href="{{ route('login') }}" class="text-white hover:text-gray-200 px-4 py-2 rounded-full border border-white">
                            Login
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Centered Registration Form -->
            <div class="relative z-10 flex items-center justify-center min-h-screen px-4 pt-20">
                <div class="w-full max-w-lg bg-black bg-opacity-50 backdrop-blur-sm shadow-xl rounded-lg p-8 border border-gray-600">
                    <!-- Welcome Text -->
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-semibold text-white">Welcome to EduME</h2>
                        <p class="text-gray-300 mt-2">Join us and start your learning journey today!</p>
                    </div>

                    <!-- Registration Form -->
                    <form method="POST" action="{{ route('register') }}" class="space-y-4">
                        @csrf

                        <!-- Name -->
                        <div>
                            <x-input-label for="name" :value="__('Name')" class="text-white" />
                            <x-text-input id="name" class="block mt-1 w-full rounded-md bg-gray-800 text-white" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-400" />
                        </div>

                        <!-- Email Address -->
                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email')" class="text-white" />
                            <x-text-input id="email" class="block mt-1 w-full rounded-md bg-gray-800 text-white" type="email" name="email" :value="old('email')" required autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-400" />
                        </div>

                        <!-- Location -->
                        <div class="mt-4">
                            <x-input-label for="location" :value="__('Location')" class="text-white" />
                            <select id="location" name="location" class="block mt-1 w-full rounded-md bg-gray-800 text-white border-gray-600 focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="" disabled selected>Select your location</option>
                                <option value="Ampara">Ampara</option>
                                <option value="Anuradhapura">Anuradhapura</option>
                                <option value="Badulla">Badulla</option>
                                <option value="Batticaloa">Batticaloa</option>
                                <option value="Colombo">Colombo</option>
                                <option value="Galle">Galle</option>
                                <option value="Gampaha">Gampaha</option>
                                <option value="Hambantota">Hambantota</option>
                                <option value="Jaffna">Jaffna</option>
                                <option value="Kalutara">Kalutara</option>
                                <option value="Kandy">Kandy</option>
                                <option value="Kegalle">Kegalle</option>
                                <option value="Kilinochchi">Kilinochchi</option>
                                <option value="Kurunegala">Kurunegala</option>
                                <option value="Mannar">Mannar</option>
                                <option value="Matale">Matale</option>
                                <option value="Matara">Matara</option>
                                <option value="Monaragala">Monaragala</option>
                                <option value="Mullaitivu">Mullaitivu</option>
                                <option value="Nuwara Eliya">Nuwara Eliya</option>
                                <option value="Polonnaruwa">Polonnaruwa</option>
                                <option value="Puttalam">Puttalam</option>
                                <option value="Ratnapura">Ratnapura</option>
                                <option value="Trincomalee">Trincomalee</option>
                                <option value="Vavuniya">Vavuniya</option>
                            </select>
                            <x-input-error :messages="$errors->get('location')" class="mt-2 text-red-400" />
                        </div>

                        <!-- Password -->
                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Password')" class="text-white" />
                            <x-text-input id="password" class="block mt-1 w-full rounded-md bg-gray-800 text-white" type="password" name="password" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-400" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-white" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full rounded-md bg-gray-800 text-white" type="password" name="password_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-400" />
                        </div>

                        <!-- Register Button -->
                        <div class="flex justify-center mt-6">
                            <x-primary-button class="px-6 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500">
                                {{ __('Register') }}
                            </x-primary-button>
                        </div>

                        <!-- Already Registered Link -->
                        <div class="flex items-center justify-center mt-4">
                            <a class="text-sm text-white hover:text-gray-300 focus:outline-none" href="{{ route('login') }}">
                                {{ __('Already registered?') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
