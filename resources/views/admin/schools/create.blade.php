<x-layout />
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Add New School & Administrator</h1>

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

    <form action="{{ route('admin.schools.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow-md space-y-6">
        @csrf

        <div>
            <h2 class="text-xl font-semibold mb-4">School Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="school_name" class="block font-semibold mb-1">School Name</label>
                    <input type="text" name="school_name" id="school_name" value="{{ old('school_name') }}"
                           placeholder="e.g. Lian Senior High School" required
                           class="w-full border border-gray-300 px-3 py-2 rounded">
                </div>

                <div>
                    <label for="address" class="block font-semibold mb-1">School Address</label>
                    <input type="text" name="address" id="address" value="{{ old('address') }}"
                           placeholder="e.g. Barangay X, Lian, Batangas" required
                           class="w-full border border-gray-300 px-3 py-2 rounded">
                </div>

                <div>
                    <label for="school_email" class="block font-semibold mb-1">School Email</label>
                    <input type="email" name="school_email" id="school_email" value="{{ old('school_email') }}"
                           placeholder="e.g. school@example.com" required
                           class="w-full border border-gray-300 px-3 py-2 rounded">
                </div>

                <div>
                    <label for="logo" class="block font-semibold mb-1">School Logo</label>
                    <input type="file" name="logo" id="logo"
                           class="w-full border border-gray-300 px-3 py-2 rounded">
                </div>
            </div>
        </div>


        <div>
            <h2 class="text-xl font-semibold mb-4">Administrative Officer</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="admin_first_name" class="block font-semibold mb-1">First Name</label>
                    <input type="text" name="admin_first_name" id="admin_first_name" value="{{ old('admin_first_name') }}"
                           placeholder="e.g. Juan" required
                           class="w-full border border-gray-300 px-3 py-2 rounded">
                </div>

                <div>
                    <label for="admin_last_name" class="block font-semibold mb-1">Last Name</label>
                    <input type="text" name="admin_last_name" id="admin_last_name" value="{{ old('admin_last_name') }}"
                           placeholder="e.g. Dela Cruz" required
                           class="w-full border border-gray-300 px-3 py-2 rounded">
                </div>

                <div>
                    <label for="admin_email" class="block font-semibold mb-1">Email</label>
                    <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email') }}"
                           placeholder="e.g. admin@example.com" required
                           class="w-full border border-gray-300 px-3 py-2 rounded">
                </div>

                <div>
                    <label for="admin_contact" class="block font-semibold mb-1">Contact Number</label>
                    <input type="text" name="admin_contact" id="admin_contact" value="{{ old('admin_contact') }}"
                           placeholder="e.g. 09123456789" required
                           class="w-full border border-gray-300 px-3 py-2 rounded">
                </div>

                <div>
                    <label for="admin_role" class="block font-semibold mb-1">Role</label>
                    <select name="admin_role" id="admin_role" required class="w-full border border-gray-300 px-3 py-2 rounded">
                        <option value="school_admin" {{ old('admin_role') == 'school_admin' ? 'selected' : '' }}>School Admin</option>
                    </select>
                </div>

                <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="admin_password" class="block font-semibold mb-1">Password</label>
                        <input type="password" name="admin_password" id="admin_password"
                               placeholder="Enter a secure password" required
                               class="w-full border border-gray-300 px-3 py-2 rounded">
                    </div>

                    <div>
                        <label for="admin_password_confirmation" class="block font-semibold mb-1">Confirm Password</label>
                        <input type="password" name="admin_password_confirmation" id="admin_password_confirmation"
                               placeholder="Re-type password" required
                               class="w-full border border-gray-300 px-3 py-2 rounded">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-right">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                Save School & Admin
            </button>
        </div>
    </form>
</div>
