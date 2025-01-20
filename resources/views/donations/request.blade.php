<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Request Donation
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Donation Details -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-xl font-semibold mb-6 text-gray-900 dark:text-gray-100">Donation Details</h3>

                    <!-- Images Gallery -->
                    @php
                        $images = is_string($donation->images) ? json_decode($donation->images, true) : $donation->images;
                        $hasImages = !empty($images) && is_array($images);
                    @endphp

                    @if($hasImages)
                        <div class="mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach($images as $image)
                                    <div class="relative aspect-w-16 aspect-h-9">
                                        <img src="{{ asset('storage/' . $image) }}" 
                                             alt="Donation image" 
                                             class="rounded-lg object-cover w-full h-full"
                                             onerror="this.src='{{ asset('images/placeholder.jpg') }}'">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Basic Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Item Information</h4>
                            <div class="space-y-3">
                                <p class="text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Item Name:</span>
                                    <span class="text-gray-600 dark:text-gray-400 ml-2">{{ $donation->item_name }}</span>
                                </p>
                                <p class="text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Category:</span>
                                    <span class="text-gray-600 dark:text-gray-400 ml-2">{{ ucfirst($donation->category) }}</span>
                                </p>
                                <p class="text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Education Level:</span>
                                    <span class="text-gray-600 dark:text-gray-400 ml-2">{{ ucfirst($donation->education_level) }}</span>
                                </p>
                                <p class="text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Condition:</span>
                                    <span class="text-gray-600 dark:text-gray-400 ml-2">{{ ucfirst(str_replace('_', ' ', $donation->condition)) }}</span>
                                </p>
                                <p class="text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Available Quantity:</span>
                                    <span class="text-gray-600 dark:text-gray-400 ml-2">{{ $donation->available_quantity }}</span>
                                </p>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-4">Donor Information</h4>
                            <div class="space-y-3">
                                <p class="text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Posted By:</span>
                                    <span class="text-gray-600 dark:text-gray-400 ml-2">
                                        {{ $donation->is_anonymous ? 'Anonymous Donor' : $donation->user->name }}
                                    </span>
                                </p>
                                @if(!$donation->is_anonymous && $donation->show_contact_details)
                                    <p class="text-sm">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Contact Method:</span>
                                        <span class="text-gray-600 dark:text-gray-400 ml-2">{{ ucfirst($donation->preferred_contact_method) }}</span>
                                    </p>
                                    <p class="text-sm">
                                        <span class="font-medium text-gray-700 dark:text-gray-300">Preferred Times:</span>
                                        <span class="text-gray-600 dark:text-gray-400 ml-2">
                                            {{ implode(', ', array_map('ucfirst', $donation->preferred_contact_times)) }}
                                        </span>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Description</h4>
                        <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $donation->description }}</p>
                    </div>
                </div>
            </div>

            <!-- Request Form -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Request Form</h3>

                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                            <strong class="font-bold">Please fix the following errors:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('donations.request.store', $donation) }}" class="space-y-6" enctype="multipart/form-data">
                        @csrf

                        <!-- Document Type -->
                        <div>
                            <x-input-label for="document_type" value="Verification Document Type" />
                            <select id="document_type" name="document_type" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                <option value="">Select a document type</option>
                                <option value="student_id" {{ old('document_type') == 'student_id' ? 'selected' : '' }}>Student ID Card</option>
                                <option value="parent_nic" {{ old('document_type') == 'parent_nic' ? 'selected' : '' }}>Parent's NIC</option>
                                <option value="school_record" {{ old('document_type') == 'school_record' ? 'selected' : '' }}>School Record Book</option>
                                <option value="institution_id" {{ old('document_type') == 'institution_id' ? 'selected' : '' }}>Institution ID</option>
                            </select>
                            <p class="mt-1 text-sm text-gray-500">Please select the type of document you will provide for verification.</p>
                            <x-input-error :messages="$errors->get('document_type')" class="mt-2" />
                        </div>

                        <!-- Verification Document Upload -->
                        <div>
                            <x-input-label for="verification_document" value="Upload Verification Document" />
                            <input type="file" id="verification_document" name="verification_document" 
                                   class="mt-1 block w-full text-sm text-gray-500
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-md file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-indigo-50 file:text-indigo-700
                                          hover:file:bg-indigo-100" 
                                   accept=".pdf,.jpg,.jpeg,.png" required />
                            <p class="mt-1 text-sm text-gray-500">Please upload a clear image/scan of your verification document (PDF, JPG, PNG formats accepted, max 2MB)</p>
                            <x-input-error :messages="$errors->get('verification_document')" class="mt-2" />
                        </div>

                        <!-- Quantity Needed -->
                        <div>
                            <x-input-label for="quantity" value="Quantity Needed" />
                            <x-text-input id="quantity" name="quantity" type="number" min="1" max="{{ $donation->available_quantity }}" 
                                         class="mt-1 block w-full" :value="old('quantity')" required />
                            <p class="mt-1 text-sm text-gray-500">Maximum available: {{ $donation->available_quantity }}</p>
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                        </div>

                        <!-- Purpose -->
                        <div>
                            <x-input-label for="purpose" value="Purpose" />
                            <textarea id="purpose" name="purpose" rows="3" 
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" 
                                      required>{{ old('purpose') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">Please explain why you need this item and how it will help your education.</p>
                            <x-input-error :messages="$errors->get('purpose')" class="mt-2" />
                        </div>



                        <!-- Contact Number -->
                        <div>
                            <x-input-label for="contact_number" value="Contact Number" />
                            <x-text-input id="contact_number" name="contact_number" type="tel" class="mt-1 block w-full" 
                                         :value="old('contact_number')" required />
                            <x-input-error :messages="$errors->get('contact_number')" class="mt-2" />
                        </div>

                        <!-- Additional Notes -->
                        <div>
                            <x-input-label for="notes" value="Additional Notes (Optional)" />
                            <textarea id="notes" name="notes" rows="2" 
                                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('notes') }}</textarea>
                            <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                        </div>

                        <div class="flex justify-end gap-4">
                            <a href="{{ route('donations.available') }}" 
                               class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancel
                            </a>
                            <x-primary-button>
                                Submit Request
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
