<x-layout>
<div class="container mx-auto px-4 py-6">
    <div class="flex items-center space-x-8 mb-8">
        @if($school->image)
            <img src="{{ asset('storage/' . $school->image) }}" alt="School Logo"
                 class="w-32 h-32 object-cover rounded-full border-4 border-green-600 shadow-md">
        @else
            <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center text-gray-400 border-4 border-gray-300 shadow-md">
                No Logo
            </div>
        @endif

        <div>
            <h1 class="text-4xl font-bold text-gray-800">{{ $school->school_name }}</h1>
            <h1 class="text-lg mt-2 font-bold text-gray-800">{{ $school->principal}}</h1>
            <p class="text-lg text-gray-600 ">{{ $school->address }}</p>
            <p class="text-md text-gray-500">{{ $school->school_email }}</p>
        </div>
    </div>

    <div class="mb-4 border-b border-gray-200">
        <ul class="flex space-x-4">
            <li>
                <a href="#admin" class="tab-link font-semibold text-green-600 border-b-2 border-green-600 pb-2" onclick="showTab('admin')">School Admin</a>
            </li>
            <li>
                <a href="#teachers" class="tab-link font-semibold text-gray-600 hover:text-green-600 pb-2" onclick="showTab('teachers')">Teachers</a>
            </li>
            <li>
                <a href="#students" class="tab-link font-semibold text-gray-600 hover:text-green-600 pb-2" onclick="showTab('students')">Students</a>
            </li>
        </ul>
    </div>

    <div id="admin" class="tab-content">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">School Administrator</h2>
            <a href="{{ route('admin.users.create', $school->id) }}"
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                Add User
            </a>
        </div>

        @if ($school->user)
            <div class="bg-white border rounded-lg px-4 py-3 flex justify-between items-center shadow-sm">
                <div>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $school->user->first_name }} {{ $school->user->last_name }}
                    </p>
                    <p class="text-sm text-gray-700">{{ $school->user->email }}</p>
                    <p class="text-sm text-gray-700">{{ $school->user->cp_no }}</p>
                    <span class="inline-block text-sm px-2 py-1 mt-1 bg-green-100 text-green-800 rounded">
                        {{ ucfirst($school->user->role) }}
                    </span>
                </div>

                <div class="flex space-x-3">
                    <a href="{{ route('admin.users.edit', $school->user->id) }}" class="hover:scale-110 transition">
                        <img src="{{ asset('images/icons/edit.png') }}" alt="Edit" class="w-5 h-5">
                    </a>
                    <form action="" method="POST" onsubmit="return confirm('Delete this user?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="hover:scale-110 transition">
                            <img src="{{ asset('images/icons/delete.png') }}" alt="Delete" class="w-5 h-5">
                        </button>
                    </form>
                </div>
            </div>
        @else
            <p class="text-gray-500 text-base">No school admin linked.</p>
        @endif
    </div>

    <div id="teachers" class="tab-content hidden">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Teachers</h2>
            <a href="{{ route('admin.users.create', $school->id) }}"
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                Add User
            </a>
        </div>

        @php $teachers = $school->users->where('role', 'teacher'); @endphp

        @if($teachers->isEmpty())
            <p class="text-gray-500 text-base">No teachers found.</p>
        @else
            <ul class="space-y-2">
                @foreach ($teachers as $teacher)
                    <li class="bg-white border rounded-lg px-4 py-3 flex justify-between items-center shadow-sm">
                        <div>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ $teacher->first_name }} {{ $teacher->last_name }}
                            </p>
                            <p class="text-sm text-gray-700">{{ $teacher->email }}</p>
                            <p class="text-sm text-gray-700">{{ $teacher->cp_no }}</p>
                            <span class="inline-block text-sm px-2 py-1 mt-1 bg-blue-100 text-blue-800 rounded">
                                {{ ucfirst($teacher->role) }}
                            </span>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('admin.users.edit', $teacher->id) }}" class="hover:scale-110 transition">
                                <img src="{{ asset('images/icons/edit.png') }}" alt="Edit" class="w-5 h-5">
                            </a>
                            <form action="" method="POST" onsubmit="return confirm('Delete this user?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="hover:scale-110 transition">
                                    <img src="{{ asset('images/icons/delete.png') }}" alt="Delete" class="w-5 h-5">
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div id="students" class="tab-content hidden">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Students</h2>
            <a href="{{ route('admin.users.create', $school->id) }}"
               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
                Add User
            </a>
        </div>

        @php $students = $school->users->where('role', 'student'); @endphp

        @if($students->isEmpty())
            <p class="text-gray-500 text-base">No students found.</p>
        @else
            <ul class="space-y-2">
                @foreach ($students as $student)
                    <li class="bg-white border rounded-lg px-4 py-3 flex justify-between items-center shadow-sm">
                        <div>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ $student->first_name }} {{ $student->last_name }}
                            </p>
                            <p class="text-sm text-gray-700">{{ $student->email }}</p>
                            <p class="text-sm text-gray-700">{{ $student->cp_no }}</p>
                            <span class="inline-block text-sm px-2 py-1 mt-1 bg-yellow-100 text-yellow-800 rounded">
                                {{ ucfirst($student->role) }}
                            </span>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('admin.users.edit', $student->id) }}" class="hover:scale-110 transition">
                                <img src="{{ asset('images/icons/edit.png') }}" alt="Edit" class="w-5 h-5">
                            </a>
                            <form action="" method="POST" onsubmit="return confirm('Delete this user?');">
                                @csrf
                                @method('DELETE')1
                                <button type="submit" class="hover:scale-110 transition">
                                    <img src="{{ asset('images/icons/delete.png') }}" alt="Delete" class="w-5 h-5">
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

<script>
    function showTab(tab) {
        const contents = document.querySelectorAll('.tab-content');
        const links = document.querySelectorAll('.tab-link');

        contents.forEach(c => c.classList.add('hidden'));
        links.forEach(l => {
            l.classList.remove('text-green-600', 'border-green-600', 'border-b-2');
            l.classList.add('text-gray-600');
        });

        document.getElementById(tab).classList.remove('hidden');
        document.querySelector(`a[href="#${tab}"]`).classList.add('text-green-600', 'border-b-2', 'border-green-600');
    }
</script>
</x-layout>