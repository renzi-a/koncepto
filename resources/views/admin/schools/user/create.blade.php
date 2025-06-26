<x-layout />
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Add New User</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <strong>There were some problems with your input:</strong>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store', $school->id) }}" method="POST" class="bg-white p-6 rounded shadow-md space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="first_name" class="block font-semibold mb-1">First Name</label>
                <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}"
                       class="w-full border border-gray-300 px-3 py-2 rounded" required>
            </div>

            <div>
                <label for="last_name" class="block font-semibold mb-1">Last Name</label>
                <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}"
                       class="w-full border border-gray-300 px-3 py-2 rounded" required>
            </div>

            <div>
                <label for="email" class="block font-semibold mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}"
                       class="w-full border border-gray-300 px-3 py-2 rounded" required>
            </div>

            <div>
                <label for="cp_no" class="block font-semibold mb-1">Contact Number</label>
                <input type="text" name="cp_no" id="cp_no" value="{{ old('cp_no') }}"
                       class="w-full border border-gray-300 px-3 py-2 rounded" required>
            </div>

            <div>
                <label for="role" class="block font-semibold mb-1">Role</label>
                <select name="role" id="role" class="w-full border border-gray-300 px-3 py-2 rounded" required>
                    <option value="teacher" {{ old('role') == 'school_admin' ? 'selected' : '' }}>School Admin</option>
                    <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                    <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label for="password" class="block font-semibold mb-1">Password</label>
                <input type="password" name="password" id="password"
                       class="w-full border border-gray-300 px-3 py-2 rounded" required>
            </div>

            <div>
                <label for="password_confirmation" class="block font-semibold mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="w-full border border-gray-300 px-3 py-2 rounded" required>
            </div>
        </div>

        <div class="text-right">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                Save User
            </button>
        </div>
    </form>
</div>
