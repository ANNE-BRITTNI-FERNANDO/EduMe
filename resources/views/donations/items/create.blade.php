<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Donate an Item
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('donation.items.store') }}" class="space-y-6" enctype="multipart/form-data">
                        @csrf

                        <!-- Category -->
                        <div>
                            <x-input-label for="category" value="Category" />
                            <select id="category" name="category" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Select Category</option>
                                <option value="textbooks">Textbooks</option>
                                <option value="stationery">Stationery</option>
                                <option value="devices">Electronic Devices</option>
                                <option value="other">Other</option>
                            </select>
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>

                        <!-- Education Level -->
                        <div>
                            <x-input-label for="education_level" value="Education Level" />
                            <select id="education_level" name="education_level" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Select Level</option>
                                <option value="primary">Primary</option>
                                <option value="secondary">Secondary</option>
                                <option value="tertiary">Tertiary</option>
                            </select>
                            <x-input-error :messages="$errors->get('education_level')" class="mt-2" />
                        </div>

                        <!-- Item Name -->
                        <div>
                            <x-input-label for="item_name" value="Item Name" />
                            <x-text-input id="item_name" name="item_name" type="text" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('item_name')" class="mt-2" />
                        </div>

                        <!-- Description -->
                        <div>
                            <x-input-label for="description" value="Description" />
                            <textarea id="description" name="description" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required></textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <!-- Condition -->
                        <div>
                            <x-input-label for="condition" value="Condition" />
                            <select id="condition" name="condition" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Select Condition</option>
                                <option value="new">New</option>
                                <option value="like_new">Like New</option>
                                <option value="good">Good</option>
                                <option value="fair">Fair</option>
                            </select>
                            <x-input-error :messages="$errors->get('condition')" class="mt-2" />
                        </div>

                        <!-- Quantity -->
                        <div>
                            <x-input-label for="quantity" value="Quantity Available" />
                            <x-text-input id="quantity" name="quantity" type="number" min="1" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                        </div>

                        <!-- Contact Number -->
                        <div>
                            <x-input-label for="contact_number" value="Contact Number" />
                            <x-text-input id="contact_number" name="contact_number" type="tel" class="mt-1 block w-full" required />
                            <x-input-error :messages="$errors->get('contact_number')" class="mt-2" />
                        </div>

                        <!-- Contact Preferences -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-medium mb-4">Contact Preferences</h3>
                            
                            <!-- Preferred Contact Method -->
                            <div class="mb-4">
                                <x-input-label for="preferred_contact_method" value="Preferred Contact Method" />
                                <select id="preferred_contact_method" name="preferred_contact_method" class="mt-1 block w-full" required>
                                    <option value="">Select Contact Method</option>
                                    <option value="phone">Phone Only</option>
                                    <option value="email">Email Only</option>
                                    <option value="both">Both Phone and Email</option>
                                </select>
                                <x-input-error :messages="$errors->get('preferred_contact_method')" class="mt-2" />
                            </div>

                            <!-- Preferred Contact Times -->
                            <div class="mb-4">
                                <x-input-label value="Preferred Contact Times" />
                                <div class="mt-2 space-y-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="preferred_contact_times[]" value="morning">
                                        <span class="ml-2">Morning (8AM - 12PM)</span>
                                    </label>
                                    <br>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="preferred_contact_times[]" value="afternoon">
                                        <span class="ml-2">Afternoon (12PM - 5PM)</span>
                                    </label>
                                    <br>
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="preferred_contact_times[]" value="evening">
                                        <span class="ml-2">Evening (5PM - 9PM)</span>
                                    </label>
                                </div>
                                <x-input-error :messages="$errors->get('preferred_contact_times')" class="mt-2" />
                            </div>

                            <!-- Contact Privacy -->
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="show_contact_details" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2">Show my contact details to approved requesters</span>
                                </label>
                                <p class="text-sm text-gray-500 mt-1">If unchecked, communication will be through our secure messaging system only.</p>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <x-input-label for="notes" value="Additional Notes (Optional)" />
                            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <!-- Images -->
                        <div>
                            <x-input-label for="images" value="Images (Optional)" />
                            <input type="file" id="images" name="images[]" multiple accept="image/*" class="mt-1 block w-full" />
                            <p class="text-sm text-gray-500 mt-1">You can upload multiple images. Supported formats: JPEG, PNG</p>
                            <x-input-error :messages="$errors->get('images')" class="mt-2" />
                        </div>

                        <!-- Anonymous Donation -->
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_anonymous" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="ml-2">Make this donation anonymous</span>
                            </label>
                        </div>

                        <div class="flex justify-end">
                            <x-primary-button>
                                Create Donation
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
