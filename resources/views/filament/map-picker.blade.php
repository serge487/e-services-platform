<div
    x-data="{
        map: null,
        marker: null,

        initMap() {
            // Get existing values if editing
            const latInput = document.querySelector('input[id*=latitude]');
            const lngInput = document.querySelector('input[id*=longitude]');

            const defaultLat = latInput?.value || 33.8938;
            const defaultLng = lngInput?.value || 35.5018;

            this.map = L.map('map-picker').setView([defaultLat, defaultLng], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);

            // If editing, show existing marker
            if (latInput?.value && lngInput?.value) {
                this.marker = L.marker([latInput.value, lngInput.value]).addTo(this.map);
            }

            this.map.on('click', (e) => {
                const { lat, lng } = e.latlng;

                // Move or place marker
                if (this.marker) {
                    this.marker.setLatLng([lat, lng]);
                } else {
                    this.marker = L.marker([lat, lng]).addTo(this.map);
                }

                // Auto-fill the inputs
                const latField = document.querySelector('input[id*=latitude]');
                const lngField = document.querySelector('input[id*=longitude]');

                if (latField) {
                    latField.value = lat.toFixed(7);
                    latField.dispatchEvent(new Event('input'));
                }
                if (lngField) {
                    lngField.value = lng.toFixed(7);
                    lngField.dispatchEvent(new Event('input'));
                }
            });

            // If admin types manually, update marker on map
            latInput?.addEventListener('change', () => this.updateMarker());
            lngInput?.addEventListener('change', () => this.updateMarker());
        },

        updateMarker() {
            const latInput = document.querySelector('input[id*=latitude]');
            const lngInput = document.querySelector('input[id*=longitude]');

            const lat = parseFloat(latInput?.value);
            const lng = parseFloat(lngInput?.value);

            if (!isNaN(lat) && !isNaN(lng)) {
                if (this.marker) {
                    this.marker.setLatLng([lat, lng]);
                } else {
                    this.marker = L.marker([lat, lng]).addTo(this.map);
                }
                this.map.setView([lat, lng], 13);
            }
        }
    }"
    x-init="$nextTick(() => initMap())"
    class="col-span-2"
>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <div class="mb-3 text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
        📍 <span>Click anywhere on the map to drop a pin and auto-fill the coordinates above. Or type manually and the pin will update.</span>
    </div>

    <div id="map-picker" style="height: 400px; width: 100%; border-radius: 8px; border: 1px solid #e5e7eb;"></div>
</div>
