<x-layout>
<style>
  @keyframes fadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
  }

  .animate-fadeIn {
    animation: fadeIn 0.3s ease-out forwards;
  }

  @keyframes bounceScale {
    0%, 100% { transform: scale(1) translateY(0); opacity: 1; }
    50% { transform: scale(1.15) translateY(-10%); opacity: 0.8; }
  }

  .animate-bounceScale {
    animation: bounceScale 1.2s ease-in-out infinite;
  }
</style>


<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Add New School & Administrator</h1>
        <a href="{{ route('admin.schools.index') }}"
            class="inline-flex items-center text-sm text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 px-4 py-2 rounded-full transition shadow-sm">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
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

    <form id="schoolForm" action="{{ route('admin.schools.store') }}" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded shadow-md space-y-6">
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
                           placeholder="e.g. Barangay X, Nasugbu, Batangas" required
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

            <div class="mt-6">
                <label class="block font-semibold mb-1">Select Location (Map)</label>
                <div id="map" class="h-96 w-full rounded shadow"></div>
                <input type="hidden" id="lat" name="lat" value="{{ old('lat') }}">
                <input type="hidden" id="lng" name="lng" value="{{ old('lng') }}">
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

                <input type="hidden" name="admin_role" value="school_admin">
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
            <button type="submit" id="submitButton" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                Save School & Admin
            </button>
        </div>
    </form>
</div>

<div id="loadingOverlay" class="hidden fixed inset-0 z-50 bg-black bg-opacity-40 flex items-center justify-center">
  <div class="bg-white rounded-xl p-8 shadow-lg flex items-center space-x-5 animate-fadeIn">
    <div class="animate-bounceScale">
      <svg class="animate-spin text-[#56AB2F]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
      </svg>
    </div>
    <span class="text-[#56AB2F] font-semibold text-lg">Saving...</span>
  </div>
</div>


<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>

<script>
document.getElementById('schoolForm').addEventListener('submit', function(e) {
    e.preventDefault();

    Swal.fire({
        title: "Are you sure?",
        text: "Please confirm to save the school and admin.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#16a34a",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, save it!"
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('loadingOverlay').classList.remove('hidden');
            this.submit();
        }
    });
});


document.addEventListener("DOMContentLoaded", function () {
    const map = L.map('map').setView([14.0940, 120.6890], 13); // Nasugbu center

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;

    const geocoder = L.Control.geocoder({
        defaultMarkGeocode: false
    })
    .on('markgeocode', function(e) {
        const latlng = e.geocode.center;
        if (marker) map.removeLayer(marker);
        marker = L.marker(latlng).addTo(map).bindPopup(e.geocode.name).openPopup();
        map.setView(latlng, 16);
        document.getElementById('lat').value = latlng.lat;
        document.getElementById('lng').value = latlng.lng;
    })
    .addTo(map);

    // Click to place marker
    map.on('click', function(e) {
        const { lat, lng } = e.latlng;
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;
    });
});
</script>

<script>
    var map = L.map('map').setView([14.0940, 120.6890], 13);
    var marker;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Add search box
    L.Control.geocoder({
        defaultMarkGeocode: false
    })
    .on('markgeocode', function(e) {
        const latlng = e.geocode.center;
        map.setView(latlng, 17);
        updateLocation(latlng.lat, latlng.lng);
    })
    .addTo(map);

    // Click map to update
    map.on('click', function(e) {
        updateLocation(e.latlng.lat, e.latlng.lng);
    });

    function updateLocation(lat, lng) {
        document.getElementById('lat').value = lat;
        document.getElementById('lng').value = lng;

        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng]).addTo(map);

        // Get address from lat/lng
fetch(`https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`)
    .then(res => res.json())
    .then(data => {
        if (data && data.display_name) {
            const parts = data.display_name.split(',');
            const schoolName = parts[0]?.trim() ?? '';
            const address = parts.slice(1).join(',').trim();

            document.getElementById('school_name').value = schoolName;
            document.getElementById('address').value = address;
        } else {
            document.getElementById('address').value = 'Address not found';
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById('address').value = 'Failed to fetch address';
    });

    }
</script>

</x-layout>
