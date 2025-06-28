<x-layout />
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">School Overview</h1>
        <a href="{{ route('admin.schools.create') }}"
           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
            Add School
        </a>
    </div>

    @if($schools->isEmpty())
        <div class="text-gray-500 text-center py-10">
            No schools found.
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($schools as $school)
                <div class="bg-white border border-gray-200 rounded-lg shadow-lg p-6 space-y-4">
                    @if($school->image)
                        <div class="w-full h-40 bg-gray-100 rounded overflow-hidden">
                            <img src="{{ asset('storage/' . $school->image) }}" alt="{{ $school->school_name }} Logo"
                                 class="w-full h-full object-cover object-center">
                        </div>
                    @else
                        <div class="w-full h-40 bg-gray-200 rounded flex items-center justify-center text-gray-400">
                            No Logo
                        </div>
                    @endif

                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-semibold">
                            {{ \Illuminate\Support\Str::limit($school->school_name, 30) }}
                        </h2>
                        <a href="{{ route('admin.schools.show', $school->id) }}"
                           class="text-green-600 hover:underline text-sm">View</a>
                    </div>

                    <p class="text-gray-600 text-sm">{{ $school->address }}</p>

                    <div class="space-y-1 text-sm text-gray-700">
                    <div class="text-sm text-gray-700 space-y-1">
                        <p><span class="font-semibold">Total Orders:</span> {{ $school->orders_count }}</p>
                        <p>
                            <span class="font-semibold">Total Users:</span>
                            {{ $school->users_count }} |
                            <span class="text-gray-600">Admins:</span> {{ $school->admin_count }} |
                            <span class="text-gray-600">Teachers:</span> {{ $school->teacher_count }} |
                            <span class="text-gray-600">Students:</span> {{ $school->student_count }}
                        </p>
                    </div>

                    </div>
                    <div class="flex justify-end gap-4 pt-4">

                        <a href="{{ route('admin.schools.edit', $school->id) }}" class="hover:scale-105 transition-transform">
                            <img src="{{ asset('images/icons/edit.png') }}" alt="Edit" class="w-6 h-6">
                        </a>

                        <form action="{{ route('admin.schools.destroy', $school->id) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this school?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="hover:scale-105 transition-transform">
                                <img src="{{ asset('images/icons/delete.png') }}" alt="Delete" class="w-6 h-6">
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
