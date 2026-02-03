/**
 * GiftFlow Gallery Field Class
 *
 * A reusable class for managing gallery/media fields with WordPress media library integration.
 *
 * @package GiftFlow
 * @since 1.0.0
 */

const $ = jQuery.noConflict();

class GiftFlowGalleryField {
	/**
	 * Default configuration options
	 *
	 * @type {Object}
	 */
	static defaults = {
		maxImages: 0, // 0 = unlimited
		imageSize: 'thumbnail',
		ajaxAction: 'giftflow_get_gallery_images',
		nonce: '',
		sortable: true,
		i18n: {
			frameTitle: 'Select Images',
			frameButton: 'Add to Gallery',
			removeImage: 'Remove Image',
			maxImagesReached: 'Maximum number of images reached.',
		},
	};

	/**
	 * Constructor
	 *
	 * @param {string|HTMLElement} selector - The gallery field container selector or element.
	 * @param {Object} options - Configuration options.
	 */
	constructor(selector, options = {}) {
		this.$gallery = typeof selector === 'string' ? $(selector) : $(selector);

		if (!this.$gallery.length) {
			console.warn('GiftFlowGalleryField: Gallery element not found.');
			return;
		}

		this.options = { ...GiftFlowGalleryField.defaults, ...options };
		this.mediaFrame = null;

		this.cacheElements();
		this.bindEvents();
		this.initSortable();
		this.updateButtonStates();
	}

	/**
	 * Cache DOM elements
	 */
	cacheElements() {
		this.$input = this.$gallery.find('input[type=hidden]');
		this.$preview = this.$gallery.find('.giftflow-gallery-preview');
		this.$addButton = this.$gallery.find('.giftflow-gallery-add');
		this.$removeAllButton = this.$gallery.find('.giftflow-gallery-remove-all');
	}

	/**
	 * Bind event listeners
	 */
	bindEvents() {
		// Add images button
		this.$addButton.on('click.giftflowGallery', (e) => {
			e.preventDefault();
			this.openMediaFrame();
		});

		// Remove single image (delegated)
		this.$gallery.on('click.giftflowGallery', '.giftflow-gallery-remove', (e) => {
			e.preventDefault();
			const $image = $(e.currentTarget).parent();
			const imageId = $image.data('id');
			this.removeImage(imageId);
		});

		// Remove all images
		this.$removeAllButton.on('click.giftflowGallery', (e) => {
			e.preventDefault();
			this.clearAll();
		});
	}

	/**
	 * Initialize sortable functionality
	 */
	initSortable() {
		if (!this.options.sortable || typeof $.fn.sortable !== 'function') {
			return;
		}

		this.$preview.sortable({
			items: '.giftflow-gallery-image',
			cursor: 'move',
			placeholder: 'giftflow-gallery-placeholder',
			tolerance: 'pointer',
			update: () => {
				this.updateInputFromPreview();
				this.$gallery.trigger('giftflow:gallery:reordered', [this.getImageIds(), this]);
			},
		});
	}

	/**
	 * Open WordPress media frame
	 */
	openMediaFrame() {
		// Check if max images already reached
		if (this.isMaxReached()) {
			alert(this.options.i18n.maxImagesReached);
			return;
		}

		// Reuse existing frame if available
		if (this.mediaFrame) {
			this.mediaFrame.open();
			return;
		}

		// Create the media frame
		this.mediaFrame = wp.media({
			title: this.options.i18n.frameTitle,
			button: {
				text: this.options.i18n.frameButton,
			},
			multiple: true,
			library: {
				type: 'image',
			},
		});

		// Handle selection
		this.mediaFrame.on('select', () => {
			this.handleMediaSelection();
		});

		this.mediaFrame.open();
	}

	/**
	 * Handle media frame selection
	 */
	handleMediaSelection() {
		const selection = this.mediaFrame.state().get('selection');
		const currentIds = this.getImageIds();
		const newIds = [];

		selection.each((attachment) => {
			const data = attachment.toJSON();
			const idStr = data.id.toString();

			// Check max limit
			if (this.options.maxImages > 0 && currentIds.length + newIds.length >= this.options.maxImages) {
				return;
			}

			// Avoid duplicates
			if (!currentIds.includes(idStr) && !newIds.includes(idStr)) {
				newIds.push(idStr);
			}
		});

		if (newIds.length > 0) {
			const allIds = [...currentIds, ...newIds];
			this.setImageIds(allIds);
			this.refreshPreview();

			this.$gallery.trigger('giftflow:gallery:added', [newIds, this]);
		}
	}

	/**
	 * Remove a single image by ID
	 *
	 * @param {string|number} imageId - The image ID to remove.
	 * @returns {GiftFlowGalleryField} Returns this for chaining.
	 */
	removeImage(imageId) {
		const idStr = imageId.toString();
		const currentIds = this.getImageIds().filter((id) => id !== idStr);

		this.setImageIds(currentIds);

		// Remove from preview
		this.$preview.find(`.giftflow-gallery-image[data-id="${imageId}"]`).remove();

		this.updateButtonStates();

		this.$gallery.trigger('giftflow:gallery:removed', [idStr, this]);

		return this;
	}

	/**
	 * Clear all images
	 *
	 * @returns {GiftFlowGalleryField} Returns this for chaining.
	 */
	clearAll() {
		const removedIds = this.getImageIds();

		this.$input.val('');
		this.$preview.empty();
		this.updateButtonStates();

		this.$gallery.trigger('giftflow:gallery:cleared', [removedIds, this]);

		return this;
	}

