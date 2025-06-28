<x-layout />
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-4">Messages</h1>

    <div class="flex border rounded-lg overflow-hidden shadow-lg h-[600px]">
        <!-- Sidebar -->
        <div class="w-1/3 bg-gray-100 overflow-y-auto border-r">
            <div class="p-4 border-b font-semibold text-gray-700">
                School Admins
            </div>

            @foreach ($users as $user)
                <a href="{{ route('admin.chat.show', $user->id) }}"
                   class="flex items-start gap-3 px-4 py-3 hover:bg-gray-200 transition {{ $activeUser && $activeUser->id === $user->id ? 'bg-gray-300' : '' }}">
                    @if ($user->school && $user->school->image)
                        <img src="{{ asset('storage/' . $user->school->image) }}"
                            alt="Logo"
                            class="w-12 h-12 rounded-full object-cover border">
                    @else
                        <div class="w-12 h-12 bg-blue-600 text-white flex items-center justify-center rounded-full text-lg font-semibold uppercase">
                            {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                        </div>
                    @endif
                    <div class="text-sm">
                        <div class="font-bold text-gray-800">{{ $user->school->school_name ?? 'N/A School' }}</div>
                        <div class="text-gray-600 text-xs">{{ $user->first_name }} {{ $user->last_name }}</div>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Chat Area -->
        <div class="w-2/3 flex flex-col">
            <div class="p-4 border-b bg-white flex justify-between items-center">
                <h2 class="font-semibold text-gray-800 text-lg">
                    @if ($activeUser)
                        {{ $activeUser->school->school_name ?? 'Unknown School' }}
                        <span class="block text-sm font-normal text-gray-500">
                            {{ $activeUser->first_name }} {{ $activeUser->last_name }}
                        </span>
                    @else
                        Select a school admin to start chatting
                    @endif
                </h2>
            </div>

            <!-- Chat messages -->
            <div class="flex-1 overflow-y-auto p-4 bg-gray-50 space-y-4">
                @foreach ($messages as $message)
                    <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                        <div class="{{ $message->sender_id === auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800' }} px-4 py-2 rounded-lg max-w-xs shadow">
                            <div>{{ $message->body }}</div>
                            <div class="text-xs text-right mt-1 {{ $message->sender_id === auth()->id() ? 'text-white/70' : 'text-gray-500' }}">
                                {{ $message->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Chat input -->
            @if ($activeUser)
            <form action="{{ route('admin.chat.send', $activeUser->id) }}" method="POST" class="p-4 border-t flex gap-2 bg-white">
                @csrf
                <input type="text" name="message" placeholder="Type a message..."
                       class="flex-1 border px-4 py-2 rounded-lg focus:outline-none focus:ring"
                       required>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    Send
                </button>
            </form>
            @endif
        </div>
    </div>
</div>
