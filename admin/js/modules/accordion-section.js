/**
 * GiftFlow Accordion Section Class
 *
 * A reusable class for managing accordion/collapsible sections.
 *
 * @package GiftFlow
 * @since 1.0.0
 */

const $ = jQuery.noConflict();

class GiftFlowAccordion {
  
	/**
	 * Default configuration options
	 *
	 * @type {Object}
	 */
	static defaults = {
		duration: 200,
		openClass: 'open',
		headerSelector: '.giftflow-accordion-header',
		contentSelector: '.giftflow-accordion-content',
		iconSelector: '.dashicons',
		initialOpen: false,
	};

	/**
	 * Constructor
	 *
	 * @param {string|HTMLElement} selector - The accordion container selector or element.
	 * @param {Object} options - Configuration options.
	 */
	constructor(selector, options = {}) {
		this.$accordion = typeof selector === 'string' ? $(selector) : $(selector);

		if (!this.$accordion.length) {
			console.warn('GiftFlowAccordion: Accordion element not found.');
			return;
		}

		this.options = { ...GiftFlowAccordion.defaults, ...options };
		this.isOpen = false;

		this.cacheElements();
		this.bindEvents();
		this.initState();
	}

	/**
	 * Cache DOM elements
	 */
	cacheElements() {
		this.$header = this.$accordion.find(this.options.headerSelector);
		this.$content = this.$accordion.find(this.options.contentSelector);
		this.$icon = this.$header.find(this.options.iconSelector);
	}

	/**
	 * Bind event listeners
	 */
	bindEvents() {
		this.$header.on('click.giftflowAccordion', (e) => {
			e.preventDefault();
			this.toggle();
		});

		// Keyboard accessibility
		this.$header.on('keydown.giftflowAccordion', (e) => {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				this.toggle();
			}
		});
	}

	/**
	 * Initialize the accordion state
	 */
	initState() {
		// Check if already has open class or option set
		const shouldBeOpen = this.$accordion.hasClass(this.options.openClass) || this.options.initialOpen;

		if (shouldBeOpen) {
			this.isOpen = true;
			this.$accordion.addClass(this.options.openClass);
			this.$content.show();
			this.rotateIcon(true);
		} else {
			this.isOpen = false;
			this.$accordion.removeClass(this.options.openClass);
			this.$content.hide();
			this.rotateIcon(false);
		}

		// Set ARIA attributes for accessibility
		this.$header.attr({
			role: 'button',
			tabindex: '0',
			'aria-expanded': this.isOpen,
		});
		this.$content.attr({
			role: 'region',
			'aria-hidden': !this.isOpen,
		});
	}

	/**
	 * Rotate the icon based on state
	 *
	 * @param {boolean} isOpen - Whether the accordion is open.
	 */
	rotateIcon(isOpen) {
		const rotation = isOpen ? 'rotate(180deg)' : 'rotate(0deg)';
		this.$icon.css('transform', rotation);
	}

	/**
	 * Update ARIA attributes
	 */
	updateAria() {
		this.$header.attr('aria-expanded', this.isOpen);
		this.$content.attr('aria-hidden', !this.isOpen);
	}

	/**
	 * Toggle the accordion open/closed
	 *
	 * @returns {GiftFlowAccordion} Returns this for chaining.
	 */
	toggle() {
		if (this.isOpen) {
			this.close();
		} else {
			this.open();
		}
		return this;
	}

	/**
	 * Open the accordion
	 *
	 * @param {boolean} animate - Whether to animate. Default true.
	 * @returns {GiftFlowAccordion} Returns this for chaining.
	 */
	open(animate = true) {
		if (this.isOpen) {
			return this;
		}

		this.isOpen = true;
		this.$accordion.addClass(this.options.openClass);
		this.rotateIcon(true);
		this.updateAria();

		if (animate) {
			this.$content.slideDown(this.options.duration, () => {
				this.$accordion.trigger('giftflow:accordion:opened', [this]);
			});
		} else {
			this.$content.show();
			this.$accordion.trigger('giftflow:accordion:opened', [this]);
		}

		return this;
	}

	/**
	 * Close the accordion
	 *
	 * @param {boolean} animate - Whether to animate. Default true.
	 * @returns {GiftFlowAccordion} Returns this for chaining.
	 */
	close(animate = true) {
		if (!this.isOpen) {
			return this;
		}

		this.isOpen = false;
		this.$accordion.removeClass(this.options.openClass);
		this.rotateIcon(false);
		this.updateAria();

		if (animate) {
			this.$content.slideUp(this.options.duration, () => {
				this.$accordion.trigger('giftflow:accordion:closed', [this]);
			});
		} else {
			this.$content.hide();
			this.$accordion.trigger('giftflow:accordion:closed', [this]);
		}

		return this;
	}

	/**
	 * Check if accordion is currently open
	 *
	 * @returns {boolean} Whether the accordion is open.
	 */
	isOpened() {
		return this.isOpen;
	}

	/**
	 * Destroy the instance and clean up
	 */
	destroy() {
		this.$header.off('.giftflowAccordion');
		this.$header.removeAttr('role tabindex aria-expanded');
		this.$content.removeAttr('role aria-hidden');
		this.$icon.css('transform', '');
		this.$accordion.removeClass(this.options.openClass);
	}

	/**
	 * Initialize all accordions matching a selector
	 *
	 * @param {string} selector - Selector for accordion containers.
	 * @param {Object} options - Configuration options.
	 * @returns {GiftFlowAccordion[]} Array of accordion instances.
	 */
	static initAll(selector, options = {}) {
		const instances = [];
		$(selector).each(function () {
			instances.push(new GiftFlowAccordion(this, options));
		});
		return instances;
	}
}

export default GiftFlowAccordion;