	/**
	 * Refresh the gallery preview via AJAX
	 *
	 * @returns {GiftFlowGalleryField} Returns this for chaining.
	 */
	refreshPreview() {
		const currentIds = this.getImageIds();

		if (currentIds.length === 0) {
			this.$preview.empty();
			this.updateButtonStates();
			return this;
		}

		$.ajax({
			url: window.ajaxurl,
			type: 'POST',
			data: {
				action: this.options.ajaxAction,
				ids: currentIds,
				size: this.options.imageSize,
				nonce: this.options.nonce,
			},
			success: (response) => {
				if (response.success && response.data) {
					this.renderPreview(response.data);
				}
			},
			error: (xhr, status, error) => {
				console.error('GiftFlowGalleryField: Failed to load images', error);
			},
		});

		return this;
	}

	/**
	 * Render the preview images
	 *
	 * @param {Object} images - Object of image data keyed by ID.
	 */
	renderPreview(images) {
		this.$preview.empty();

		// Maintain order from input
		const orderedIds = this.getImageIds();

		orderedIds.forEach((id) => {
			const image = images[id];
			if (image) {
				const $imageEl = this.createImageElement(id, image);
				this.$preview.append($imageEl);
			}
		});

		this.updateButtonStates();
		this.$gallery.trigger('giftflow:gallery:rendered', [orderedIds, this]);
	}

	/**
	 * Create an image element for the preview
	 *
	 * @param {string} id - Image ID.
	 * @param {Object} image - Image data with url and alt.
	 * @returns {jQuery} The image element.
	 */
	createImageElement(id, image) {
		const $container = $('<div>', {
			class: 'giftflow-gallery-image',
			'data-id': id,
		});

		const $img = $('<img>', {
			src: image.url,
			alt: image.alt || '',
		});

		const $remove = $('<span>', {
			class: 'giftflow-gallery-remove',
			title: this.options.i18n.removeImage,
			html: '&times;',
		});

		$container.append($img, $remove);

		return $container;
	}

	/**
	 * Update the hidden input from preview order
	 */
	updateInputFromPreview() {
		const ids = [];
		this.$preview.find('.giftflow-gallery-image').each(function () {
			ids.push($(this).data('id').toString());
		});
		this.setImageIds(ids);
	}

	/**
	 * Update button visibility states
	 */
	updateButtonStates() {
		const hasImages = this.getImageIds().length > 0;
		const maxReached = this.isMaxReached();

		// Toggle remove all button
		if (hasImages) {
			this.$removeAllButton.show();
		} else {
			this.$removeAllButton.hide();
		}

		// Toggle add button state
		if (maxReached) {
			this.$addButton.addClass('disabled').prop('disabled', true);
		} else {
			this.$addButton.removeClass('disabled').prop('disabled', false);
		}
	}

	/**
	 * Check if max images limit is reached
	 *
	 * @returns {boolean} True if max reached.
	 */
	isMaxReached() {
		if (this.options.maxImages <= 0) {
			return false;
		}
		return this.getImageIds().length >= this.options.maxImages;
	}

	/**
	 * Get current image IDs
	 *
	 * @returns {string[]} Array of image IDs.
	 */
	getImageIds() {
		const value = this.$input.val();
		return value ? value.split(',').filter((id) => id.trim() !== '') : [];
	}

	/**
	 * Set image IDs
	 *
	 * @param {string[]|number[]} ids - Array of image IDs.
	 * @returns {GiftFlowGalleryField} Returns this for chaining.
	 */
	setImageIds(ids) {
		this.$input.val(ids.join(','));
		this.$input.trigger('change');
		this.updateButtonStates();
		return this;
	}

	/**
	 * Add images by IDs
	 *
	 * @param {string[]|number[]} ids - Array of image IDs to add.
	 * @returns {GiftFlowGalleryField} Returns this for chaining.
	 */
	addImages(ids) {
		const currentIds = this.getImageIds();
		const newIds = ids.map((id) => id.toString()).filter((id) => !currentIds.includes(id));

		if (newIds.length > 0) {
			const allIds = [...currentIds, ...newIds];

			// Respect max limit
			if (this.options.maxImages > 0) {
				allIds.splice(this.options.maxImages);
			}

			this.setImageIds(allIds);
			this.refreshPreview();
		}

		return this;
	}

	/**
	 * Get image count
	 *
	 * @returns {number} Number of images.
	 */
	getCount() {
		return this.getImageIds().length;
	}

	/**
	 * Destroy the instance and clean up
	 */
	destroy() {
		this.$addButton.off('.giftflowGallery');
		this.$removeAllButton.off('.giftflowGallery');
		this.$gallery.off('.giftflowGallery');

		if (this.$preview.data('ui-sortable')) {
			this.$preview.sortable('destroy');
		}

		if (this.mediaFrame) {
			this.mediaFrame.off('select');
			this.mediaFrame = null;
		}
	}

	/**
	 * Initialize all gallery fields matching a selector
	 *
	 * @param {string} selector - Selector for gallery containers.
	 * @param {Object} options - Configuration options.
	 * @returns {GiftFlowGalleryField[]} Array of gallery instances.
	 */
	static initAll(selector, options = {}) {
		const instances = [];
		$(selector).each(function () {
			instances.push(new GiftFlowGalleryField(this, options));
		});
		return instances;
	}
}

export default GiftFlowGalleryField;
