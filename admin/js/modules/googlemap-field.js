/**
 * GiftFlow Google Map Field Class
 *
 * A reusable class for managing Google Maps location picker fields.
 *
 * @package GiftFlow
 * @since 1.0.0
 */

const $ = jQuery.noConflict();

class GiftFlowGoogleMapField {
	/**
	 * Default configuration options
	 *
	 * @type {Object}
	 */
	static defaults = {
		lat: 40.7128,
		lng: -74.006,
		zoom: 2,
		apiKey: '',
		i18n: {
			geocodeError: 'Geocode was not successful for the following reason: ',
		},
	};

	/**
	 * Constructor
	 *
	 * @param {string|HTMLElement} selector - The map field container selector or element.
	 * @param {Object} options - Configuration options.
	 */
	constructor(selector, options = {}) {
		this.$mapField = typeof selector === 'string' ? $(selector) : $(selector);

		if (!this.$mapField.length) {
			console.warn('GiftFlowGoogleMapField: Map field element not found.');
			return;
		}

		this.options = { ...GiftFlowGoogleMapField.defaults, ...options };
		this.map = null;
		this.marker = null;
		this.geocoder = null;

		this.cacheElements();
		this.init();
	}

	/**
	 * Cache DOM elements
	 */
	cacheElements() {
		this.$input = this.$mapField.find('input[type=hidden]');
		this.$addressInput = this.$mapField.find('.giftflow-googlemap-address-input');
		this.$searchButton = this.$mapField.find('.giftflow-googlemap-search');
		this.$mapContainer = this.$mapField.find('.giftflow-googlemap-container');
		this.$latDisplay = this.$mapField.find('.giftflow-googlemap-lat');
		this.$lngDisplay = this.$mapField.find('.giftflow-googlemap-lng');
	}

