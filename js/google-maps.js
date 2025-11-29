/**
 * Google Maps Integration for PharmaVault
 * Handles pharmacy location display and distance calculations
 */

let map;
let markers = [];
let userLocationMarker = null;
let infoWindow;
let userLocation = null;

/**
 * Initialize the map
 * @param {string} containerId - ID of the map container
 * @param {object} options - Map options {center: {lat, lng}, zoom}
 */
function initMap(containerId, options = {}) {
    const defaultOptions = {
        center: options.center || { lat: 5.6037, lng: -0.1870 }, // Accra, Ghana
        zoom: options.zoom || 12,
        mapTypeControl: true,
        streetViewControl: true,
        fullscreenControl: true,
        zoomControl: true,
        styles: [
            {
                featureType: "poi.business",
                stylers: [{ visibility: "off" }]
            }
        ]
    };

    map = new google.maps.Map(
        document.getElementById(containerId),
        defaultOptions
    );

    infoWindow = new google.maps.InfoWindow();

    return map;
}

/**
 * Add a pharmacy marker to the map
 * @param {object} pharmacy - Pharmacy data {id, name, lat, lng, address, phone, email}
 */
function addPharmacyMarker(pharmacy) {
    if (!pharmacy.lat || !pharmacy.lng) {
        console.warn(`Pharmacy ${pharmacy.name} has no coordinates`);
        return null;
    }

    const position = {
        lat: parseFloat(pharmacy.lat),
        lng: parseFloat(pharmacy.lng)
    };

    // Create custom marker icon
    const icon = {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 12,
        fillColor: '#667eea',
        fillOpacity: 1,
        strokeColor: '#ffffff',
        strokeWeight: 3,
    };

    const marker = new google.maps.Marker({
        position: position,
        map: map,
        title: pharmacy.name,
        icon: icon,
        animation: google.maps.Animation.DROP
    });

    // Calculate distance if user location is available
    let distanceText = '';
    if (userLocation) {
        const distance = calculateDistance(
            userLocation.lat,
            userLocation.lng,
            position.lat,
            position.lng
        );
        distanceText = `<div style="margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0;">
            <strong><i class="fas fa-route"></i> Distance:</strong> ${distance.toFixed(2)} km away
        </div>`;
    }

    // Create info window content
    const contentString = `
        <div style="padding: 15px; max-width: 300px; font-family: 'Segoe UI', sans-serif;">
            <h4 style="margin: 0 0 10px 0; color: #667eea; font-size: 1.2rem;">
                <i class="fas fa-hospital"></i> ${pharmacy.name}
            </h4>
            <div style="color: #475569; line-height: 1.8;">
                <div style="margin-bottom: 8px;">
                    <i class="fas fa-map-marker-alt" style="color: #667eea; width: 20px;"></i>
                    <strong>Address:</strong><br/>
                    <span style="margin-left: 24px;">${pharmacy.address || 'Not provided'}</span>
                </div>
                <div style="margin-bottom: 8px;">
                    <i class="fas fa-phone" style="color: #667eea; width: 20px;"></i>
                    <strong>Phone:</strong> ${pharmacy.phone || 'N/A'}
                </div>
                <div style="margin-bottom: 8px;">
                    <i class="fas fa-envelope" style="color: #667eea; width: 20px;"></i>
                    <strong>Email:</strong> ${pharmacy.email || 'N/A'}
                </div>
                ${distanceText}
            </div>
            <a href="pharmacy_view.php?id=${pharmacy.id}"
               style="display: inline-block; margin-top: 15px; padding: 10px 20px;
                      background: linear-gradient(135deg, #667eea, #764ba2);
                      color: white; text-decoration: none; border-radius: 8px;
                      font-weight: 600; text-align: center;">
                <i class="fas fa-store"></i> View Products
            </a>
        </div>
    `;

    marker.addListener('click', () => {
        infoWindow.setContent(contentString);
        infoWindow.open(map, marker);
    });

    markers.push(marker);
    return marker;
}

/**
 * Add user location marker
 * @param {number} lat - Latitude
 * @param {number} lng - Longitude
 */
