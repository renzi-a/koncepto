@php use Illuminate\Support\Str; @endphp
<x-layout>
   <div class="container mx-auto px-4 h-screen flex flex-col">

        <h1 class="text-2xl font-bold mb-4">Messages</h1>

        <div class="flex flex-col md:flex-row border rounded-lg overflow-hidden shadow-lg flex-1 min-h-0">

            <div class="w-full md:w-1/4 bg-gray-100 overflow-y-auto border-r">
                <div class="p-4 border-b font-semibold text-gray-700">School Admins</div>
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

            <div class="w-full md:w-3/4 flex flex-col min-h-0">

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

                <div id="adminChatMessages" class="flex-1 overflow-y-auto p-4 bg-gray-50 space-y-4">
                    @foreach ($messages as $message)
                        <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                            <div class="{{ $message->sender_id === auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800' }} px-4 py-2 rounded-lg max-w-xs md:max-w-sm shadow space-y-2">
                                @if ($message->message)
                                    <div>{{ $message->message }}</div>
                                @endif

                                @if ($message->attachment)
                                    @php
                                        $fileUrl = asset('storage/' . $message->attachment);
                                        $isImage = Str::startsWith($message->attachment, 'chat_attachments/') &&
                                                   preg_match('/\.(jpg|jpeg|png|gif)$/i', $message->attachment);
                                    @endphp

                                    @if ($isImage)
                                        <img src="{{ $fileUrl }}" alt="attachment" class="rounded-md max-w-full border" />
                                    @else
                                            <a href="{{ $fileUrl }}" target="_blank" class="underline text-sm text-blue-600 hover:text-blue-800">
                                            {{ basename($message->original_name ?? $message->attachment) }}
                                        </a>
                                    @endif
                                @endif

                                <div class="text-xs text-right mt-1 {{ $message->sender_id === auth()->id() ? 'text-white/70' : 'text-gray-500' }}">
                                    {{ $message->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div id="typingIndicator" class="hidden flex justify-start">
                        <div class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg max-w-xs md:max-w-sm shadow">
                            <div class="flex gap-1">
                                <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce [animation-delay:0.1s]"></span>
                                <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce [animation-delay:0.3s]"></span>
                                <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce [animation-delay:0.5s]"></span>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($activeUser)
                    <form action="{{ route('admin.chat.send', $activeUser->id) }}" method="POST" enctype="multipart/form-data" class="p-4 border-t bg-white" id="chatForm">
                        @csrf
                            <div id="attachmentPreview" class="mb-2 ml-10 text-sm text-gray-700 flex items-center gap-2 hidden">
                                <img id="previewImage" src="" class="w-16 h-16 object-cover border rounded" />
                                <span id="previewFileName" class="truncate max-w-[150px]"></span>
                                <button type="button" onclick="clearAttachment()" class="text-red-500 hover:underline text-xs">Remove</button>
                            </div>

                            <div class="flex items-center gap-3">
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
                @endif
            </div>
        </div>
    </div>

    <script>
        const inputField = document.getElementById('messageInput');
        const typingIndicator = document.getElementById('typingIndicator');
        let typingTimer;

        inputField.addEventListener('input', () => {
            typingIndicator.classList.remove('hidden');
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                typingIndicator.classList.add('hidden');
            }, 2000);
        });
    </script>
</x-layout>
