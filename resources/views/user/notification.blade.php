<x-nav-link />

<div class="bg-gray-100 py-10 min-h-screen">
    <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow-md">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">🔔 Your Notifications</h2>
            <div class="flex gap-4">
                <a href="{{ route('user.home') }}" class="text-[#56AB2F] hover:underline text-sm">← Back</a>
                @if(!$notifications->isEmpty())
                <form method="POST" action="{{ route('notifications.clear') }}">
                    @csrf
                    <button type="submit" class="text-sm bg-[#56AB2F] text-white px-3 py-1 rounded hover:bg-green-700 transition">
                        Clear All
                    </button>
                </form>
                @endif
            </div>
        </div>
        @if(session('status'))
            <div class="bg-green-100 text-green-700 px-4 py-2 rounded text-sm mb-4">
                {{ session('status') }}
            </div>
        @endif
        @if($notifications->isEmpty())
            <div class="text-gray-500 text-center py-10 text-sm">
                You have no notifications.
            </div>
        @else
            <div class="space-y-4">
                @foreach($notifications as $notif)
                    <div class="bg-gray-50 p-4 rounded-lg shadow-sm flex justify-between items-start hover:bg-gray-100 transition">
                        <div class="text-sm max-w-4xl">
                            <h3 class="font-semibold text-gray-800">{{ $notif->title ?? 'Notification' }}</h3>
                            <p class="text-gray-600">{{ $notif->message }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                        </div>
                        @if(!$notif->is_read)
                            <span class="bg-[#56AB2F] text-white text-[10px] px-2 py-1 rounded-full h-fit">Unread</span>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="pt-6">
                {{ $notifications->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
</div>

<x-footer />
