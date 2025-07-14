<x-nav-link />

<div class="max-w-7xl mx-auto mt-10 mb-10 bg-white p-8 shadow-xl rounded-2xl flex flex-col min-h-[80vh]">
<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold">Chat</h2>
    <span id="chatSource" class="text-sm text-gray-500 italic">Checking who you're chatting with...</span>
    <a href="{{ url()->previous() }}" class="text-blue-600 hover:underline text-sm flex items-center gap-1">
        Back
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
    </a>
</div>



    <div class="flex-1 overflow-y-auto p-0 bg-gray-50 space-y-0 border rounded mb-2 max-h-[60vh] flex flex-col">
    <div id="chatMessages" class="flex-1 overflow-y-auto p-6 space-y-3">
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
                            <a href="{{ $fileUrl }}" target="_blank" class="underline text-sm text-blue-600 hover:text-blue-800">
                                {{ basename($message->original_name ?? $message->attachment) }}
                            </a>
                        @endif
                    @endif

                    <div class="text-[10px] text-right mt-1 {{ $message->sender_id === auth()->id() ? 'text-white/70' : 'text-gray-500' }}">
                        {{ $message->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div id="typingIndicator" class="px-4 py-2 text-sm text-gray-500 animate-pulse hidden">
        Admin is typing...
    </div>
</div>


<form id="chatForm" enctype="multipart/form-data" class="flex flex-col gap-2 w-full">
    @csrf

    <div id="attachmentPreview" class="ml-10 text-sm text-gray-700 flex items-center gap-2 hidden">
        <img id="previewImage" src="" class="w-16 h-16 object-cover border rounded hidden" />
        <span id="previewFileName" class="truncate max-w-[150px]"></span>
        <button type="button" onclick="clearAttachment()" class="text-red-500 hover:underline text-xs">Remove</button>
    </div>

    <div class="flex items-center gap-3 w-full">
        <label for="attachmentInput" class="cursor-pointer shrink-0">
            <img src="{{ asset('images/clip.png') }}" alt="Attach" class="w-6 h-6 hover:opacity-80 transition" />
        </label>
        <input type="file" name="attachment" id="attachmentInput" class="hidden" accept="image/*,.pdf,.doc,.docx" />
        <input type="text" name="message" id="messageInput" placeholder="Type a message..."
            class="flex-1 border px-4 py-2 rounded-lg text-sm focus:outline-none focus:ring focus:border-blue-400" />
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
            Send
        </button>
    </div>
</form>

</div>
<style>
    .animate-bounce {
        animation: bounce 1s infinite ease-in-out;
    }
    .dot {
        display: inline-block;
    }
    @keyframes bounce {
        0%, 80%, 100% {
            transform: scale(0);
        }
        40% {
            transform: scale(1);
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/relativeTime.js"></script>
<script>
    dayjs.extend(dayjs_plugin_relativeTime);
</script>

<script>
const chatBox = document.getElementById('chatMessages');
const currentUserId = {{ auth()->id() }};
let lastMessageId = null;

function scrollToBottom() {
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
}

function isAtBottom() {
    const threshold = 80; 
    return chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < threshold;
}

function scrollToBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}

function renderMessages(messages) {
    let maxId = lastMessageId ?? 0;
    const shouldScroll = isAtBottom();

    messages.forEach(message => {
        if (lastMessageId && message.id <= lastMessageId) return;

        const isOwn = message.sender_id === currentUserId;
        const isImage = message.attachment && /\.(jpg|jpeg|png|gif)$/i.test(message.attachment);
        const fileUrl = `/storage/${message.attachment}`;

        const html = `
            <div class="flex ${isOwn ? 'justify-end' : 'justify-start'}" id="msg-${message.id}">
                <div class="${isOwn ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-800'} px-3 py-2 rounded-lg max-w-sm shadow text-sm space-y-1">
                    ${message.message ? `<div>${message.message}</div>` : ''}
                    ${message.attachment ? (
                        isImage
                            ? `<img src="${fileUrl}" class="rounded-md border mt-1 max-w-full max-h-48 object-contain" />`
                            : `<a href="${fileUrl}" target="_blank" class="underline text-sm text-gray-800 hover:text-black">
                                    ${message.original_name || message.attachment.split('/').pop()}
                               </a>`
                    ) : ''}
                    <div class="text-[10px] text-right mt-1 ${isOwn ? 'text-white/70' : 'text-gray-500'}">
                        ${dayjs(message.created_at).fromNow()}
                    </div>
                </div>
            </div>
        `;

        chatBox.insertAdjacentHTML('beforeend', html);
        maxId = Math.max(maxId, message.id);
    });

    lastMessageId = maxId;

    if (shouldScroll) {
        scrollToBottom();
    }
}


function fetchMessages() {
    fetch('{{ route("user.chat.messages") }}')
        .then(res => res.json())
        .then(data => renderMessages(data));
}

fetchMessages();
setInterval(fetchMessages, 3000);
scrollToBottom();
</script>

<script>
function checkTyping() {
    const typingIndicator = document.getElementById('typingIndicator');

    fetch("{{ route('user.chat.checkTyping') }}")
        .then(res => res.json())
        .then(data => {
            if (data.typing) {
                typingIndicator.classList.remove('hidden')
                scrollToBottom();
            } else {
                typingIndicator.classList.add('hidden');
            }
        });
}
setInterval(checkTyping, 1500);
</script>
<script>
const messageInput = document.getElementById('messageInput');

messageInput.addEventListener('input', () => {
    fetch("{{ route('user.chat.typing') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({})
    });
});
</script>

<script>
const chatForm = document.getElementById('chatForm');

chatForm.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(chatForm);
    const url = "{{ route('user.chat.send') }}";

    fetch(url, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: formData
    })
    .then(res => {
        if (!res.ok) {
            throw new Error("Message failed");
        }

        messageInput.value = '';
        clearAttachment();
        fetchMessages();
    });
});
</script>

<script>
const attachmentInput = document.getElementById('attachmentInput');
const preview = document.getElementById('attachmentPreview');
const previewImg = document.getElementById('previewImage');
const previewFileName = document.getElementById('previewFileName');

attachmentInput.addEventListener('change', function () {
    if (this.files && this.files[0]) {
        const file = this.files[0];
        preview.classList.remove('hidden');
        previewFileName.textContent = file.name;

        const isImage = file.type.startsWith('image/');
        if (isImage) {
            const reader = new FileReader();
            reader.onload = function (e) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            previewImg.src = '';
            previewImg.classList.add('hidden');
        }
    }
});

function clearAttachment() {
    attachmentInput.value = '';
    preview.classList.add('hidden');
    previewImg.src = '';
    previewFileName.textContent = '';
}
</script>
<script>
function checkChatSource() {
    fetch("{{ route('user.chat.source') }}")
        .then(res => res.json())
        .then(data => {
            const status = document.getElementById('chatSource');
            status.textContent = data.source === 'admin'
                ? "You're chatting with Admin"
                : "You're chatting with Koncepto Bot";
        });
}

setInterval(checkChatSource, 5000);
checkChatSource();
</script>

<x-footer />
