<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Chat: {{ $conversation->title }}
            </h2>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Chatting with: {{ $otherUser->name }}
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Donation Request Details -->
            @if($conversation->donationRequest && $conversation->donationRequest->donationItem)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-4">
                    <div class="p-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Item Details</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Item:</span> {{ $conversation->donationRequest->donationItem->item_name }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Category:</span> {{ ucfirst($conversation->donationRequest->donationItem->category) }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Quantity:</span> {{ $conversation->donationRequest->quantity }}
                                </p>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Request Status</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-medium">Status:</span>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $conversation->donationRequest->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                           ($conversation->donationRequest->status === 'rejected' ? 'bg-red-100 text-red-800' : 
                                           'bg-yellow-100 text-yellow-800') }}">
                                        {{ ucfirst($conversation->donationRequest->status) }}
                                    </span>
                                </p>
                                @if($conversation->donationRequest->pickup_date && !is_string($conversation->donationRequest->pickup_date))
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">Pickup Date:</span> 
                                        {{ $conversation->donationRequest->pickup_date->format('M d, Y') }}
                                    </p>
                                @elseif($conversation->donationRequest->pickup_date)
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">Pickup Date:</span> 
                                        {{ $conversation->donationRequest->pickup_date }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Chat Messages -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4">
                    <!-- Messages Container -->
                    <div id="messages" class="space-y-4 h-96 overflow-y-auto mb-4 messages-container" style="height: 400px; overflow-y: auto;">
                        @foreach($conversation->messages as $message)
                            @include('donor.partials.message', ['message' => $message])
                        @endforeach
                    </div>

                    <!-- Message Input -->
                    <form id="messageForm" class="mt-4 message-form">
                        @csrf
                        <div class="flex space-x-2 input-group">
                            <input type="text" 
                                   name="message" 
                                   id="messageInput"
                                   class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 form-control"
                                   placeholder="Type your message...">
                            <div class="input-group-append">
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 btn btn-primary">
                                    Send
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messagesContainer = document.getElementById('messages');
            const messageForm = document.getElementById('messageForm');
            const messageInput = document.getElementById('messageInput');
            let lastMessageId = '{{ $conversation->messages->last()?->id ?? 0 }}';

            // Scroll to bottom initially
            scrollToBottom();

            // Handle form submission
            messageForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                const message = messageInput.value.trim();
                if (!message) return;

                try {
                    const response = await fetch('{{ route("donation.chat.send", $conversation->donationRequest->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ message })
                    });

                    if (!response.ok) throw new Error('Failed to send message');

                    const data = await response.json();
                    const messageHtml = createMessageHtml(data.message);
                    messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
                    messageInput.value = '';
                    scrollToBottom();
                    lastMessageId = data.message.id;
                } catch (error) {
                    console.error('Error:', error);
                    alert('Failed to send message. Please try again.');
                }
            });

            // Function to create message HTML
            function createMessageHtml(message) {
                const isCurrentUser = message.sender_id === {{ auth()->id() }};
                return `
                    <div class="flex ${isCurrentUser ? 'justify-end' : 'justify-start'}">
                        <div class="max-w-[70%] ${isCurrentUser ? 'bg-indigo-100' : 'bg-gray-100'} rounded-lg px-4 py-2 shadow">
                            ${!isCurrentUser ? `<div class="text-xs text-gray-500 mb-1">${message.sender.name}</div>` : ''}
                            <div class="text-gray-800">${message.content}</div>
                            <div class="text-xs text-gray-500 text-right mt-1">
                                ${new Date(message.created_at).toLocaleString('en-US', {
                                    month: 'short',
                                    day: 'numeric',
                                    hour: 'numeric',
                                    minute: 'numeric',
                                    hour12: true
                                })}
                            </div>
                        </div>
                    </div>
                `;
            }

            // Auto-scroll to bottom
            function scrollToBottom() {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            // Check for new messages periodically
            setInterval(async function() {
                try {
                    const response = await fetch(`{{ route("donation.chat.new", [$conversation->id, ':lastMessageId']) }}`.replace(':lastMessageId', lastMessageId));
                    if (!response.ok) throw new Error('Failed to fetch new messages');

                    const data = await response.json();
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(message => {
                            const messageHtml = createMessageHtml(message);
                            messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
                            lastMessageId = message.id;
                        });
                        scrollToBottom();
                    }
                } catch (error) {
                    console.error('Error fetching new messages:', error);
                }
            }, 3000);
        });
    </script>
    @endpush
</x-app-layout>
