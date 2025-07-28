<x-profile-link>
    <div class="container mx-auto px-4 py-6 space-y-8" 
        x-data="{ showConfirm: false, showSaved: {{ session('success') ? 'true' : 'false' }}, showSaving: false }"
    >
        <div class="flex items-center space-x-8 mb-8">
            @if(auth()->user()->school && auth()->user()->school->image)
                <img src="{{ asset('storage/' . auth()->user()->school->image) }}" alt="School Logo"
                    class="w-32 h-32 object-cover rounded-full border-4 border-green-600 shadow-md">
            @else
                <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 border-4 border-gray-300 shadow-md">
                    No Logo
                </div>
            @endif

            <div>
                <h1 class="text-4xl font-bold text-gray-800">
                    {{ auth()->user()->school->school_name ?? 'No School Assigned' }}
                </h1>
                <p class="text-lg text-gray-600 mt-2">{{ auth()->user()->school->address ?? 'N/A' }}</p>
                <p class="text-md text-gray-500">{{ auth()->user()->school->school_email ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-8 w-full">

            <form 
                method="POST" 
                action="{{ route('user.profile.update') }}" 
                class="space-y-6"
                x-ref="profileForm"
                @submit.prevent="showConfirm = true"
            >
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name', auth()->user()->first_name) }}"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-200 focus:border-green-500 focus:outline-none" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name', auth()->user()->last_name) }}"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-200 focus:border-green-500 focus:outline-none" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-200 focus:border-green-500 focus:outline-none" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', auth()->user()->cp_no) }}"
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-200 focus:border-green-500 focus:outline-none">
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Change Password</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Current Password</label>
                            <input type="password" name="current_password"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-200 focus:border-green-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">New Password</label>
                            <input type="password" name="new_password"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-200 focus:border-green-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation"
                                class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-200 focus:border-green-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <div class="pt-6 text-right">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-2 bg-[#56AB2F] text-white font-semibold rounded-md hover:bg-green-700 transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <div 
            x-show="showConfirm" 
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" 
            x-transition
        >
            <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-xl">
                <h2 class="text-xl font-semibold text-gray-800 mb-2">Confirm Changes</h2>
                <p class="text-gray-600 mb-4">Are you sure you want to save these changes?</p>
                <div class="flex justify-end space-x-4">
                    <button 
                        class="px-4 py-2 bg-gray-200 rounded-md text-gray-700 hover:bg-gray-300"
                        @click="showConfirm = false"
                    >
                        Cancel
                    </button>
                    <button 
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                        @click="showConfirm = false; showSaving = true; $refs.profileForm.submit()"
                    >
                        Confirm
                    </button>
                </div>
            </div>
        </div>

        <div 
            x-show="showSaving" 
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            class="fixed inset-0 z-50 bg-black bg-opacity-40 flex items-center justify-center"
        >
            <div class="bg-white rounded-xl p-8 shadow-lg flex items-center space-x-5 animate-fadeIn">
                <svg class="animate-spin text-[#56AB2F]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width: 48px; height: 48px;">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                <span class="text-[#56AB2F] font-semibold text-xl">Saving changes...</span>
            </div>
        </div>

        <div 
            x-show="showSaved" 
            x-transition 
            x-init="setTimeout(() => showSaved = false, 2000)" 
            class="fixed top-6 right-6 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50"
        >
            <span class="font-semibold">Changes saved successfully!</span>
        </div>
    </div>
</x-profile-link>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out forwards;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .animate-spin {
        animation: spin 1s linear infinite;
    }
</style>