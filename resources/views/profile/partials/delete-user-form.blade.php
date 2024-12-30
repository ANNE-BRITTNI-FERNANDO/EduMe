<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Before deleting your account, please note:') }}
        </p>
        <ul class="mt-2 list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
            <li>{{ __('All your active products will be marked as unavailable') }}</li>
            <li>{{ __('Your bundles will no longer be available for purchase') }}</li>
            <li>{{ __('You cannot delete your account if you have:') }}
                <ul class="ml-6 list-disc list-inside">
                    <li>{{ __('Active orders (pending or processing)') }}</li>
                    <li>{{ __('Available balance in your seller account') }}</li>
                </ul>
            </li>
            <li>{{ __('Order history will be preserved for record-keeping') }}</li>
        </ul>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Delete Account') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('This action cannot be undone. All your products will be made unavailable and your account will be permanently deleted.') }}
            </p>

            @error('deletion_error')
                <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg">
                    {{ $message }}
                </div>
            @enderror

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('Password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('Delete Account') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
