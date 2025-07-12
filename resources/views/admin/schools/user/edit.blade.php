<x-layout>
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit User</h1>
        <a href="{{ url()->previous() }}"
           class="inline-flex items-center text-sm text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-full transition shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back
        </a>
    </div>

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

    <form id="userForm" action="{{ route('admin.users.update', $user->id) }}" method="POST" class="bg-white p-6 rounded shadow-md space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="first_name" class="block font-semibold mb-1">First Name</label>
                <input type="text" name="first_name" id="first_name"
                       value="{{ old('first_name', $user->first_name) }}"
                       class="w-full border border-gray-300 px-3 py-2 rounded">
            </div>

            <div>
                <label for="last_name" class="block font-semibold mb-1">Last Name</label>
                <input type="text" name="last_name" id="last_name"
                       value="{{ old('last_name', $user->last_name) }}" required
                       class="w-full border border-gray-300 px-3 py-2 rounded">
            </div>

            <div>
                <label for="email" class="block font-semibold mb-1">Email</label>
                <input type="email" name="email" id="email"
                       value="{{ old('email', $user->email) }}" required
                       class="w-full border border-gray-300 px-3 py-2 rounded">
            </div>

            <div>
                <label for="cp_no" class="block font-semibold mb-1">Contact Number</label>
                <input type="text" name="cp_no" id="cp_no"
                       value="{{ old('cp_no', $user->cp_no) }}" required
                       class="w-full border border-gray-300 px-3 py-2 rounded">
            </div>

            <div>
                <label for="role" class="block font-semibold mb-1">Role</label>
                <select name="role" id="role" required class="w-full border border-gray-300 px-3 py-2 rounded">
                    <option value="school_admin" {{ $user->role == 'school_admin' ? 'selected' : '' }}>School Admin</option>
                    <option value="teacher" {{ $user->role == 'teacher' ? 'selected' : '' }}>Teacher</option>
                    <option value="student" {{ $user->role == 'student' ? 'selected' : '' }}>Student</option>
                </select>
            </div>

            <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block font-semibold mb-1">New Password (optional)</label>
                    <input type="password" name="password" id="password"
                           class="w-full border border-gray-300 px-3 py-2 rounded">
                </div>

                <div>
                    <label for="password_confirmation" class="block font-semibold mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="w-full border border-gray-300 px-3 py-2 rounded">
                </div>
            </div>
        </div>

        <div class="text-right">
            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                Update User
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const form = document.getElementById('userForm');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: "Are you sure?",
                text: "Update this user information?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#16a34a",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, update it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
</x-layout>