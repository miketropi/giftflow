/**
 * GiftFlow Campaign Images Gallery Class
 *
 * A reusable class for managing campaign image galleries with thumbnail navigation.
 *
 * @package GiftFlow
 * @since 1.0.0
 */

class GiftFlowImageGallery {
	/**
	 * Default configuration options
	 *
	 * @type {Object}
	 */
	static defaults = {
		thumbnailSelector: '.giftflow-campaign-single-images-gallery-thumbnail',
		mainImageSelector: '.giftflow-campaign-single-images-main',
		expandButtonSelector: '.giftflow-campaign-single-images-gallery-expand',
		hiddenClass: 'giftflow-thumbnail-hidden',
		activeClass: 'active',
		expandedClass: 'expanded',
		removeExpandButton: true,
		autoPlay: false,
		autoPlayInterval: 5000,
		i18n: {
			expandLabel: 'Show more images',
			collapseLabel: 'Show fewer images',
		},
	};

	/**
	 * Constructor
	 *
	 * @param {string|HTMLElement} selector - The gallery container selector or element.
	 * @param {Object} options - Configuration options.
	 */
	constructor(selector, options = {}) {
		this.container = typeof selector === 'string' ? document.querySelector(selector) : selector;

		if (!this.container) {
			console.warn('GiftFlowImageGallery: Gallery element not found.');
			return;
		}

		this.options = { ...GiftFlowImageGallery.defaults, ...options };
		this.currentIndex = 0;
		this.autoPlayTimer = null;
		this.isExpanded = false;

		this.cacheElements();
		this.bindEvents();
		this.initState();

		if (this.options.autoPlay) {
			this.startAutoPlay();
		}
	}

	/**
	 * Cache DOM elements
	 */
	cacheElements() {
		this.thumbnails = this.container.querySelectorAll(this.options.thumbnailSelector);
		this.mainImage = this.container.querySelector(this.options.mainImageSelector);
		this.expandButton = this.container.querySelector(this.options.expandButtonSelector);
	}

	/**
	 * Bind event listeners
	 */
	bindEvents() {
		// Thumbnail clicks
		this.thumbnails.forEach((thumbnail, index) => {
			thumbnail.addEventListener('click', () => {
				this.goTo(index);
			});

			// Keyboard navigation on thumbnails
			thumbnail.addEventListener('keydown', (e) => {
				this.handleThumbnailKeydown(e, index);
			});
		});

		// Expand button
		if (this.expandButton) {
			// Store original labels
			this.expandButton.dataset.expandLabel =
				this.expandButton.getAttribute('aria-label') || this.options.i18n.expandLabel;
			this.expandButton.dataset.collapseLabel = this.options.i18n.collapseLabel;

			this.expandButton.addEventListener('click', (e) => {
				e.preventDefault();
				this.toggleExpand();
			});

			this.expandButton.addEventListener('keydown', (e) => {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					this.toggleExpand();
				}
			});
		}

