<div class="message {{ $message->user_id === Auth::id() ? 'sent' : 'received' }} mb-4">
    <div class="flex items-start {{ $message->user_id === Auth::id() ? 'justify-end' : 'justify-start' }}">
        <div class="max-w-[70%] bg-{{ $message->user_id === Auth::id() ? 'blue-500' : 'gray-100' }} rounded-lg p-3 shadow">
            @if($message->content)
                <p class="text-{{ $message->user_id === Auth::id() ? 'white' : 'gray-800' }} break-words">
                    {!! nl2br(e($message->content)) !!}
                </p>
            @endif
            
            @if($message->attachment_url)
                <div class="mt-2">
                    @if(Str::startsWith($message->attachment_type, 'image/'))
                        <img src="{{ Storage::url($message->attachment_url) }}" alt="Attachment" class="max-w-full rounded">
                    @else
                        <a href="{{ Storage::url($message->attachment_url) }}" 
                           target="_blank"
                           class="flex items-center text-{{ $message->user_id === Auth::id() ? 'white' : 'blue-500' }} hover:underline">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Download Attachment
                        </a>
                    @endif
                </div>
            @endif
            
            <div class="text-xs text-{{ $message->user_id === Auth::id() ? 'blue-200' : 'gray-500' }} mt-1">
                {{ $message->created_at->format('g:i A') }}
            </div>
        </div>
    </div>
</div>
