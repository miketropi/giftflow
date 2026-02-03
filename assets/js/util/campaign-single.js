/**
 * GiftFlow Tab Widget Class
 *
 * A reusable class for managing tabbed content widgets.
 *
 * @package GiftFlow
 * @since 1.0.0
 */

class GiftFlowTabWidget {
	/**
	 * Default configuration options
	 *
	 * @type {Object}
	 */
	static defaults = {
		tabItemSelector: '.giftflow-tab-widget-tab-item',
		contentItemSelector: '.giftflow-tab-widget-content-item',
		contentContainerSelector: '.giftflow-tab-widget-content',
		activeClass: 'active',
		tabIdAttribute: 'tabId',
		useHash: true,
		hashKeywords: {
			comment: 'comments',
		},
	};

	/**
	 * Constructor
	 *
	 * @param {string|HTMLElement} selector - The tab widget container selector or element.
	 * @param {Object} options - Configuration options.
	 */
	constructor(selector, options = {}) {
		this.container = typeof selector === 'string' ? document.querySelector(selector) : selector;

		if (!this.container) {
			console.warn('GiftFlowTabWidget: Tab widget element not found.');
			return;
		}

		this.options = { ...GiftFlowTabWidget.defaults, ...options };
		this.activeTabId = null;

		this.cacheElements();
		this.bindEvents();
		this.initFromHash();
	}

	/**
	 * Cache DOM elements
	 */
	cacheElements() {
		this.contentContainer = this.container.querySelector(this.options.contentContainerSelector) || this.container;
		this.tabItems = this.container.querySelectorAll(this.options.tabItemSelector);
		this.contentItems = this.contentContainer.querySelectorAll(this.options.contentItemSelector);
	}

