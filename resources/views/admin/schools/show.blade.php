<x-layout>
    @push('styles')
        {{-- Leaflet CSS --}}
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxNIDN1/rXgSNGF0K6w6rBwz7jMv1z5kPq30sN6qI=" crossorigin="" />
        <style>
            #map {
                height: 500px;
                width: 100%;
                border-radius: 0.75rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
        </style>
    @endpush

    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">School & Administrator Details</h1>
            <a href="{{ route('admin.schools.index') }}"
               class="inline-flex items-center text-sm text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-full transition shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Schools
            </a>
        </div>

        {{-- Card --}}
        <div class="bg-white p-6 rounded shadow-md space-y-6">
            {{-- School Info --}}
            <div>
                <h2 class="text-xl font-semibold mb-4">School Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">School Name</label>
                        <p class="w-full bg-gray-50 border border-gray-200 px-3 py-2 rounded">
                            {{ $school->school_name }}
                        </p>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">School Address</label>
                        <p class="w-full bg-gray-50 border border-gray-200 px-3 py-2 rounded">
                            {{ $school->address }}
                        </p>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">School Email</label>
                        <p class="w-full bg-gray-50 border border-gray-200 px-3 py-2 rounded">
                            {{ $school->school_email }}
                        </p>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Principal</label>
                        <p class="w-full bg-gray-50 border border-gray-200 px-3 py-2 rounded">
                            {{ $school->principal }}
                        </p>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">School Logo</label>
                        @if($school->image)
                            <img src="{{ asset('storage/' . $school->image) }}" 
                                 alt="Logo" class="w-16 h-16 mt-2 rounded-md">
                        @else
                            <p class="text-gray-500">No logo uploaded</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Admin Officer --}}
            <div>
                <h2 class="text-xl font-semibold mb-4">Administrative Officer</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold mb-1">First Name</label>
                        <p class="w-full bg-gray-50 border border-gray-200 px-3 py-2 rounded">
                            {{ $admin->first_name ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Last Name</label>
                        <p class="w-full bg-gray-50 border border-gray-200 px-3 py-2 rounded">
                            {{ $admin->last_name ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Email</label>
                        <p class="w-full bg-gray-50 border border-gray-200 px-3 py-2 rounded">
                            {{ $admin->email ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Contact Number</label>
                        <p class="w-full bg-gray-50 border border-gray-200 px-3 py-2 rounded">
                            {{ $admin->cp_no ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Location --}}
            <div>
                <h2 class="text-xl font-semibold mb-4">School Location</h2>
                <div id="map"></div>
                <p id="map-address-display" class="text-sm text-gray-600 mt-2">
                    Address: {{ $school->address }}
                </p>
            </div>
        </div>
    </div>

    @push('scripts')
        {{-- Leaflet JS --}}
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nqc1w3pw9jZ3f9W5Z5d9E6B7x9+oGgH4fJ1kC10A1y/sA==" crossorigin="">
        </script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if(!empty($school->lat) && !empty($school->lng))
            const lat = {{ $school->lat }};
            const lng = {{ $school->lng }};

            const map = L.map('map').setView([lat, lng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            L.marker([lat, lng]).addTo(map)
                .bindPopup(`<b>{{ $school->school_name }}</b><br>{{ $school->address }}`)
                .openPopup();
        @else
            document.getElementById('map').style.display = 'none';
            document.getElementById('map-address-display').textContent = 'Location coordinates are not available for this school.';
        @endif
    });
</script>

    @endpush
</x-layout>
