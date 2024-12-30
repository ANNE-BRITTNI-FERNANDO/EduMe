<section>
    <header>
        <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and delivery details.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="space-y-6">
            <!-- Name -->
            <div>
                <label class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                    {{ __('Name') }}
                </label>
                <input type="text" id="name" name="name" 
                    class="block w-full px-4 py-3 text-gray-900 dark:text-gray-100 bg-white/50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm backdrop-blur-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                    value="{{ old('name', Auth::user()?->name) }}" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <!-- Email -->
            <div>
                <label class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                    {{ __('Email') }}
                </label>
                <input type="email" id="email" name="email"
                    class="block w-full px-4 py-3 text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm backdrop-blur-sm transition-colors duration-200"
                    value="{{ old('email', Auth::user()?->email) }}" readonly disabled />
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Email address cannot be changed to protect your account and product data. Please contact support if you need to update your email.') }}
                </p>
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if (Auth::user() && Auth::user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! Auth::user()->hasVerifiedEmail())
                    <div>
                        <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                            {{ __('Your email address is unverified.') }}

                            <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Phone -->
            <div>
                <label class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                    {{ __('Phone Number') }}
                </label>
                <input type="tel" id="phone" name="phone"
                    class="block w-full px-4 py-3 text-gray-900 dark:text-gray-100 bg-white/50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm backdrop-blur-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200"
                    value="{{ old('phone', Auth::user()?->phone) }}" required autocomplete="tel" />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <!-- Province -->
            <div>
                <label class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                    {{ __('Province') }}
                </label>
                <select name="province" id="province" 
                    class="block w-full px-4 py-3 text-gray-900 dark:text-gray-100 bg-white/50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm backdrop-blur-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200" 
                    required>
                    <option value="">Select Province</option>
                    <option value="Western" {{ old('province', Auth::user()?->province) === 'Western' ? 'selected' : '' }}>Western Province</option>
                    <option value="Central" {{ old('province', Auth::user()?->province) === 'Central' ? 'selected' : '' }}>Central Province</option>
                    <option value="Southern" {{ old('province', Auth::user()?->province) === 'Southern' ? 'selected' : '' }}>Southern Province</option>
                    <option value="Northern" {{ old('province', Auth::user()?->province) === 'Northern' ? 'selected' : '' }}>Northern Province</option>
                    <option value="Eastern" {{ old('province', Auth::user()?->province) === 'Eastern' ? 'selected' : '' }}>Eastern Province</option>
                    <option value="North Western" {{ old('province', Auth::user()?->province) === 'North Western' ? 'selected' : '' }}>North Western Province</option>
                    <option value="North Central" {{ old('province', Auth::user()?->province) === 'North Central' ? 'selected' : '' }}>North Central Province</option>
                    <option value="Uva" {{ old('province', Auth::user()?->province) === 'Uva' ? 'selected' : '' }}>Uva Province</option>
                    <option value="Sabaragamuwa" {{ old('province', Auth::user()?->province) === 'Sabaragamuwa' ? 'selected' : '' }}>Sabaragamuwa Province</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('province')" />
            </div>

            <!-- District -->
            <div>
                <label class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                    {{ __('District') }}
                </label>
                <select name="location" id="location" 
                    class="block w-full px-4 py-3 text-gray-900 dark:text-gray-100 bg-white/50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm backdrop-blur-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200" 
                    required>
                    <option value="">Select District</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('location')" />
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Select your district for delivery</p>
            </div>

            <!-- Address -->
            <div>
                <label class="block font-medium text-sm text-gray-900 dark:text-gray-100">
                    {{ __('Street Address') }}
                </label>
                <textarea id="address" name="address" rows="3" 
                    class="block w-full px-4 py-3 text-gray-900 dark:text-gray-100 bg-white/50 dark:bg-gray-700/50 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm backdrop-blur-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors duration-200" 
                    required>{{ old('address', Auth::user()?->address) }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-6">
            <button type="submit" 
                class="inline-flex items-center px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-base font-medium rounded-xl transition-all duration-150 ease-in-out transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ __('Save Changes') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600 dark:text-green-400">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>