		// Main image click (for lightbox integration)
		if (this.mainImage) {
			this.mainImage.addEventListener('click', () => {
				this.dispatchEvent('mainImageClick', {
					index: this.currentIndex,
					image: this.getCurrentImageData(),
				});
			});
		}
	}

	/**
	 * Initialize gallery state
	 */
	initState() {
		// Find initially active thumbnail
		const activeThumb = this.container.querySelector(
			`${this.options.thumbnailSelector}.${this.options.activeClass}`
		);

		if (activeThumb) {
			this.currentIndex = Array.from(this.thumbnails).indexOf(activeThumb);
		}

		// Set up ARIA attributes
		this.thumbnails.forEach((thumb, index) => {
			thumb.setAttribute('role', 'button');
			thumb.setAttribute('tabindex', index === this.currentIndex ? '0' : '-1');
			thumb.setAttribute('aria-selected', index === this.currentIndex ? 'true' : 'false');
		});
	}

	/**
	 * Handle keydown on thumbnails
	 *
	 * @param {KeyboardEvent} e - Keyboard event.
	 * @param {number} index - Current thumbnail index.
	 */
	handleThumbnailKeydown(e, index) {
		let newIndex = index;

		switch (e.key) {
			case 'ArrowRight':
			case 'ArrowDown':
				e.preventDefault();
				newIndex = (index + 1) % this.thumbnails.length;
				break;
			case 'ArrowLeft':
			case 'ArrowUp':
				e.preventDefault();
				newIndex = (index - 1 + this.thumbnails.length) % this.thumbnails.length;
				break;
			case 'Home':
				e.preventDefault();
				newIndex = 0;
				break;
			case 'End':
				e.preventDefault();
				newIndex = this.thumbnails.length - 1;
				break;
			case 'Enter':
			case ' ':
				e.preventDefault();
				this.goTo(index);
				return;
			default:
				return;
		}

		this.goTo(newIndex);
		this.thumbnails[newIndex].focus();
	}

	/**
	 * Go to a specific image by index
	 *
	 * @param {number} index - The image index to display.
	 * @returns {GiftFlowImageGallery} Returns this for chaining.
	 */
	goTo(index) {
		if (index < 0 || index >= this.thumbnails.length) {
			return this;
		}

		const thumbnail = this.thumbnails[index];
		const previousIndex = this.currentIndex;

		// Update active states
		this.thumbnails.forEach((thumb, i) => {
			thumb.classList.toggle(this.options.activeClass, i === index);
			thumb.setAttribute('aria-selected', i === index ? 'true' : 'false');
			thumb.setAttribute('tabindex', i === index ? '0' : '-1');
		});

		// Update main image
		if (this.mainImage && thumbnail) {
			this.mainImage.src = thumbnail.dataset.imageUrl || thumbnail.src;
			this.mainImage.alt = thumbnail.dataset.imageAlt || thumbnail.alt || '';

			if (thumbnail.dataset.imageFullUrl) {
				this.mainImage.dataset.fullUrl = thumbnail.dataset.imageFullUrl;
			}
			if (thumbnail.dataset.imageId) {
				this.mainImage.dataset.imageId = thumbnail.dataset.imageId;
			}
		}

		this.currentIndex = index;

		this.dispatchEvent('change', {
			index,
			previousIndex,
			image: this.getCurrentImageData(),
		});

		return this;
	}

	/**
	 * Go to next image
	 *
	 * @returns {GiftFlowImageGallery} Returns this for chaining.
	 */
	next() {
		const nextIndex = (this.currentIndex + 1) % this.thumbnails.length;
		return this.goTo(nextIndex);
	}

	/**
	 * Go to previous image
	 *
	 * @returns {GiftFlowImageGallery} Returns this for chaining.
	 */
	prev() {
		const prevIndex = (this.currentIndex - 1 + this.thumbnails.length) % this.thumbnails.length;
		return this.goTo(prevIndex);
	}

	/**
	 * Toggle expand/collapse of hidden thumbnails
	 *
	 * @returns {GiftFlowImageGallery} Returns this for chaining.
	 */
	toggleExpand() {
		if (this.isExpanded) {
			this.collapse();
		} else {
			this.expand();
		}
		return this;
	}

	/**
	 * Expand to show all thumbnails
	 *
	 * @returns {GiftFlowImageGallery} Returns this for chaining.
	 */
	expand() {
		const hiddenThumbnails = this.container.querySelectorAll(`.${this.options.hiddenClass}`);

		hiddenThumbnails.forEach((thumb) => {
			thumb.classList.remove(this.options.hiddenClass);
		});

		this.isExpanded = true;

		if (this.expandButton) {
			this.expandButton.classList.add(this.options.expandedClass);
			this.expandButton.setAttribute('aria-label', this.expandButton.dataset.collapseLabel);
			this.expandButton.setAttribute('aria-expanded', 'true');

			if (this.options.removeExpandButton) {
				this.expandButton.remove();
				this.expandButton = null;
			}
		}

		this.dispatchEvent('expanded');

		return this;
	}

	/**
	 * Collapse to hide extra thumbnails
	 *
	 * @returns {GiftFlowImageGallery} Returns this for chaining.
	 */
	collapse() {
		// This requires knowing which thumbnails should be hidden
		// Usually handled by CSS or initial state
		this.isExpanded = false;

		if (this.expandButton) {
			this.expandButton.classList.remove(this.options.expandedClass);
			this.expandButton.setAttribute('aria-label', this.expandButton.dataset.expandLabel);
			this.expandButton.setAttribute('aria-expanded', 'false');
		}

		this.dispatchEvent('collapsed');

		return this;
	}

	/**
	 * Get current image data
	 *
	 * @returns {Object} Current image data.
	 */
	getCurrentImageData() {
		const thumbnail = this.thumbnails[this.currentIndex];

		if (!thumbnail) {
			return null;
		}

		return {
			index: this.currentIndex,
			url: thumbnail.dataset.imageUrl || thumbnail.src,
			fullUrl: thumbnail.dataset.imageFullUrl || thumbnail.dataset.imageUrl || thumbnail.src,
			alt: thumbnail.dataset.imageAlt || thumbnail.alt || '',
			id: thumbnail.dataset.imageId || null,
		};
	}

	/**
	 * Get all images data
	 *
	 * @returns {Object[]} Array of image data objects.
	 */
	getAllImages() {
		return Array.from(this.thumbnails).map((thumbnail, index) => ({
			index,
			url: thumbnail.dataset.imageUrl || thumbnail.src,
			fullUrl: thumbnail.dataset.imageFullUrl || thumbnail.dataset.imageUrl || thumbnail.src,
			alt: thumbnail.dataset.imageAlt || thumbnail.alt || '',
			id: thumbnail.dataset.imageId || null,
		}));
	}

	/**
	 * Get total image count
	 *
	 * @returns {number} Total number of images.
	 */
	getCount() {
		return this.thumbnails.length;
	}

	/**
	 * Get current index
	 *
	 * @returns {number} Current image index.
	 */
	getCurrentIndex() {
		return this.currentIndex;
	}

	/**
	 * Start auto-play slideshow
	 *
	 * @param {number} interval - Optional interval in milliseconds.
	 * @returns {GiftFlowImageGallery} Returns this for chaining.
	 */
	startAutoPlay(interval = null) {
		this.stopAutoPlay();

		const ms = interval || this.options.autoPlayInterval;

		this.autoPlayTimer = setInterval(() => {
			this.next();
		}, ms);

		this.dispatchEvent('autoPlayStarted', { interval: ms });

		return this;
	}

	/**
	 * Stop auto-play slideshow
	 *
	 * @returns {GiftFlowImageGallery} Returns this for chaining.
	 */
	stopAutoPlay() {
		if (this.autoPlayTimer) {
			clearInterval(this.autoPlayTimer);
			this.autoPlayTimer = null;
			this.dispatchEvent('autoPlayStopped');
		}
		return this;
	}

	/**
	 * Dispatch a custom event
	 *
	 * @param {string} eventName - Event name (without prefix).
	 * @param {Object} detail - Event detail data.
	 */
	dispatchEvent(eventName, detail = {}) {
		this.container.dispatchEvent(
			new CustomEvent(`giftflow:gallery:${eventName}`, {
				detail: { ...detail, instance: this },
				bubbles: true,
			})
		);
	}

	/**
	 * Destroy the instance and clean up
	 */
	destroy() {
		this.stopAutoPlay();

		// Clone and replace to remove event listeners
		this.thumbnails.forEach((thumb) => {
			thumb.replaceWith(thumb.cloneNode(true));
		});

		if (this.expandButton) {
			this.expandButton.replaceWith(this.expandButton.cloneNode(true));
		}

		if (this.mainImage) {
			this.mainImage.replaceWith(this.mainImage.cloneNode(true));
		}

		this.container = null;
		this.thumbnails = null;
		this.mainImage = null;
		this.expandButton = null;
	}

	/**
	 * Initialize all galleries matching a selector
	 *
	 * @param {string} selector - Selector for gallery containers.
	 * @param {Object} options - Configuration options.
	 * @returns {GiftFlowImageGallery[]} Array of gallery instances.
	 */
	static initAll(selector, options = {}) {
		const instances = [];
		document.querySelectorAll(selector).forEach((element) => {
			instances.push(new GiftFlowImageGallery(element, options));
		});
		return instances;
	}
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
	window.giftflowImageGalleries = GiftFlowImageGallery.initAll('.giftflow-campaign-single-images-gallery');
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
	module.exports = GiftFlowImageGallery;
}

// Make available globally
window.GiftFlowImageGallery = GiftFlowImageGallery;
