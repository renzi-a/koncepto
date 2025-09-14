<x-profile-link>
    <div class="container mx-auto px-4 py-6 space-y-6">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Track Your Orders</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($orders as $order)
                <div class="bg-white rounded-2xl shadow-lg p-5 border border-gray-200 transition-transform duration-300 hover:scale-105 hover:shadow-xl">
                    <p class="text-lg font-bold text-gray-900 mb-1">Order #{{ $order->id }}</p>
                    <p class="text-sm text-gray-600 mb-2">Status:
                        @php
                            $statusClasses = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'shipped' => 'bg-teal-100 text-teal-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <span class="px-2 py-1 text-xs font-semibold rounded-md {{ $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </p>
                    <p class="text-sm text-gray-600 mb-4">
                        School: <span class="font-medium text-gray-800">{{ $order->school->school_name ?? 'N/A' }}</span>
                    </p>
                    <button
                        onclick="showMap('{{ $order->type }}', {{ $order->id }}, {{ $order->school->lat ?? 0 }}, {{ $order->school->lng ?? 0 }})"
                        class="w-full mt-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-300 transform active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Track Delivery
                    </button>
                </div>
            @empty
                <div class="col-span-full text-center py-8">
                    <p class="text-gray-500 text-lg">You don't have any active orders to track. 😔</p>
                </div>
            @endforelse
        </div>

        <div id="mapContainer" class="mt-8 hidden">
            <h2 class="text-xl font-semibold mb-3">Driver Location</h2>
            <div id="map" class="w-full h-[500px] rounded-xl border border-gray-300 shadow-md"></div>

            <div id="distance" class="mt-3 inline-flex items-center space-x-2 px-4 py-2 bg-green-100 text-green-800 font-semibold rounded-lg shadow-sm">
                <span>Loading route...</span>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>

    <script>
        let map, driverMarker, schoolMarker, routingControl, interval;

        const driverIcon = L.icon({
            iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        const schoolIcon = L.icon({
            iconUrl: 'https://cdn.rawgit.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        function showMap(type, id, schoolLat, schoolLng) {
            document.getElementById('mapContainer').classList.remove('hidden');

            if (!map) {
                map = L.map('map').setView([schoolLat, schoolLng], 14);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);
            }

            if (driverMarker) map.removeLayer(driverMarker);
            if (schoolMarker) map.removeLayer(schoolMarker);
            if (routingControl) map.removeControl(routingControl);
            clearInterval(interval);

            schoolMarker = L.marker([schoolLat, schoolLng], { icon: schoolIcon })
                .addTo(map)
                .bindPopup("Destination");

            async function updateLocation() {
                try {
                    const res = await fetch(`/track-orders/${type}/${id}/location`);
                    const data = await res.json();

                    if (data?.driver_latitude && data?.driver_longitude) {
                        const lat = parseFloat(data.driver_latitude);
                        const lng = parseFloat(data.driver_longitude);

                        if (driverMarker) {
                            driverMarker.setLatLng([lat, lng]);
                        } else {
                            driverMarker = L.marker([lat, lng], { icon: driverIcon })
                                .addTo(map)
                                .bindPopup("Driver");
                        }

                        map.setView([lat, lng], map.getZoom());

                        if (routingControl) map.removeControl(routingControl);

                        routingControl = L.Routing.control({
                            waypoints: [
                                L.latLng(lat, lng),
                                L.latLng(schoolLat, schoolLng)
                            ],
                            lineOptions: { styles: [{ color: '#16a34a', weight: 6 }] },
                            show: false,
                            addWaypoints: false,
                            routeWhileDragging: false,
                        }).on('routesfound', function(e) {
                            const route = e.routes[0];
                            const distanceKm = (route.summary.totalDistance / 1000).toFixed(2);
                            const durationMin = Math.round(route.summary.totalTime / 60);

                            document.getElementById('distance').innerHTML =
                                `🚗 ${distanceKm} km away | ⏱ ETA: ${durationMin} mins`;
                        }).addTo(map);
                    }
                } catch (err) {
                    console.error("Tracking error:", err);
                    document.getElementById('distance').innerText = "🚫 Error fetching location.";
                }
            }

            updateLocation();
            interval = setInterval(updateLocation, 5000); 
        }
    </script>
</x-profile-link>