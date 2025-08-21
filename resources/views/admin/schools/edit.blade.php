<x-layout>
    @push('styles')
    <style>
        .loader {
            border-top-color: #3b82f6; /* A shade of blue-600 */
            -webkit-animation: spin 1s linear infinite;
            animation: spin 1s linear infinite;
        }

        @-webkit-keyframes spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    @endpush

    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Edit School & Administrator</h1>
            <a href="{{ route('admin.schools.index') }}"
               class="inline-flex items-center text-sm text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-full transition shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Schools
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

        <form id="schoolForm" action="{{ route('admin.schools.update', $school->id) }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow-md space-y-6">
            @csrf
            @method('PUT')

            <div>
                <h2 class="text-xl font-semibold mb-4">School Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="school_name" class="block font-semibold mb-1">School Name</label>
                        <input type="text" name="school_name" id="school_name" value="{{ old('school_name', $school->school_name) }}" required class="w-full border border-gray-300 px-3 py-2 rounded">
                    </div>

                    <div>
                        <label for="address" class="block font-semibold mb-1">School Address</label>
                        <input type="text" name="address" id="address" value="{{ old('address', $school->address) }}" required class="w-full border border-gray-300 px-3 py-2 rounded">
                    </div>

                    <div>
                        <label for="school_email" class="block font-semibold mb-1">School Email</label>
                        <input type="email" name="school_email" id="school_email" value="{{ old('school_email', $school->school_email) }}" required class="w-full border border-gray-300 px-3 py-2 rounded">
                    </div>
                    <div>
                        <label for="principal" class="block font-semibold mb-1">Principal</label>
                        <input type="text" name="principal" id="principal" value="{{ old('principal', $school->principal) }}" class="w-full border border-gray-300 px-3 py-2 rounded">
                    </div>
                    
                    <div>
                        <label for="logo" class="block font-semibold mb-1">School Logo</label>
                        <input type="file" name="logo" id="logo" class="w-full border border-gray-300 px-3 py-2 rounded">
                        @if($school->image)
                            <img src="{{ asset('storage/' . $school->image) }}" alt="Logo" class="w-16 h-16 mt-2">
                        @endif
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-xl font-semibold mb-4">Administrative Officer</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="admin_first_name" class="block font-semibold mb-1">First Name</label>
                        <input type="text" name="admin_first_name" id="admin_first_name" value="{{ old('admin_first_name', $admin->first_name ?? '') }}" required class="w-full border border-gray-300 px-3 py-2 rounded">
                    </div>

                    <div>
                        <label for="admin_last_name" class="block font-semibold mb-1">Last Name</label>
                        <input type="text" name="admin_last_name" id="admin_last_name" value="{{ old('admin_last_name', $admin->last_name ?? '') }}" required class="w-full border border-gray-300 px-3 py-2 rounded">
                    </div>

                    <div>
                        <label for="admin_email" class="block font-semibold mb-1">Email</label>
                        <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email', $admin->email ?? '') }}" required class="w-full border border-gray-300 px-3 py-2 rounded">
                    </div>

                    <div>
                        <label for="admin_contact" class="block font-semibold mb-1">Contact Number</label>
                        <input type="text" name="admin_contact" id="admin_contact" value="{{ old('admin_contact', $admin->cp_no ?? '') }}" required class="w-full border border-gray-300 px-3 py-2 rounded">
                    </div>

                    <input type="hidden" name="admin_role" value="school_admin">

                    <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="admin_password" class="block font-semibold mb-1">New Password (optional)</label>
                            <input type="password" name="admin_password" id="admin_password" class="w-full border border-gray-300 px-3 py-2 rounded">
                        </div>

                        <div>
                            <label for="admin_password_confirmation" class="block font-semibold mb-1">Confirm Password</label>
                            <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" class="w-full border border-gray-300 px-3 py-2 rounded">
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Update School & Admin
                </button>
            </div>
        </form>
    </div>

    <div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0" id="confirmModalContent">
            <h3 class="text-2xl font-bold mb-4 text-gray-800">Update School & Admin</h3>
            <p id="confirmMessage" class="mb-6 text-gray-700 leading-relaxed">Are you sure you want to update this school and admin?</p>
            <div class="flex justify-end space-x-3">
                <button id="noButton" class="px-6 py-2 bg-gray-300 text-gray-800 font-semibold rounded-lg hover:bg-gray-400 transition-colors">No</button>
                <button id="yesButton" class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">Yes</button>
            </div>
        </div>
    </div>

    <div id="loadingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full text-center">
            <div class="loader ease-linear rounded-full border-4 border-t-4 border-blue-200 h-12 w-12 mb-4 mx-auto"></div>
            <h3 class="text-xl font-semibold text-gray-800">Updating School & Admin...</h3>
            <p class="text-gray-500">Please wait while we save your changes.</p>
        </div>
    </div>

    <div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-50 p-4">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full transform transition-all duration-300 scale-95 opacity-0" id="successModalContent">
            <h3 class="text-2xl font-bold mb-4 text-gray-800">Success!</h3>
            <p id="successMessage" class="mb-6 text-gray-700 leading-relaxed">School and admin updated successfully!</p>
            <div class="flex justify-end">
                <button id="successModalClose" class="px-6 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors">OK</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const schoolForm = document.getElementById('schoolForm');
            const confirmModal = document.getElementById('confirmModal');
            const confirmModalContent = document.getElementById('confirmModalContent');
            const loadingModal = document.getElementById('loadingModal');
            const successModal = document.getElementById('successModal');
            const successModalContent = document.getElementById('successModalContent');
            const yesButton = document.getElementById('yesButton');
            const noButton = document.getElementById('noButton');
            const successModalClose = document.getElementById('successModalClose');

            function showModal(modalElement, contentElement) {
                modalElement.classList.remove('hidden');
                if (contentElement) {
                    setTimeout(() => {
                        contentElement.classList.remove('scale-95', 'opacity-0');
                        contentElement.classList.add('scale-100', 'opacity-100');
                    }, 10);
                }
            }

            function hideModal(modalElement, contentElement, callback = () => {}) {
                if (contentElement) {
                    contentElement.classList.remove('scale-100', 'opacity-100');
                    contentElement.classList.add('scale-95', 'opacity-0');
                    setTimeout(() => {
                        modalElement.classList.add('hidden');
                        callback();
                    }, 300);
                } else {
                    modalElement.classList.add('hidden');
                    callback();
                }
            }

            schoolForm.addEventListener('submit', function(event) {
                event.preventDefault();
                showModal(confirmModal, confirmModalContent);
            });

            yesButton.addEventListener('click', function() {
                hideModal(confirmModal, confirmModalContent, () => {
                    showModal(loadingModal);
                    
                    const formData = new FormData(schoolForm);
                    formData.append('_method', 'PUT');

                    const actionUrl = schoolForm.action;
                    const csrfToken = schoolForm.querySelector('input[name="_token"]').value;

                    fetch(actionUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => {
                        hideModal(loadingModal);
                        if (!response.ok) {
                             // Handle validation errors or other server-side errors
                            return response.json().then(err => { throw err; });
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.message) {
                            document.getElementById('successMessage').textContent = data.message;
                            showModal(successModal, successModalContent);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        hideModal(loadingModal, null, () => {
                            // Display a generic error message
                            alert('An unexpected error occurred. Please try again.');
                        });
                    });
                });
            });

            noButton.addEventListener('click', function() {
                hideModal(confirmModal, confirmModalContent);
            });

            successModalClose.addEventListener('click', function() {
                hideModal(successModal, successModalContent, () => {
                    window.location.href = "{{ route('admin.schools.index') }}";
                });
            });

            [confirmModal, successModal, loadingModal].forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target.id === modal.id || e.target.closest('.loader') === null) {
                        if (modal.id !== 'loadingModal') {
                            hideModal(modal, modal.querySelector('[id$="Content"]'));
                        }
                    }
                });
            });
        });
    </script>
    @endpush
</x-layout>