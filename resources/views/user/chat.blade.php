<x-nav-link />

<div class="max-w-7xl mx-auto mt-10 bg-white p-8 shadow-xl rounded-2xl flex flex-col min-h-[80vh]">
    <h2 class="text-2xl font-bold mb-6">Chat</h2>

    <div id="chatMessages" class="flex-1 overflow-y-auto p-6 bg-gray-50 space-y-3 border rounded mb-6 max-h-[60vh]">
        @php use Illuminate\Support\Str; @endphp

        @foreach ($messages as $message)
            <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                <div class="{{ $message->sender_id === auth()->id() ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-800' }} px-3 py-2 rounded-lg max-w-sm shadow space-y-1 text-sm">
                    
                    @if ($message->message)
                        <div>{{ $message->message }}</div>
                    @endif

                    @if ($message->attachment)
                        @php
                            $fileUrl = asset('storage/' . $message->attachment);
                            $isImage = Str::startsWith($message->attachment, 'chat_attachments/') && preg_match('/\.(jpg|jpeg|png|gif)$/i', $message->attachment);
                        @endphp

                        @if ($isImage)
                            <img src="{{ $fileUrl }}" alt="attachment" class="rounded-md max-w-full border mt-1" />
                        @else
                            <a href="{{ $fileUrl }}" target="_blank" class="underline text-xs text-blue-200 hover:text-white">📎 View Attachment</a>
                        @endif
                    @endif

                    <div class="text-[10px] text-right mt-1 {{ $message->sender_id === auth()->id() ? 'text-white/70' : 'text-gray-500' }}">
                        {{ $message->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Chat form -->
    <form method="POST" action="{{ route('user.chat.send') }}" enctype="multipart/form-data" class="flex items-center gap-4">
        @csrf
        <label for="attachmentInput" class="cursor-pointer shrink-0">
            <img src="{{ asset('images/clip.png') }}" alt="Attach" class="w-6 h-6 hover:opacity-80 transition" />
        </label>
        <input type="file" name="attachment" id="attachmentInput" class="hidden" accept="image/*,.pdf,.doc,.docx" />
        <input name="message" type="text" placeholder="Type a message..." class="flex-1 border rounded px-4 py-3 text-sm focus:outline-none focus:ring focus:border-green-400" />
        <button type="submit" class="bg-[#56AB2F] text-white px-6 py-3 rounded-lg text-sm hover:bg-green-700 transition">
            Send
        </button>
    </form>

    <!-- Attachment preview -->
    <div id="attachmentPreview" class="mt-4 ml-12 text-sm text-gray-700 flex items-center gap-3 hidden">
        <img id="previewImage" src="" class="w-16 h-16 object-cover border rounded" />
        <span id="previewFileName" class="truncate max-w-[200px]"></span>
        <button type="button" onclick="clearAttachment()" class="text-red-500 hover:underline text-xs">Remove</button>
    </div>
</div>

<x-footer />

<script>
    const chatBox = document.querySelector('.overflow-y-auto');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    const input = document.getElementById('attachmentInput');
    const preview = document.getElementById('attachmentPreview');
    const previewImg = document.getElementById('previewImage');
    const previewFileName = document.getElementById('previewFileName');

    input.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            preview.classList.remove('hidden');

            const isImage = file.type.startsWith('image/');
            if (isImage) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    previewImg.classList.remove('hidden');
                    previewFileName.textContent = file.name;
                };
                reader.readAsDataURL(file);
            } else {
                previewImg.classList.add('hidden');
                previewImg.src = '';
                previewFileName.textContent = file.name;
            }
        }
    });

    function clearAttachment() {
        input.value = '';
        preview.classList.add('hidden');
        previewImg.src = '';
        previewFileName.textContent = '';
    }
</script>

<script>
    let lastMessageId = null;

function renderMessages(messages) {
    messages.forEach(message => {
        if (lastMessageId === message.id) return; // skip duplicate
        if (!document.getElementById(`msg-${message.id}`)) {
            const isOwn = message.sender_id === {{ auth()->id() }};
            const isImage = message.attachment && /\.(jpg|jpeg|png|gif)$/i.test(message.attachment);
            const fileUrl = `/storage/${message.attachment}`;

            let html = `
                <div class="flex ${isOwn ? 'justify-end' : 'justify-start'}" id="msg-${message.id}">
                    <div class="${isOwn ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-800'} px-3 py-2 rounded-lg max-w-sm shadow text-sm space-y-1">
                        ${message.message ? `<div>${message.message}</div>` : ''}
                        ${message.attachment ? (isImage
                            ? `<img src="${fileUrl}" class="rounded-md max-w-full border mt-1">`
                            : `<a href="${fileUrl}" target="_blank" class="underline text-xs text-blue-200 hover:text-white">📎 View Attachment</a>`) : ''}
                        <div class="text-[10px] text-right mt-1 ${isOwn ? 'text-white/70' : 'text-gray-500'}">
                            ${dayjs(message.created_at).fromNow()}
                        </div>
                    </div>
                </div>
            `;

            chatBox.insertAdjacentHTML('beforeend', html);
            lastMessageId = message.id;
        }
    });

    chatBox.scrollTop = chatBox.scrollHeight;
}


    function fetchMessages() {
        fetch('{{ route("user.chat.messages") }}')
            .then(res => res.json())
            .then(data => renderMessages(data));
    }


    setInterval(fetchMessages, 3000);
    fetchMessages();
</script>

<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/relativeTime.js"></script>
<script>
    dayjs.extend(dayjs_plugin_relativeTime);
</script>
