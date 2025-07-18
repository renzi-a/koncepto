<x-profile-link>
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">{{ $school->school_name }}</h1>
            <div class="bg-white rounded-lg p-3">
                <span class="text-gray-600 text-lg font-medium">
                    Total Users: {{ $users->total() }}
                </span>
            </div>
        </div>

        <div class="bg-white rounded-lg p-6">
            <form method="GET" action="{{ route('user.users') }}" id="filterForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="search" class="block mb-1 font-semibold">Search by Name or Email</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}"
                        placeholder="Enter name or email"
                        class="border border-gray-300 rounded px-3 py-2 w-full">
                </div>

                <div>
                    <label for="role" class="block mb-1 font-semibold">Filter by Role</label>
                    <select name="role" id="role" class="border border-gray-300 rounded px-3 py-2 w-full">
                        <option value="">All Roles</option>
                        @foreach(['school_admin', 'teacher', 'student'] as $roleOption)
                            <option value="{{ $roleOption }}" {{ request('role') == $roleOption ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $roleOption)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div id="userTable" class="mt-10">
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-md shadow-sm">
                        <thead>
                            <tr class="bg-gray-100 text-left">
                                <th class="px-4 py-2 border-b">Name</th>
                                <th class="px-4 py-2 border-b">Email</th>
                                <th class="px-4 py-2 border-b">Role</th>
                                <th class="px-4 py-2 border-b">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2">{{ $user->first_name }} {{ $user->last_name }}</td>
                                    <td class="px-4 py-2">{{ $user->email }}</td>
                                    <td class="px-4 py-2 capitalize">{{ str_replace('_', ' ', $user->role) }}</td>
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
                                    <td colspan="4" class="text-center text-gray-500 py-6">No users found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-6">
                        {{ $users->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('filterForm');
        const inputs = form.querySelectorAll('input, select');

        inputs.forEach(input => {
            input.addEventListener('input', fetchFilteredUsers);
            input.addEventListener('change', fetchFilteredUsers);
        });

        function fetchFilteredUsers() {
            const url = form.action + '?' + new URLSearchParams(new FormData(form)).toString();

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('userTable').innerHTML = data.html;
            })
            .catch(error => console.error('Fetch error:', error));
        }
    </script>
</x-profile-link>