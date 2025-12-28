{{-- Mobile Fuel Consumption Card - synced with tracking map date --}}
<style>
    .mobile-fuel-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-top: 15px;
    }
    
    .mobile-fuel-header {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .mobile-fuel-title {
        font-size: 14px;
        font-weight: 600;
        color: #FF0049;
        margin: 0;
    }
    
    .mobile-fuel-body {
        padding: 10px 15px;
    }
    
    .mobile-fuel-trip-list {
        max-height: 200px;
        overflow-y: auto;
        padding-right: 10px;
    }
    
    .mobile-fuel-trip-item {
        display: flex;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f5f5f5;
        font-size: 13px;
    }
    
    .mobile-fuel-trip-item:last-child {
        border-bottom: none;
    }
    
    .mobile-fuel-trip-item.empty {
        opacity: 0.5;
    }
    
    .mobile-fuel-trip-num {
        width: 22px;
        height: 22px;
        background: #f0f0f0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 10px;
        color: #666;
        margin-right: 10px;
        flex-shrink: 0;
    }
    
    .mobile-fuel-trip-num.active {
        background: #22B404;
        color: white;
    }
    
    .mobile-fuel-trip-time {
        color: #666;
        font-size: 12px;
        flex: 1;
    }
    
    .mobile-fuel-trip-stats {
        display: flex;
        gap: 12px;
        text-align: right;
    }
    
    .mobile-fuel-trip-stat {
        min-width: 45px;
    }
    
    .mobile-fuel-trip-stat-value {
        font-weight: 600;
        font-size: 13px;
        color: #000;
    }
    
    .mobile-fuel-trip-stat-unit {
        color: #999;
        font-size: 10px;
    }
    
    .mobile-fuel-totals {
        background: #fafafa;
        border-radius: 8px;
        padding: 12px;
        margin-top: 10px;
    }
    
    .mobile-fuel-totals-row {
        display: flex;
        justify-content: space-around;
        text-align: center;
    }
    
    .mobile-fuel-total-item {
        flex: 1;
    }
    
    .mobile-fuel-total-value {
        font-size: 16px;
        font-weight: 700;
        color: #000;
    }
    
    .mobile-fuel-total-label {
        font-size: 10px;
        color: #666;
        text-transform: uppercase;
        margin-top: 2px;
    }
    
    .mobile-fuel-empty {
        text-align: center;
        padding: 20px;
        color: #999;
    }
    
    .mobile-fuel-empty i {
        font-size: 32px;
        margin-bottom: 8px;
        display: block;
    }
</style>

<div class="mobile-fuel-card" id="mobile-fuel-consumption-card">
    <div class="mobile-fuel-header">
        <i class="ti ti-gas-station" style="font-size: 18px; color: #FF0049;"></i>
        <h6 class="mobile-fuel-title">{{ __('Fuel Consumption') }}</h6>
    </div>
    <div class="mobile-fuel-body">
        <div id="mobile-fuel-consumption-table">
            <div class="mobile-fuel-empty">
                <i class="ti ti-gas-station"></i>
                <p class="mb-0" style="font-size: 12px;">{{ __('Select date on map to see fuel consumption') }}</p>
            </div>
        </div>
        <div id="mobile-fuel-consumption-total" style="display: none;">
            <div class="mobile-fuel-totals">
                <div class="mobile-fuel-totals-row">
                    <div class="mobile-fuel-total-item">
                        <div class="mobile-fuel-total-value" id="mobile-fuel-total-distance">-</div>
                        <div class="mobile-fuel-total-label">{{ __('Distance') }}</div>
                    </div>
                    <div class="mobile-fuel-total-item">
                        <div class="mobile-fuel-total-value" id="mobile-fuel-total-liters">-</div>
                        <div class="mobile-fuel-total-label">{{ __('Fuel') }}</div>
                    </div>
                    <div class="mobile-fuel-total-item">
                        <div class="mobile-fuel-total-value" id="mobile-fuel-total-trips">-</div>
                        <div class="mobile-fuel-total-label">{{ __('Trips') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const fuelConsumption = {{ $vehicle->fuel_consumption ?? 0 }};
    const unitKm = '{{ __("km") }}';
    const unitL = '{{ __("l") }}';
    
    window.addEventListener('mobileTripsDataLoaded', function(e) {
        updateMobileFuelConsumption(e.detail.trips, e.detail.date);
    });
    
    function updateMobileFuelConsumption(trips, date) {
        const tableEl = document.getElementById('mobile-fuel-consumption-table');
        const totalEl = document.getElementById('mobile-fuel-consumption-total');
        
        if (!trips || trips.length === 0) {
            tableEl.innerHTML = `
                <div class="mobile-fuel-empty">
                    <i class="ti ti-gas-station"></i>
                    <p class="mb-0" style="font-size: 12px;">{{ __('No trips for this day') }}</p>
                </div>
            `;
            totalEl.style.display = 'none';
            return;
        }
        
        let totalDistance = 0;
        let totalFuel = 0;
        let validTripsCount = 0;
        
        let html = '<div class="mobile-fuel-trip-list">';
        
        trips.forEach((trip, index) => {
            const startTime = new Date(trip.started_at).toLocaleTimeString('uk-UA', {hour: '2-digit', minute: '2-digit'});
            const endTime = trip.ended_at 
                ? new Date(trip.ended_at).toLocaleTimeString('uk-UA', {hour: '2-digit', minute: '2-digit'})
                : '...';
            
            const distance = parseFloat(trip.total_distance_km) || 0;
            const fuel = fuelConsumption > 0 ? (distance * fuelConsumption / 100) : 0;
            
            if (distance > 0) {
                validTripsCount++;
            }
            
            totalDistance += distance;
            totalFuel += fuel;
            
            const numClass = trip.is_active ? 'mobile-fuel-trip-num active' : 'mobile-fuel-trip-num';
            const emptyClass = distance === 0 ? ' empty' : '';
            
            html += `
                <div class="mobile-fuel-trip-item${emptyClass}">
                    <div class="${numClass}">${index + 1}</div>
                    <div class="mobile-fuel-trip-time">${startTime} - ${endTime}</div>
                    <div class="mobile-fuel-trip-stats">
                        <div class="mobile-fuel-trip-stat">
                            <span class="mobile-fuel-trip-stat-value">${distance.toFixed(1)}</span>
                            <span class="mobile-fuel-trip-stat-unit">${unitKm}</span>
                        </div>
                        <div class="mobile-fuel-trip-stat">
                            <span class="mobile-fuel-trip-stat-value">${fuel.toFixed(1)}</span>
                            <span class="mobile-fuel-trip-stat-unit">${unitL}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        
        tableEl.innerHTML = html;
        
        document.getElementById('mobile-fuel-total-distance').textContent = totalDistance.toFixed(1) + ' ' + unitKm;
        document.getElementById('mobile-fuel-total-liters').textContent = totalFuel.toFixed(1) + ' ' + unitL;
        
        let tripsText = trips.length.toString();
        if (validTripsCount < trips.length) {
            tripsText += ` (${validTripsCount} {{ __('with data') }})`;
        }
        document.getElementById('mobile-fuel-total-trips').textContent = tripsText;
        totalEl.style.display = 'block';
    }
})();
</script>
