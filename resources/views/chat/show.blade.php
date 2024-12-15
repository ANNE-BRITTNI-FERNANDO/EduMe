<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Chat with {{ auth()->id() === $conversation->seller_id ? $conversation->buyer->name : $conversation->seller->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-col h-screen">
                        <!-- Product/Bundle Details -->
                        <div class="bg-white dark:bg-gray-800 shadow-sm p-4 border-b">
                            @if($conversation->product)
                                <div class="flex items-center space-x-4">
                                    <img src="{{ $conversation->product->image_url }}" alt="{{ $conversation->product->product_name }}" class="w-16 h-16 object-cover rounded-lg">
                                    <div>
                                        <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $conversation->product->product_name }}</h3>
                                        <p class="text-gray-600 dark:text-gray-400">₹{{ number_format($conversation->product->price, 2) }}</p>
                                    </div>
                                </div>
                            @endif
                            @if($conversation->bundle)
                                <div class="space-y-4">
                                    <!-- Bundle Main Info -->
                                    <div class="flex items-center space-x-4">
                                        <img src="{{ asset('storage/' . $conversation->bundle->bundle_image) }}" alt="{{ $conversation->bundle->bundle_name }}" class="w-20 h-20 object-cover rounded-lg">
                                        <div>
                                            <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $conversation->bundle->bundle_name }}</h3>
                                            <p class="text-gray-600 dark:text-gray-400">₹{{ number_format($conversation->bundle->price, 2) }}</p>
                                        </div>
                                    </div>
                                    <!-- Bundle Categories -->
                                    <div class="mt-4">
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Bundle Items:</h4>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            @foreach($conversation->bundle->categories as $category)
                                                <div class="flex items-center space-x-3 bg-gray-50 dark:bg-gray-700 p-2 rounded-lg">
                                                    <img src="{{ asset('storage/' . $category->category_image) }}" alt="{{ $category->category }}" class="w-12 h-12 object-cover rounded">
                                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $category->category }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Chat Messages -->
                        <div class="flex-1 overflow-y-auto p-4 space-y-4" id="messages-container">
                            @foreach($messages as $message)
                                <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                                    <div class="max-w-[70%] {{ $message->sender_id === auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-100' }} rounded-lg p-3 shadow">
                                        @if($message->attachment_path)
                                            @if(in_array($message->attachment_type, ['image/jpeg', 'image/png', 'image/gif']))
                                                <img src="{{ $message->attachment_url }}" alt="Attachment" class="max-w-full rounded-lg mb-2">
                                            @else
                                                <a href="{{ $message->attachment_url }}" target="_blank" class="text-blue-500 underline">
                                                    View Attachment
                                                </a>
                                            @endif
                                        @endif
                                        
                                        <div class="break-words">
                                            {!! preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1" target="_blank" class="underline">$1</a>', e($message->content)) !!}
                                        </div>
                                        
                                        <div class="text-xs mt-1 {{ $message->sender_id === auth()->id() ? 'text-blue-100' : 'text-gray-500' }}">
                                            {{ $message->created_at->format('g:i A') }}
                                            @if($message->sender_id === auth()->id())
                                                @if($message->read_at)
                                                    <span class="ml-1">✓✓</span>
                                                @else
                                                    <span class="ml-1">✓</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Message Input -->
                        <div class="border-t p-4 bg-white">
                            <form id="messageForm" action="{{ route('messages.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="conversation_id" value="{{ $conversation->id }}">
                                <div class="flex space-x-4">
                                    <div class="relative flex-1">
                                        <input type="text" name="content" id="content" class="w-full border rounded-lg px-4 py-2 pr-10" placeholder="Type your message...">
                                        <label for="attachment" class="absolute right-2 top-2 text-gray-500 hover:text-gray-700 cursor-pointer">
                                            <input type="file" name="attachment" id="attachment" class="hidden" accept="image/*,.pdf,.doc,.docx">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                        </label>
                                        <div id="file-info" class="text-sm text-gray-500 mt-1"></div>
                                    </div>
                                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600">Send</button>
                                </div>
                            </form>
                            @if($errors->any())
                                <div class="text-red-500 text-sm mt-2">
                                    @foreach($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif
                            @if(session('error'))
                                <div class="text-red-500 text-sm mt-2">
                                    {{ session('error') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh messages every 3 seconds
        let refreshInterval = setInterval(refreshMessages, 3000);

        function refreshMessages() {
            fetch('{{ route("messages.get", $conversation->id) }}')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('messages-container');
                    let html = '';
                    
                    data.messages.forEach(message => {
                        const isCurrentUser = message.sender_id === {{ auth()->id() }};
                        const justify = isCurrentUser ? 'justify-end' : 'justify-start';
                        const bgColor = isCurrentUser ? 'bg-blue-500 text-white' : 'bg-gray-100';
                        const timeColor = isCurrentUser ? 'text-blue-100' : 'text-gray-500';
                        
                        let attachmentHtml = '';
                        if (message.attachment_path) {
                            if (['image/jpeg', 'image/png', 'image/gif'].includes(message.attachment_type)) {
                                attachmentHtml = `<img src="${message.attachment_url}" alt="Attachment" class="max-w-full rounded-lg mb-2">`;
                            } else {
                                attachmentHtml = `<a href="${message.attachment_url}" target="_blank" class="text-blue-500 underline">View Attachment</a>`;
                            }
                        }

                        html += `
                            <div class="flex ${justify}">
                                <div class="max-w-[70%] ${bgColor} rounded-lg p-3 shadow">
                                    ${attachmentHtml}
                                    <div class="break-words">${message.content || ''}</div>
                                    <div class="text-xs mt-1 ${timeColor}">
                                        ${new Date(message.created_at).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })}
                                        ${isCurrentUser ? (message.read_at ? '<span class="ml-1">✓✓</span>' : '<span class="ml-1">✓</span>') : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    container.innerHTML = html;
                    container.scrollTop = container.scrollHeight;
                });
        }

        // Handle file selection
        document.getElementById('attachment').addEventListener('change', function(e) {
            const fileInfo = document.getElementById('file-info');
            const contentInput = document.getElementById('content');
            
            if (this.files && this.files[0]) {
                const file = this.files[0];
                fileInfo.textContent = `Selected: ${file.name}`;
                
                // If no message content, set a default message
                if (!contentInput.value) {
                    contentInput.value = 'Sent an attachment';
                }
            } else {
                fileInfo.textContent = '';
            }
        });

        // Handle form submission
        document.getElementById('messageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Clear the refresh interval while uploading
            clearInterval(refreshInterval);
            
            // Submit form data using fetch
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(() => {
                // Clear inputs
                document.getElementById('content').value = '';
                document.getElementById('attachment').value = '';
                document.getElementById('file-info').textContent = '';
                
                // Refresh messages immediately
                refreshMessages();
                
                // Resume refresh interval
                refreshInterval = setInterval(refreshMessages, 3000);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to send message. Please try again.');
            });
        });

        // Mark messages as read when the page loads
        fetch('{{ route("messages.markAllRead", $conversation->id) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        });
    </script>
</x-app-layout>
