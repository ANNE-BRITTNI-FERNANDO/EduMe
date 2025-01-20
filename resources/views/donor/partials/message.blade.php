<div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
    <div class="max-w-[70%] {{ $message->sender_id === auth()->id() ? 'bg-indigo-100 dark:bg-indigo-900' : 'bg-gray-100 dark:bg-gray-700' }} rounded-lg px-4 py-2 shadow">
        @if($message->sender_id !== auth()->id())
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $message->sender->name }}</div>
        @endif
        <div class="text-gray-800 dark:text-gray-200">{{ $message->content }}</div>
        <div class="text-xs text-gray-500 dark:text-gray-400 text-right mt-1">
            {{ \Carbon\Carbon::parse($message->created_at)->format('M d, g:i A') }}
            @if($message->read_at && $message->sender_id === auth()->id())
                <span class="ml-1">✓</span>
            @endif
        </div>
    </div>
</div>
