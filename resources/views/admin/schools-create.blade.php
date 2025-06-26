<x-layout />
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">School Overview</h1>
        <a href="{{ route('admin.schools.create') }}"
           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
            Add School & Admin
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($schools as $school)
            <div class="bg-white border border-gray-200 rounded-md shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold">{{ \Illuminate\Support\Str::limit($school->school_name, 20) }}</h2>
                    <a href="{{ route('admin.schools.show', $school->id) }}"
                       class="text-green-600 hover:underline text-sm">View</a>
                </div>
                <p class="text-gray-600 text-sm mt-1">{{ $school->address }}</p>
                <div class="mt-4 space-y-1 text-sm text-gray-700">
                    <p><span class="font-semibold">Total Orders:</span> {{ $school->orders_count }}</p>
                    <p><span class="font-semibold">Total Users:</span> {{ $school->users_count }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>
