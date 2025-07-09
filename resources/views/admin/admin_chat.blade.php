<x-layout />
<div class="container mx-auto px-4 pt-6 pb-4 h-screen flex flex-col">

    <h1 class="text-2xl font-bold mb-4">Messages</h1>

    <div class="flex flex-col md:flex-row border rounded-lg overflow-hidden shadow-lg flex-1 min-h-0">

        <!-- Sidebar -->
        <div class="w-full md:w-1/4 bg-gray-100 overflow-y-auto border-r">
            <div class="p-4 border-b font-semibold text-gray-700">
                School Admins
            </div>

            @foreach ($users as $user)
                <a href="{{ route('admin.chat.show', $user->id) }}"
                   class="flex items-center gap-3 px-4 py-4 hover:bg-gray-200 transition duration-200 {{ $activeUser && $activeUser->id === $user->id ? 'bg-gray-300' : '' }}">
                    @if ($user->school && $user->school->image)
                        <img src="{{ asset('storage/' . $user->school->image) }}"
                             alt="Logo"
                             class="w-14 h-14 rounded-full object-cover border">
                    @else
                        <div class="w-14 h-14 bg-blue-600 text-white flex items-center justify-center rounded-full text-xl font-semibold uppercase">
                            {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                        </div>
                    @endif
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800 text-base truncate">{{ $user->school->school_name ?? 'N/A School' }}</div>
                        <div class="text-gray-600 text-sm truncate">{{ $user->first_name }} {{ $user->last_name }}</div>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Chat Area -->
        <div class="w-full md:w-3/4 flex flex-col">
            <div class="p-4 border-b bg-white flex justify-between items-center">
                <h2 class="font-semibold text-gray-800 text-lg">
                    @if ($activeUser)
                        {{ $activeUser->school->school_name ?? 'Unknown School' }}
                        <span class="block text-sm font-normal text-gray-500">
                            {{ $activeUser->first_name }} {{ $activeUser->last_name }}
                        </span>
                    @else
                        <span class="text-sm font-normal text-gray-500">Select a school admin to start chatting</span>
                    @endif
                </h2>
            </div>

    <!-- Chat messages -->
    <div id="adminChatMessages" class="flex-1 overflow-y-auto p-4 bg-gray-50 space-y-4">
        @php use Illuminate\Support\Str; @endphp

        @foreach ($messages as $message)
            <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                <div class="{{ $message->sender_id === auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800' }} px-4 py-2 rounded-lg max-w-xs md:max-w-sm shadow space-y-2">
                    
                    @if ($message->message)
                        <div>{{ $message->message }}</div>
                    @endif

                    @if ($message->attachment)
                        @php
                            $fileUrl = asset('storage/' . $message->attachment);
                            $isImage = Str::startsWith($message->attachment, 'chat_attachments/') && preg_match('/\.(jpg|jpeg|png|gif)$/i', $message->attachment);
                        @endphp

                        @if ($isImage)
                            <img src="{{ $fileUrl }}" alt="attachment" class="rounded-md max-w-full border" />
                        @else
                            <a href="{{ $fileUrl }}" target="_blank" class="underline text-sm text-blue-200 hover:text-white">📎 View Attachment</a>
                        @endif
                    @endif

                    <div class="text-xs text-right mt-1 {{ $message->sender_id === auth()->id() ? 'text-white/70' : 'text-gray-500' }}">
                        {{ $message->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
            @if ($activeUser)
                <form action="{{ route('admin.chat.send', $activeUser->id) }}" method="POST" enctype="multipart/form-data" class="p-4 border-t bg-white" id="chatForm">
                    @csrf
                    <div class="flex items-center gap-3">
                        <label for="attachmentInput" class="cursor-pointer shrink-0">
                            <img src="{{ asset('images/clip.png') }}" alt="Attach" class="w-6 h-6 hover:opacity-80 transition" />
                        </label>
                        <input type="file" name="attachment" id="attachmentInput" class="hidden" accept="image/*,.pdf,.doc,.docx" />
                        <input type="text" name="message" placeholder="Type a message..."
                            class="flex-1 border px-4 py-2 rounded-lg text-sm focus:outline-none focus:ring focus:border-blue-400" />
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm">
                            Send
                        </button>
                    </div>
                    <div id="attachmentPreview" class="mt-2 ml-10 text-sm text-gray-700 flex items-center gap-2 hidden">
                        <img id="previewImage" src="" class="w-16 h-16 object-cover border rounded" />
                        <span id="previewFileName" class="truncate max-w-[150px]"></span>
                        <button type="button" onclick="clearAttachment()" class="text-red-500 hover:underline text-xs">Remove</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

<script>
    const chatBox = document.querySelector('.overflow-y-auto');
    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }
</script>

<script>
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

<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/relativeTime.js"></script>
<script>dayjs.extend(dayjs_plugin_relativeTime);</script>

@if ($activeUser)
<script>
    const currentUserId = {{ auth()->id() }};
    const chatBox = document.getElementById('adminChatMessages');
    const form = document.getElementById('chatForm');
    const input = document.getElementById('attachmentInput');
    const preview = document.getElementById('attachmentPreview');
    const previewImg = document.getElementById('previewImage');
    const previewFileName = document.getElementById('previewFileName');

    function scrollToBottom() {
        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
    }

    function renderMessages(messages) {
        chatBox.innerHTML = '';

        messages.forEach(message => {
            const isOwn = message.sender_id === currentUserId;
            const isImage = message.attachment && /\.(jpg|jpeg|png|gif)$/i.test(message.attachment);
            const fileUrl = `/storage/${message.attachment}`;

            let html = `
                <div class="flex ${isOwn ? 'justify-end' : 'justify-start'}">
                    <div class="${isOwn ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800'} px-4 py-2 rounded-lg max-w-xs md:max-w-sm shadow space-y-2 text-sm">
                        ${message.message ? `<div>${message.message}</div>` : ''}
            `;

            if (message.attachment) {
                if (isImage) {
                    html += `<img src="${fileUrl}" class="rounded-md max-w-full border mt-1" />`;
                } else {
                    html += `<a href="${fileUrl}" target="_blank" class="underline text-sm text-blue-200 hover:text-white">📎 View Attachment</a>`;
                }
            }

            html += `<div class="text-xs text-right mt-1 ${isOwn ? 'text-white/70' : 'text-gray-500'}">
                        ${dayjs(message.created_at).fromNow()}
                    </div>
                </div></div>`;

            chatBox.insertAdjacentHTML('beforeend', html);
        });

        scrollToBottom();
    }

    function fetchMessages() {
        fetch("{{ route('admin.chat.messages', $activeUser->id) }}")
            .then(res => res.json())
            .then(data => renderMessages(data));
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error('Failed to send message');
            form.reset();
            clearAttachment();
            fetchMessages();
        })
        .catch(err => {
            alert('Failed to send message.');
            console.error(err);
        });
    });

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


    fetchMessages();
    setInterval(fetchMessages, 3000);
</script>
@endif
