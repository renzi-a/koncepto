<x-layout>
    
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Users</h1>
        <span class="text-gray-600 text-lg font-medium">
            Total Users: {{ $users->total() }}
        </span>
    </div>

    <form method="GET" action="{{ route('admin.users.index') }}" id="filterForm" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
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

        <div>
            <label for="school_id" class="block mb-1 font-semibold">Filter by School</label>
            <select name="school_id" id="school_id" class="border border-gray-300 rounded px-3 py-2 w-full">
                <option value="">All Schools</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                        {{ $school->school_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>

    <div id="userTable">
        @include('components.user_table', ['users' => $users])
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
</x-layout>
