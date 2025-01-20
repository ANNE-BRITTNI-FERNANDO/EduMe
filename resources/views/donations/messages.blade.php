<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Messages for Donation Request
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-2">Donation Details</h3>
                    <p class="text-gray-600 dark:text-gray-400">Item: {{ $donationRequest->donationItem->item_name }}</p>
                    <p class="text-gray-600 dark:text-gray-400">Status: {{ ucfirst($donationRequest->status) }}</p>
                </div>

                <div class="space-y-4 mb-6">
                    @foreach($messages as $message)
                        <div class="p-4 rounded-lg {{ $message->sender_id === auth()->id() ? 'bg-blue-100 dark:bg-blue-900 ml-12' : 'bg-gray-100 dark:bg-gray-700 mr-12' }}">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-semibold text-sm">
                                    {{ $message->sender_id === auth()->id() ? 'You' : $message->sender->name }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $message->created_at->format('M j, Y g:i A') }}
                                </span>
                            </div>
                            <p class="text-gray-800 dark:text-gray-200">{{ $message->message }}</p>
                        </div>
                    @endforeach
                </div>

                @if($donationRequest->status === 'approved')
                    <form method="POST" action="{{ route('donations.messages.store', $donationRequest) }}" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="message" value="New Message" />
                            <textarea
                                id="message"
                                name="message"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                required
                            ></textarea>
                            <x-input-error :messages="$errors->get('message')" class="mt-2" />
                        </div>

                        <input type="hidden" name="receiver_id" value="{{ $donationRequest->user_id === auth()->id() ? $donationRequest->donationItem->user_id : $donationRequest->user_id }}">

                        <x-primary-button>
                            Send Message
                        </x-primary-button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
