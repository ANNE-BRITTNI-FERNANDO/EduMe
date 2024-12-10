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
                    <!-- Product Info -->
                    <!-- Item Info -->
                    <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                        @if($conversation->product_id)
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Product: {{ $conversation->product->product_name }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Price: ${{ number_format($conversation->product->price, 2) }}
                            </p>
                            @if(Auth::id() === $conversation->buyer_id)
                                <div class="mt-4">
                                    <button onclick="showBankTransferModal({{ $conversation->product->price }})" 
                                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        Pay via Bank Transfer
                                    </button>
                                </div>
                            @endif
                        @else
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                Bundle: {{ $conversation->bundle->bundle_name }}
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Price: ${{ number_format($conversation->bundle->price, 2) }}
                            </p>
                            @if(Auth::id() === $conversation->buyer_id)
                                <div class="mt-4">
                                    <button onclick="showBankTransferModal({{ $conversation->bundle->price }})" 
                                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                        </svg>
                                        Pay via Bank Transfer
                                    </button>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Bank Transfer Modal -->
                    <div id="bankTransferModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
                        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                            <div class="mt-3">
                                <h3 class="text-lg font-medium text-gray-900">Bank Transfer Details</h3>
                                <div class="mt-2 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                                        <input type="text" id="transferAmount" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" readonly>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Bank Details</label>
                                        <textarea id="bankDetails" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Enter bank transfer details..."></textarea>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Upload Payment Slip</label>
                                        <input type="file" 
                                               id="paymentSlip" 
                                               accept=".jpg,.jpeg,.png,.pdf"
                                               class="mt-1 block w-full text-sm text-gray-500
                                                      file:mr-4 file:py-2 file:px-4
                                                      file:rounded-md file:border-0
                                                      file:text-sm file:font-semibold
                                                      file:bg-indigo-50 file:text-indigo-700
                                                      hover:file:bg-indigo-100"
                                               max="5242880">
                                        <p class="mt-1 text-sm text-gray-500">
                                            Accepted formats: JPG, PNG, PDF (Max 5MB)
                                        </p>
                                    </div>
                                    <div class="flex justify-end space-x-3">
                                        <button onclick="closeBankTransferModal()" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                                        <button onclick="createBankTransferOrder()" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Submit Payment</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Confirmation Modal (for Seller) -->
                    <div id="paymentConfirmationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
                        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                            <div class="mt-3">
                                <h3 class="text-lg font-medium text-gray-900">Confirm Payment</h3>
                                <div class="mt-4 space-y-4">
                                    <div id="paymentSlipPreview" class="w-full h-64 bg-gray-100 flex items-center justify-center">
                                        <!-- Payment slip will be displayed here -->
                                    </div>
                                    <div class="flex justify-end space-x-3">
                                        <button onclick="rejectPayment()" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Reject</button>
                                        <button onclick="confirmPayment()" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Confirm Payment</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Container -->
                    <div class="chat-messages space-y-4 mb-4 h-96 overflow-y-auto p-4 bg-gray-50 dark:bg-gray-900 rounded-lg" id="chat-messages">
                        @foreach($messages as $message)
                            <div class="message {{ $message->sender_id === auth()->id() ? 'text-right' : 'text-left' }}">
                                <div class="inline-block max-w-lg">
                                    <div class="px-4 py-2 rounded-lg {{ $message->sender_id === auth()->id() ? 'bg-indigo-600 text-white ml-auto' : 'bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100' }}">
                                        {{ $message->content }}
                                    </div>
                                    <div class="text-sm {{ $message->sender_id === auth()->id() ? 'text-gray-500' : 'text-gray-600' }} dark:text-gray-400 mt-1">
                                        {{ $message->sender_id === auth()->id() ? 'You' : $message->sender->name }} • {{ $message->created_at->diffForHumans() }}
                                        @if($message->read_at && $message->sender_id === auth()->id())
                                            <span class="text-xs text-gray-400">• Read</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Message Input -->
                    <form id="message-form" method="POST" action="{{ route('chat.message', $conversation) }}" class="mt-4">
                        @csrf
                        <div class="flex items-center space-x-3">
                            <input type="text" 
                                   name="content" 
                                   id="message-input"
                                   class="flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm" 
                                   placeholder="Type your message..."
                                   required>
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Send
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Only initialize once
        if (!window.chatInitialized) {
            window.chatInitialized = true;
            
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Chat view loaded');
                const messageForm = document.getElementById('message-form');
                const messageInput = document.getElementById('message-input');
                const messagesContainer = document.getElementById('chat-messages');

                if (!messageForm || !messageInput || !messagesContainer) {
                    console.error('Required elements not found');
                    return;
                }

                // Scroll to bottom of messages
                function scrollToBottom() {
                    console.log('Scrolling to bottom');
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                }
                scrollToBottom();

                messageForm.addEventListener('submit', function(e) {
                    console.log('Form submitted');
                    e.preventDefault();
                    
                    const content = messageInput.value.trim();
                    if (!content) return;

                    const formData = new FormData();
                    formData.append('content', content);
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch(messageForm.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        console.log('Response received:', response);
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Message data:', data);
                        if (!data.message || !data.message.content) {
                            throw new Error('Invalid message data received');
                        }
                        
                        // Add new message to the chat
                        const messageHtml = `
                            <div class="message text-right">
                                <div class="inline-block max-w-lg">
                                    <div class="px-4 py-2 rounded-lg bg-indigo-600 text-white ml-auto">
                                        ${data.message.content}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        You • Just now
                                    </div>
                                </div>
                            </div>
                        `;
                        messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
                        messageInput.value = '';
                        scrollToBottom();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to send message. Please try again.');
                    });
                });
            });
        }

        let currentAmount = 0;

        function showBankTransferModal(amount) {
            document.getElementById('transferAmount').value = amount.toFixed(2);
            document.getElementById('bankTransferModal').classList.remove('hidden');
        }

        function closeBankTransferModal() {
            document.getElementById('bankTransferModal').classList.add('hidden');
            document.getElementById('bankDetails').value = '';
            document.getElementById('paymentSlip').value = '';
        }

        // Add CSRF token to meta tag if not exists
        if (!document.querySelector('meta[name="csrf-token"]')) {
            const meta = document.createElement('meta');
            meta.name = 'csrf-token';
            meta.content = '{{ csrf_token() }}';
            document.head.appendChild(meta);
        }

        function createBankTransferOrder() {
            const bankDetails = document.getElementById('bankDetails').value.trim();
            const paymentSlip = document.getElementById('paymentSlip').files[0];
            const amount = document.getElementById('transferAmount').value;
            
            if (!bankDetails) {
                alert('Please enter bank transfer details');
                return;
            }

            if (!paymentSlip) {
                alert('Please upload payment slip');
                return;
            }

            if (paymentSlip.size > 5242880) {
                alert('File size must be less than 5MB');
                return;
            }

            const formData = new FormData();
            formData.append('bank_details', bankDetails);
            formData.append('payment_slip', paymentSlip);
            formData.append('amount', amount);

            fetch('/bank-transfer/{{ $conversation->id }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch {
                            throw new Error(text);
                        }
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    closeBankTransferModal();
                    alert('Payment slip uploaded successfully. Waiting for seller confirmation.');
                    location.reload(); // Refresh to show the new message
                } else {
                    alert(data.message || 'Error submitting payment. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error details:', error);
                alert(error.message || 'Error submitting payment. Please try again.');
            });
        }

        // For Seller: Show payment confirmation modal
        function showPaymentConfirmationModal(paymentSlipUrl) {
            const preview = document.getElementById('paymentSlipPreview');
            
            if (paymentSlipUrl.toLowerCase().endsWith('.pdf')) {
                preview.innerHTML = `<embed src="${paymentSlipUrl}" type="application/pdf" width="100%" height="100%">`;
            } else {
                preview.innerHTML = `<img src="${paymentSlipUrl}" class="max-w-full max-h-full object-contain">`;
            }
            
            document.getElementById('paymentConfirmationModal').classList.remove('hidden');
        }

        function confirmPayment() {
            fetch('{{ route("bank-transfer.confirm", ["conversation" => $conversation->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('paymentConfirmationModal').classList.add('hidden');
                    alert('Payment confirmed successfully.');
                } else {
                    alert(data.message || 'Error confirming payment. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error confirming payment. Please try again.');
            });
        }

        function rejectPayment() {
            const reason = prompt('Please enter reason for rejection:');
            if (!reason) return;

            fetch('{{ route("bank-transfer.reject", ["conversation" => $conversation->id]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ reason })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('paymentConfirmationModal').classList.add('hidden');
                    alert('Payment rejected successfully.');
                } else {
                    alert(data.message || 'Error rejecting payment. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error rejecting payment. Please try again.');
            });
        }
    </script>
    @endpush
</x-app-layout>
