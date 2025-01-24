<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Donate Educational Items
        </h2>
    </x-slot>

    <div class="py-12" style="background: linear-gradient(rgba(255, 255, 255, 0.92), rgba(255, 255, 255, 0.92)), url('https://img.freepik.com/free-photo/stack-books-library-room_1150-5920.jpg?w=1380&t=st=1706047577~exp=1706048177~hmac=36f2f17b1e46c40d5f8c8c12893d2f3cdd12eef22b6e7e5c7c33c654d1defb7b') center/cover fixed no-repeat;">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg backdrop-blur-sm bg-opacity-90">
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-8 text-center text-gray-900 dark:text-gray-100">
                        Donate Educational Items
                    </h2>
                    <form action="{{ route('donations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        <!-- Item Name -->
                        <div>
                            <x-input-label for="item_name" :value="__('Item Name')" />
                            <x-text-input id="item_name" name="item_name" type="text" class="mt-1 block w-full" :value="old('item_name')" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('item_name')" />
                        </div>

                        <!-- Education Level -->
                        <div>
                            <x-input-label for="education_level" :value="__('Education Level')" />
                            <select id="education_level" name="education_level" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Select Education Level</option>
                                <option value="primary">Primary School</option>
                                <option value="secondary">Secondary School</option>
                                <option value="higher_secondary">Higher Secondary</option>
                                <option value="undergraduate">Undergraduate</option>
                                <option value="postgraduate">Postgraduate</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('education_level')" />
                        </div>

                        <!-- Category -->
                        <div>
                            <x-input-label for="category" :value="__('Category')" />
                            <select id="category" name="category" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Select Category</option>
                                <option value="textbooks">Textbooks</option>
                                <option value="stationery">Stationery</option>
                                <option value="uniforms">Uniforms</option>
                                <option value="electronics">Electronics</option>
                                <option value="other">Other</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('category')" />
                        </div>

                        <!-- Quantity -->
                        <div>
                            <x-input-label for="quantity" :value="__('Quantity')" />
                            <x-text-input id="quantity" name="quantity" type="number" class="mt-1 block w-full" min="1" :value="old('quantity', 1)" required />
                            <x-input-error class="mt-2" :messages="$errors->get('quantity')" />
                        </div>

                        <!-- Condition -->
                        <div>
                            <x-input-label for="condition" :value="__('Condition')" />
                            <select id="condition" name="condition" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Select Condition</option>
                                <option value="new">New</option>
                                <option value="like_new">Like New</option>
                                <option value="good">Good</option>
                                <option value="fair">Fair</option>
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('condition')" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>{{ old('description') }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                        <!-- Contact Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Contact Information</h3>

                            <!-- Contact Number -->
                            <div>
                                <x-input-label for="contact_number" :value="__('Contact Number')" />
                                <x-text-input id="contact_number" name="contact_number" type="tel" class="mt-1 block w-full" :value="old('contact_number')" required />
                                <x-input-error class="mt-2" :messages="$errors->get('contact_number')" />
                            </div>

                            <!-- Pickup Address -->
                            <div>
                                <x-input-label for="pickup_address" :value="__('Pickup Address')" />
                                <textarea id="pickup_address" name="pickup_address" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>{{ old('pickup_address') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('pickup_address')" />
                            </div>

                            <!-- Preferred Contact Method -->
                            <div>
                                <x-input-label for="preferred_contact_method" :value="__('Preferred Contact Method')" />
                                <select id="preferred_contact_method" name="preferred_contact_method" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">Select Contact Method</option>
                                    <option value="phone">Phone</option>
                                    <option value="email">Email</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('preferred_contact_method')" />
                            </div>

                            <!-- Preferred Contact Times -->
                            <div>
                                <x-input-label :value="__('Preferred Contact Times')" />
                                <div class="mt-2 space-y-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="preferred_contact_times[]" value="morning" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Morning (9 AM - 12 PM)</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="preferred_contact_times[]" value="afternoon" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Afternoon (12 PM - 5 PM)</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="preferred_contact_times[]" value="evening" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Evening (5 PM - 8 PM)</span>
                                    </label>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('preferred_contact_times')" />
                            </div>
                        </div>

                        <!-- Images -->
                        <div>
                            <x-input-label for="images" :value="__('Images')" />
                            <input type="file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/jpg" class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-400
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-md file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100
                                dark:file:bg-gray-700 dark:file:text-gray-200
                                dark:hover:file:bg-gray-600">
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Upload up to 5 images of your donation items. Accepted formats: JPG, JPEG, PNG. Max size: 2MB per image.</p>
                            <x-input-error class="mt-2" :messages="$errors->get('images')" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Submit Donation') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