	/**
	 * Initialize the map field
	 */
	init() {
		if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
			this.loadGoogleMapsAPI();
		} else {
			this.initMap();
		}
	}

	/**
	 * Load Google Maps API dynamically
	 */
	loadGoogleMapsAPI() {
		// Check if script is already being loaded
		if (window.giftflowGoogleMapsLoading) {
			// Wait for existing load to complete
			window.giftflowGoogleMapsCallbacks = window.giftflowGoogleMapsCallbacks || [];
			window.giftflowGoogleMapsCallbacks.push(() => this.initMap());
			return;
		}

		window.giftflowGoogleMapsLoading = true;
		window.giftflowGoogleMapsCallbacks = window.giftflowGoogleMapsCallbacks || [];
		window.giftflowGoogleMapsCallbacks.push(() => this.initMap());

		// Define global callback
		window.giftflowGoogleMapsCallback = () => {
			window.giftflowGoogleMapsLoading = false;
			window.giftflowGoogleMapsCallbacks.forEach((callback) => callback());
			window.giftflowGoogleMapsCallbacks = [];
		};

		const script = document.createElement('script');
		script.src = `https://maps.googleapis.com/maps/api/js?key=${this.options.apiKey}&callback=giftflowGoogleMapsCallback`;
		script.async = true;
		script.defer = true;
		document.head.appendChild(script);
	}

	/**
	 * Initialize the Google Map
	 */
	initMap() {
		const { lat, lng } = this.options;
		const hasCoordinates = lat && lng && lat !== GiftFlowGoogleMapField.defaults.lat;
		const zoom = hasCoordinates ? 15 : this.options.zoom;
		const center = { lat: parseFloat(lat), lng: parseFloat(lng) };

		// Create map instance
		this.map = new google.maps.Map(this.$mapContainer[0], {
			center,
			zoom,
			mapTypeControl: true,
			streetViewControl: true,
			fullscreenControl: true,
		});

		// Create geocoder
		this.geocoder = new google.maps.Geocoder();

		// Create draggable marker
		this.marker = new google.maps.Marker({
			map: this.map,
			draggable: true,
			position: center,
		});

		this.bindEvents();

		// Reverse geocode initial position if coordinates exist
		if (hasCoordinates) {
			this.reverseGeocode(center);
		}
	}

	/**
	 * Bind event listeners
	 */
	bindEvents() {
		// Marker drag event
		this.marker.addListener('dragend', () => {
			this.updateLocationFromMarker();
		});

		// Map click event
		this.map.addListener('click', (event) => {
			this.marker.setPosition(event.latLng);
			this.updateLocationFromMarker();
		});

		// Search button click
		this.$searchButton.on('click', (e) => {
			e.preventDefault();
			this.searchAddress();
		});

		// Enter key in address field
		this.$addressInput.on('keypress', (e) => {
			if (e.which === 13) {
				e.preventDefault();
				this.searchAddress();
			}
		});
	}

	/**
	 * Update location from marker position
	 */
	updateLocationFromMarker() {
		const position = this.marker.getPosition();
		this.reverseGeocode({
			lat: position.lat(),
			lng: position.lng(),
		});
	}

	/**
	 * Reverse geocode to get address from coordinates
	 *
	 * @param {Object} position - Position object with lat and lng.
	 */
	reverseGeocode(position) {
		const latLng = position.lat && position.lng ? position : { lat: position.lat(), lng: position.lng() };

		this.geocoder.geocode({ location: latLng }, (results, status) => {
			if (status === 'OK' && results[0]) {
				this.$addressInput.val(results[0].formatted_address);
				this.updateLocationData(latLng.lat, latLng.lng, results[0].formatted_address);
			}
		});
	}

	/**
	 * Update location data in the hidden input and display elements
	 *
	 * @param {number} lat - Latitude.
	 * @param {number} lng - Longitude.
	 * @param {string} address - Formatted address.
	 */
	updateLocationData(lat, lng, address) {
		// Update display elements
		this.$latDisplay.text(lat);
		this.$lngDisplay.text(lng);

		// Update hidden input with JSON data
		const locationData = { lat, lng, address };
		this.$input.val(JSON.stringify(locationData));

		// Trigger change event for external listeners
		this.$input.trigger('change', [locationData]);
	}

	/**
	 * Search for an address and update the map
	 */
	searchAddress() {
		const address = this.$addressInput.val().trim();

		if (!address) {
			return;
		}

		this.geocoder.geocode({ address }, (results, status) => {
			if (status === 'OK') {
				const position = results[0].geometry.location;
				this.map.setCenter(position);
				this.map.setZoom(15);
				this.marker.setPosition(position);
				this.updateLocationData(position.lat(), position.lng(), results[0].formatted_address);
			} else {
				alert(this.options.i18n.geocodeError + status);
			}
		});
	}

	/**
	 * Set map location programmatically
	 *
	 * @param {number} lat - Latitude.
	 * @param {number} lng - Longitude.
	 * @param {number} zoom - Optional zoom level.
	 */
	setLocation(lat, lng, zoom = 15) {
		const position = { lat: parseFloat(lat), lng: parseFloat(lng) };

		if (this.map && this.marker) {
			this.map.setCenter(position);
			this.map.setZoom(zoom);
			this.marker.setPosition(position);
			this.reverseGeocode(position);
		}
	}

	/**
	 * Get current location data
	 *
	 * @returns {Object|null} Location data or null if not set.
	 */
	getLocation() {
		const value = this.$input.val();
		if (value) {
			try {
				return JSON.parse(value);
			} catch (e) {
				return null;
			}
		}
		return null;
	}

	/**
	 * Clear the map location
	 */
	clear() {
		this.$addressInput.val('');
		this.$latDisplay.text('');
		this.$lngDisplay.text('');
		this.$input.val('');

		// Reset to defaults
		const center = {
			lat: GiftFlowGoogleMapField.defaults.lat,
			lng: GiftFlowGoogleMapField.defaults.lng,
		};

		if (this.map && this.marker) {
			this.map.setCenter(center);
			this.map.setZoom(GiftFlowGoogleMapField.defaults.zoom);
			this.marker.setPosition(center);
		}
	}

	/**
	 * Destroy the instance and clean up
	 */
	destroy() {
		// Remove event listeners
		this.$searchButton.off('click');
		this.$addressInput.off('keypress');

		if (this.marker) {
			google.maps.event.clearInstanceListeners(this.marker);
		}

		if (this.map) {
			google.maps.event.clearInstanceListeners(this.map);
		}

		this.map = null;
		this.marker = null;
		this.geocoder = null;
	}
}

export default GiftFlowGoogleMapField;