	/**
	 * Bind event listeners
	 */
	bindEvents() {
		this.tabItems.forEach((tabItem) => {
			tabItem.addEventListener('click', (e) => {
				e.preventDefault();
				const tabId = tabItem.dataset[this.options.tabIdAttribute];
				this.activateTab(tabId);
			});

			// Keyboard accessibility
			tabItem.addEventListener('keydown', (e) => {
				if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					const tabId = tabItem.dataset[this.options.tabIdAttribute];
					this.activateTab(tabId);
				}

				// Arrow key navigation
				if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
					e.preventDefault();
					this.navigateWithArrows(e.key === 'ArrowRight' ? 1 : -1, tabItem);
				}
			});
		});

		// Listen for hash changes
		if (this.options.useHash) {
			window.addEventListener('hashchange', () => this.initFromHash());
		}
	}

	/**
	 * Initialize tab from URL hash
	 */
	initFromHash() {
		if (!this.options.useHash) {
			return;
		}

		const hash = window.location.hash.substring(1);

		if (hash) {
			// Check for keyword matches (e.g., 'comment' -> 'comments' tab)
			for (const [keyword, tabId] of Object.entries(this.options.hashKeywords)) {
				if (hash.includes(keyword)) {
					this.activateTab(tabId);
					return;
				}
			}

			// Try direct tab ID match
			this.activateTab(hash);
		}
	}

	/**
	 * Navigate tabs with arrow keys
	 *
	 * @param {number} direction - Direction to navigate (1 for next, -1 for previous).
	 * @param {HTMLElement} currentTab - The currently focused tab.
	 */
	navigateWithArrows(direction, currentTab) {
		const tabsArray = Array.from(this.tabItems);
		const currentIndex = tabsArray.indexOf(currentTab);
		let newIndex = currentIndex + direction;

		// Wrap around
		if (newIndex < 0) {
			newIndex = tabsArray.length - 1;
		} else if (newIndex >= tabsArray.length) {
			newIndex = 0;
		}

		const newTab = tabsArray[newIndex];
		newTab.focus();
		this.activateTab(newTab.dataset[this.options.tabIdAttribute]);
	}

	/**
	 * Activate a tab by its ID
	 *
	 * @param {string} tabId - The tab ID to activate.
	 * @returns {GiftFlowTabWidget} Returns this for chaining.
	 */
	activateTab(tabId) {
		if (!tabId || tabId === this.activeTabId) {
			return this;
		}
		
		const targetTab = this.container.querySelector(
			`${this.options.tabItemSelector}[data-${this.toKebabCase(this.options.tabIdAttribute)}="${tabId}"]`
		);
		const targetContent = this.contentContainer.querySelector(
			`${this.options.contentItemSelector}[data-${this.toKebabCase(this.options.tabIdAttribute)}="${tabId}"]`
		);

		if (!targetTab || !targetContent) {
			return this;
		}

		// Deactivate all tabs and content
		this.tabItems.forEach((tab) => {
			tab.classList.remove(this.options.activeClass);
			tab.setAttribute('aria-selected', 'false');
			tab.setAttribute('tabindex', '-1');
		});

		this.contentItems.forEach((content) => {
			content.classList.remove(this.options.activeClass);
			content.setAttribute('aria-hidden', 'true');
		});

		// Activate target tab and content
		targetTab.classList.add(this.options.activeClass);
		targetTab.setAttribute('aria-selected', 'true');
		targetTab.setAttribute('tabindex', '0');

		targetContent.classList.add(this.options.activeClass);
		targetContent.setAttribute('aria-hidden', 'false');

		this.activeTabId = tabId;

		// Dispatch custom event
		this.container.dispatchEvent(
			new CustomEvent('giftflow:tab:changed', {
				detail: { tabId, tab: targetTab, content: targetContent, instance: this },
				bubbles: true,
			})
		);

		return this;
	}

	/**
	 * Convert camelCase to kebab-case
	 *
	 * @param {string} str - The string to convert.
	 * @returns {string} The kebab-case string.
	 */
	toKebabCase(str) {
		return str.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
	}

	/**
	 * Get the currently active tab ID
	 *
	 * @returns {string|null} The active tab ID or null.
	 */
	getActiveTabId() {
		return this.activeTabId;
	}

	/**
	 * Get the currently active tab element
	 *
	 * @returns {HTMLElement|null} The active tab element or null.
	 */
	getActiveTab() {
		if (!this.activeTabId) {
			return null;
		}
		return this.container.querySelector(
			`${this.options.tabItemSelector}[data-${this.toKebabCase(this.options.tabIdAttribute)}="${this.activeTabId}"]`
		);
	}

	/**
	 * Get the currently active content element
	 *
	 * @returns {HTMLElement|null} The active content element or null.
	 */
	getActiveContent() {
		if (!this.activeTabId) {
			return null;
		}
		return this.contentContainer.querySelector(
			`${this.options.contentItemSelector}[data-${this.toKebabCase(this.options.tabIdAttribute)}="${this.activeTabId}"]`
		);
	}

	/**
	 * Go to the next tab
	 *
	 * @returns {GiftFlowTabWidget} Returns this for chaining.
	 */
	next() {
		const tabsArray = Array.from(this.tabItems);
		const currentIndex = tabsArray.findIndex((tab) => tab.dataset[this.options.tabIdAttribute] === this.activeTabId);
		const nextIndex = (currentIndex + 1) % tabsArray.length;
		const nextTabId = tabsArray[nextIndex].dataset[this.options.tabIdAttribute];
		return this.activateTab(nextTabId);
	}

	/**
	 * Go to the previous tab
	 *
	 * @returns {GiftFlowTabWidget} Returns this for chaining.
	 */
	prev() {
		const tabsArray = Array.from(this.tabItems);
		const currentIndex = tabsArray.findIndex((tab) => tab.dataset[this.options.tabIdAttribute] === this.activeTabId);
		const prevIndex = (currentIndex - 1 + tabsArray.length) % tabsArray.length;
		const prevTabId = tabsArray[prevIndex].dataset[this.options.tabIdAttribute];
		return this.activateTab(prevTabId);
	}

	/**
	 * Refresh the cached elements (useful after dynamic content changes)
	 *
	 * @returns {GiftFlowTabWidget} Returns this for chaining.
	 */
	refresh() {
		this.cacheElements();
		return this;
	}

	/**
	 * Destroy the instance and clean up
	 */
	destroy() {
		this.tabItems.forEach((tabItem) => {
			tabItem.replaceWith(tabItem.cloneNode(true));
		});

		if (this.options.useHash) {
			window.removeEventListener('hashchange', this.initFromHash);
		}

		this.container = null;
		this.contentContainer = null;
		this.tabItems = null;
		this.contentItems = null;
	}

	/**
	 * Initialize all tab widgets matching a selector
	 *
	 * @param {string} selector - Selector for tab widget containers.
	 * @param {Object} options - Configuration options.
	 * @returns {GiftFlowTabWidget[]} Array of tab widget instances.
	 */
	static initAll(selector, options = {}) {
		const instances = [];
		document.querySelectorAll(selector).forEach((element) => {
			instances.push(new GiftFlowTabWidget(element, options));
		});
		return instances;
	}
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
	// Auto-init any tab widgets with default class
	window.giftflowTabWidgets = GiftFlowTabWidget.initAll('.giftflow-campaign-single-content');
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
	module.exports = GiftFlowTabWidget;
}

// Make available globally
window.GiftFlowTabWidget = GiftFlowTabWidget;
