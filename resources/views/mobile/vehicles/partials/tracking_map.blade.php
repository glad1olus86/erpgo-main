{{-- Mobile GPS Tracking Map Component --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<style>
    /* Mobile Tracking Map Styles */
    .mobile-tracking-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .mobile-tracking-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .mobile-tracking-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #FF0049;
        margin: 0;
    }
    
    .mobile-track-controls {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
        position: relative;
        z-index: 1000;
    }
    
    #mobile-trip-selector {
        padding: 6px 10px;
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        font-size: 13px;
        background: #fff;
        min-width: 120px;
        max-width: 150px;
        position: relative;
        z-index: 1001;
        -webkit-appearance: menulist;
        cursor: pointer;
    }
    
    #mobile-trip-selector:focus {
        outline: none;
        border-color: #FF0049;
    }
    
    #mobile-track-date {
        padding: 6px 10px;
        border: 1px solid #e8e8e8;
        border-radius: 8px;
        font-size: 13px;
        background: #fff;
        width: 140px;
        position: relative;
        z-index: 1001;
        -webkit-appearance: none;
        cursor: pointer;
    }
    
    #mobile-track-date::-webkit-calendar-picker-indicator {
        cursor: pointer;
        padding: 0;
        margin: 0;
    }
    
    #mobile-track-date:focus {
        outline: none;
        border-color: #FF0049;
    }
    
    #mobile-tracking-map {
        height: 300px;
        z-index: 1;
        background: #f0f0f0;
    }
    
    .mobile-no-track-data {
        display: none;
        text-align: center;
        padding: 40px 20px;
    }
    
    .mobile-no-track-data.show {
        display: block;
    }
    
    .mobile-track-info {
        display: none;
        padding: 12px 15px;
        border-top: 1px solid #f0f0f0;
        background: #fafafa;
    }
    
    .mobile-track-info.show {
        display: block;
    }
    
    .mobile-track-stats {
        display: flex;
        justify-content: space-around;
        text-align: center;
    }
    
    .mobile-track-stat {
        flex: 1;
    }
    
    .mobile-track-stat-label {
        font-size: 11px;
        color: #666;
        display: block;
        margin-bottom: 2px;
    }
    
    .mobile-track-stat-value {
        font-size: 14px;
        font-weight: 600;
        color: #000;
    }
    
    /* Map Markers */
    .track-marker .marker-icon {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 10px;
        font-weight: bold;
        border: 2px solid white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
    }
    
    .track-marker .marker-icon.bg-success { background-color: #22B404; }
    .track-marker .marker-icon.bg-danger { background-color: #dc3545; }
    .track-marker .marker-icon.bg-primary { background-color: #FF0049; }
    
    .track-marker.current .marker-icon.pulse {
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(255, 0, 73, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(255, 0, 73, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 0, 73, 0); }
    }
    
    /* Active badge */
    .mobile-badge-active {
        background: rgba(34, 180, 4, 0.15);
        color: #22B404;
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 600;
    }
</style>

<div class="mobile-tracking-card">
    <div class="mobile-tracking-header">
        <h6 class="mobile-tracking-title">
            <i class="ti ti-map-pin" style="font-size: 18px;"></i>
            {{ __('Route Map') }}
        </h6>
        <div class="mobile-track-controls">
            <select id="mobile-trip-selector" style="display: none;"></select>
            <input type="date" id="mobile-track-date" 
                   value="{{ date('Y-m-d') }}" 
                   max="{{ date('Y-m-d') }}">
        </div>
    </div>
    
    <div id="mobile-tracking-map"></div>
    
    <div class="mobile-no-track-data" id="mobile-no-track-data">
        <i class="ti ti-map-off" style="font-size: 40px; color: #ccc;"></i>
        <p class="text-muted mt-2 mb-0" style="font-size: 13px;">{{ __('No tracking data for this day') }}</p>
    </div>
    
    <div class="mobile-track-info" id="mobile-track-info">
        <div class="mobile-track-stats">
            <div class="mobile-track-stat">
                <span class="mobile-track-stat-label">{{ __('Start') }}</span>
                <span class="mobile-track-stat-value" id="mobile-trip-start">-</span>
            </div>
            <div class="mobile-track-stat">
                <span class="mobile-track-stat-label">{{ __('End') }}</span>
                <span class="mobile-track-stat-value" id="mobile-trip-end">-</span>
            </div>
            <div class="mobile-track-stat">
                <span class="mobile-track-stat-label">{{ __('Distance') }}</span>
                <span class="mobile-track-stat-value" id="mobile-trip-distance">-</span>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
(function() {
    const vehicleId = {{ $vehicle->id }};
    let map = null;
    let trackLayers = [];
    let markers = [];
    let selectedTripId = null;

    function initMap() {
        const mapEl = document.getElementById('mobile-tracking-map');
        if (!mapEl || typeof L === 'undefined') {
            console.error('Map element or Leaflet not found');
            return false;
        }
        
        map = L.map('mobile-tracking-map', {
            zoomControl: true,
            attributionControl: false
        }).setView([48.46, 35.05], 12);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);
        
        return true;
    }

    function clearTrack() {
        if (!map) return;
        trackLayers.forEach(layer => map.removeLayer(layer));
        markers.forEach(marker => map.removeLayer(marker));
        trackLayers = [];
        markers = [];
    }

    function drawSegment(points, color, dashed) {
        const latlngs = points.map(p => [p.lat, p.lng]);
        const options = {
            color: color,
            weight: 4,
            opacity: 0.8
        };
        if (dashed) {
            options.dashArray = '10, 10';
        }
        const polyline = L.polyline(latlngs, options).addTo(map);
        trackLayers.push(polyline);
        return polyline;
    }

    function addMarker(point, type, popupText) {
        let iconHtml, className;
        
        if (type === 'start') {
            iconHtml = '<div class="marker-icon bg-success">▶</div>';
            className = 'track-marker start';
        } else if (type === 'end') {
            iconHtml = '<div class="marker-icon bg-danger">■</div>';
            className = 'track-marker end';
        } else {
            iconHtml = '<div class="marker-icon bg-primary pulse">●</div>';
            className = 'track-marker current';
        }

        const marker = L.marker([point.lat, point.lng], {
            icon: L.divIcon({
                className: className,
                html: iconHtml,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            })
        }).addTo(map);
        
        if (popupText) {
            marker.bindPopup(popupText);
        }
        
        markers.push(marker);
        return marker;
    }

    function drawTrack(points, trip) {
        if (!points || points.length === 0) return;

        let normalSegment = [];
        
        points.forEach((point, index) => {
            if (point.is_gap && normalSegment.length > 0) {
                drawSegment(normalSegment, '#FF0049', false);
                const gapSegment = [normalSegment[normalSegment.length - 1], point];
                drawSegment(gapSegment, '#999', true);
                normalSegment = [point];
            } else {
                normalSegment.push(point);
            }
        });
        
        if (normalSegment.length > 0) {
            drawSegment(normalSegment, '#FF0049', false);
        }
        
        const startTime = new Date(trip.started_at).toLocaleTimeString('uk-UA', {hour: '2-digit', minute: '2-digit'});
        addMarker(points[0], 'start', '{{ __("Start") }}: ' + startTime);
        
        if (trip.ended_at) {
            const endTime = new Date(trip.ended_at).toLocaleTimeString('uk-UA', {hour: '2-digit', minute: '2-digit'});
            addMarker(points[points.length - 1], 'end', '{{ __("End") }}: ' + endTime);
        } else {
            addMarker(points[points.length - 1], 'current', '{{ __("Current position") }}');
        }
        
        const bounds = points.map(p => [p.lat, p.lng]);
        map.fitBounds(bounds, { padding: [30, 30] });
    }

    function updateTripSelector(trips, currentTripId) {
        const selector = document.getElementById('mobile-trip-selector');
        
        if (!trips || trips.length <= 1) {
            selector.style.display = 'none';
            return;
        }
        
        selector.innerHTML = '';
        trips.forEach((trip, index) => {
            const option = document.createElement('option');
            option.value = trip.id;
            const time = new Date(trip.started_at).toLocaleTimeString('uk-UA', {hour: '2-digit', minute: '2-digit'});
            option.textContent = trip.label + ' (' + time + ')';
            if (trip.is_active) {
                option.textContent += ' 🟢';
            }
            selector.appendChild(option);
        });
        
        selector.style.display = 'block';
        
        if (currentTripId) {
            selector.value = currentTripId;
        }
    }

    function updateTripInfo(trip) {
        const infoEl = document.getElementById('mobile-track-info');
        
        if (!trip) {
            infoEl.classList.remove('show');
            return;
        }
        
        infoEl.classList.add('show');
        
        const startTime = new Date(trip.started_at).toLocaleTimeString('uk-UA', {hour: '2-digit', minute: '2-digit'});
        document.getElementById('mobile-trip-start').textContent = startTime;
        
        if (trip.ended_at) {
            const endTime = new Date(trip.ended_at).toLocaleTimeString('uk-UA', {hour: '2-digit', minute: '2-digit'});
            document.getElementById('mobile-trip-end').textContent = endTime;
        } else {
            document.getElementById('mobile-trip-end').innerHTML = '<span class="mobile-badge-active">{{ __("Active") }}</span>';
        }
        
        if (trip.total_distance_km) {
            document.getElementById('mobile-trip-distance').textContent = trip.total_distance_km + ' km';
        } else {
            document.getElementById('mobile-trip-distance').textContent = '-';
        }
    }

    function showNoData() {
        document.getElementById('mobile-tracking-map').style.display = 'none';
        document.getElementById('mobile-no-track-data').classList.add('show');
        document.getElementById('mobile-track-info').classList.remove('show');
        document.getElementById('mobile-trip-selector').style.display = 'none';
    }

    function showMap() {
        document.getElementById('mobile-tracking-map').style.display = 'block';
        document.getElementById('mobile-no-track-data').classList.remove('show');
        if (map) map.invalidateSize();
    }

    async function loadTrack(date, tripId = null) {
        try {
            let url = `/vehicles/${vehicleId}/track?date=${date}`;
            if (tripId) {
                url += `&trip_id=${tripId}`;
            }
            
            const response = await fetch(url);
            
            if (!response.ok) {
                showNoData();
                return;
            }
            
            const data = await response.json();
            
            clearTrack();
            
            if (!data.points || data.points.length === 0) {
                showNoData();
                return;
            }
            
            selectedTripId = data.trip.id;
            
            updateTripSelector(data.trips, selectedTripId);
            showMap();
            drawTrack(data.points, data.trip);
            updateTripInfo(data.trip);
            
        } catch (error) {
            console.error('Error loading track:', error);
            showNoData();
        }
    }

    function init() {
        if (!initMap()) {
            console.error('Failed to initialize mobile map');
            return;
        }
        
        const dateInput = document.getElementById('mobile-track-date');
        const tripSelector = document.getElementById('mobile-trip-selector');
        
        // Fix for mobile date picker - ensure it opens on click
        dateInput.addEventListener('click', function(e) {
            e.stopPropagation();
            this.showPicker && this.showPicker();
        });
        
        dateInput.addEventListener('touchend', function(e) {
            e.stopPropagation();
            this.focus();
            this.showPicker && this.showPicker();
        });
        
        dateInput.addEventListener('change', function() {
            selectedTripId = null;
            loadTrack(this.value);
        });
        
        tripSelector.addEventListener('change', function() {
            const date = dateInput.value;
            loadTrack(date, this.value);
        });
        
        // Load today's track
        loadTrack(dateInput.value);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
