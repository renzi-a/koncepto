<div id="popupChatWindow" class="fixed bottom-20 right-6 w-80 bg-white border shadow-lg rounded-xl z-50 flex flex-col max-h-[70vh]">
    <div class="bg-[#56AB2F] text-white px-4 py-2 flex justify-between items-center">
        <span class="font-semibold">KONCEPTO Chat</span>
        <button onclick="document.getElementById('inlineMiniChat').remove()">×</button>
    </div>

    <div id="popupMessages" class="p-3 text-sm overflow-y-auto flex-1 space-y-2 max-h-60">
        @foreach ($messages as $message)
            <div class="text-sm {{ $message->sender_id === auth()->id() ? 'text-right' : 'text-left' }}">
                <div class="{{ $message->sender_id === auth()->id() ? 'bg-green-500 text-white' : 'bg-gray-200 text-black' }} inline-block px-3 py-1 rounded-lg">
                    {{ $message->message }}
                </div>
            </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('user.chat.send') }}" class="flex gap-2 p-2 border-t">
        @csrf
        <input type="text" name="message" class="flex-1 border px-2 py-1 rounded text-sm" placeholder="Type..." />
        <button type="submit" class="bg-[#56AB2F] text-white px-3 py-1 rounded">Send</button>
    </form>
</div>
