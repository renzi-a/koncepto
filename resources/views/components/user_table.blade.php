<div class="overflow-x-auto">
    <table class="min-w-full bg-white border border-gray-200 rounded-md shadow-sm">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="px-4 py-2 border-b">Name</th>
                <th class="px-4 py-2 border-b">Email</th>
                <th class="px-4 py-2 border-b">Role</th>
                <th class="px-4 py-2 border-b">School</th>
                <th class="px-4 py-2 border-b">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $user->first_name }} {{ $user->last_name }}</td>
                    <td class="px-4 py-2">{{ $user->email }}</td>
                    <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $user->role) }}</td>
                    <td class="px-4 py-2">{{ $user->school->school_name ?? '—' }}</td>
                    <td class="px-4 py-2">
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('admin.users.edit', $user->id) }}">
                                <img src="{{ asset('images/icons/edit.png') }}" class="w-5 h-5" />
                            </a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Delete this user?');">
                                @csrf @method('DELETE')
                                <button type="submit">
                                    <img src="{{ asset('images/icons/delete.png') }}" class="w-5 h-5" />
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-gray-500 py-6">No users found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-6">
        {{ $users->withQueryString()->links() }}
    </div>
</div>