function addUserLocationMarker(lat, lng) {
    // Remove existing user location marker
    if (userLocationMarker) {
        userLocationMarker.setMap(null);
    }

    userLocation = { lat, lng };

    // Create user location marker with different style
    const icon = {
        path: google.maps.SymbolPath.CIRCLE,
        scale: 10,
        fillColor: '#10b981',
        fillOpacity: 1,
        strokeColor: '#ffffff',
        strokeWeight: 3,
    };

    userLocationMarker = new google.maps.Marker({
        position: { lat, lng },
        map: map,
        title: 'Your Location',
        icon: icon,
        zIndex: 1000
    });

    const contentString = `
        <div style="padding: 10px; font-family: 'Segoe UI', sans-serif;">
            <h4 style="margin: 0 0 5px 0; color: #10b981;">
                <i class="fas fa-map-marker-alt"></i> Your Location
            </h4>
            <p style="margin: 0; color: #64748b; font-size: 0.9rem;">You are here</p>
        </div>
    `;

    userLocationMarker.addListener('click', () => {
        infoWindow.setContent(contentString);
        infoWindow.open(map, userLocationMarker);
    });

    // Recenter map on user location
    map.setCenter({ lat, lng });

    return userLocationMarker;
}

/**
 * Get user's current location using browser geolocation
 * @param {function} callback - Callback function(lat, lng)
 * @param {function} errorCallback - Error callback function
 */
function getUserLocation(callback, errorCallback) {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                callback(lat, lng);
            },
            (error) => {
                console.error('Geolocation error:', error);
                if (errorCallback) {
                    errorCallback(error);
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        const error = new Error('Geolocation is not supported by this browser.');
        console.error(error);
        if (errorCallback) {
            errorCallback(error);
        }
    }
}

/**
 * Calculate distance between two coordinates using Haversine formula
 * @param {number} lat1 - Latitude of point 1
 * @param {number} lng1 - Longitude of point 1
 * @param {number} lat2 - Latitude of point 2
 * @param {number} lng2 - Longitude of point 2
 * @returns {number} Distance in kilometers
 */
function calculateDistance(lat1, lng1, lat2, lng2) {
    const R = 6371; // Earth's radius in kilometers
    const dLat = toRad(lat2 - lat1);
    const dLng = toRad(lng2 - lng1);

    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
        Math.sin(dLng / 2) * Math.sin(dLng / 2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const distance = R * c;

    return distance;
}

/**
 * Convert degrees to radians
 */
function toRad(degrees) {
    return degrees * (Math.PI / 180);
}

/**
 * Clear all markers from the map
 */
function clearMarkers() {
    markers.forEach(marker => marker.setMap(null));
    markers = [];
}

/**
 * Fit map bounds to show all markers
 */
function fitMapToMarkers() {
    if (markers.length === 0) return;

    const bounds = new google.maps.LatLngBounds();
    markers.forEach(marker => {
        bounds.extend(marker.getPosition());
    });

    if (userLocationMarker) {
        bounds.extend(userLocationMarker.getPosition());
    }

    map.fitBounds(bounds);

    // Ensure minimum zoom level
    const listener = google.maps.event.addListener(map, "idle", function() {
        if (map.getZoom() > 15) map.setZoom(15);
        google.maps.event.removeListener(listener);
    });
}

/**
 * Add distance information to pharmacy cards
 * @param {number} pharmacyId - Pharmacy ID
 * @param {number} lat - Pharmacy latitude
 * @param {number} lng - Pharmacy longitude
 */
function updatePharmacyDistance(pharmacyId, lat, lng) {
    if (!userLocation) return;

    const distance = calculateDistance(
        userLocation.lat,
        userLocation.lng,
        lat,
        lng
    );

    // Find the pharmacy card and add distance info
    const pharmacyCard = document.querySelector(`[data-pharmacy-id="${pharmacyId}"]`);
    if (pharmacyCard) {
        const distanceElement = pharmacyCard.querySelector('.pharmacy-distance');
        if (distanceElement) {
            distanceElement.innerHTML = `
                <i class="fas fa-route"></i> ${distance.toFixed(2)} km away
            `;
            distanceElement.style.display = 'block';
        }
    }
}

/**
 * Show loading state for map
 */
function showMapLoading(containerId) {
    const container = document.getElementById(containerId);
    if (container) {
        container.innerHTML = `
            <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: #667eea;">
                <div style="text-align: center;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <div>Loading map...</div>
                </div>
            </div>
        `;
    }
}

/**
 * Show notification toast
 */
function showNotification(message, type = 'info') {
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        info: '#667eea',
        warning: '#f59e0b'
    };

    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle',
        warning: 'fa-exclamation-triangle'
    };

    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 1rem;
        z-index: 99999;
        animation: slideIn 0.3s ease;
        border-left: 4px solid ${colors[type]};
    `;

    notification.innerHTML = `
        <i class="fas ${icons[type]}" style="color: ${colors[type]}; font-size: 1.5rem;"></i>
        <span style="color: #1e293b;">${message}</span>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
`;
document.head.appendChild(style);
