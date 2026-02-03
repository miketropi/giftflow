/**
 * GiftFlow Share Block Class
 *
 * A reusable class for managing social sharing and clipboard copy functionality.
 *
 * @package GiftFlow
 * @since 1.0.0
 */

class GiftFlowShare {
	/**
	 * Default configuration options
	 *
	 * @type {Object}
	 */
	static defaults = {
		copyButtonSelector: '.giftflow-share__button--copy-url',
		feedbackSelector: '.giftflow-share__copy-feedback',
		shareButtonSelector: '.giftflow-share__button',
		urlAttribute: 'data-url',
		networkAttribute: 'data-network',
		feedbackDuration: 2000,
		shareUrl: null, // If null, uses current page URL
		shareTitle: null, // If null, uses document title
		shareText: null,
		popupWidth: 600,
		popupHeight: 400,
	};

	/**
	 * Share URL templates for different networks
	 *
	 * @type {Object}
	 */
	static networks = {
		facebook: 'https://www.facebook.com/sharer/sharer.php?u={url}',
		twitter: 'https://twitter.com/intent/tweet?url={url}&text={title}',
		x: 'https://twitter.com/intent/tweet?url={url}&text={title}',
		linkedin: 'https://www.linkedin.com/sharing/share-offsite/?url={url}',
		pinterest: 'https://pinterest.com/pin/create/button/?url={url}&description={title}',
		reddit: 'https://reddit.com/submit?url={url}&title={title}',
		telegram: 'https://t.me/share/url?url={url}&text={title}',
		whatsapp: 'https://api.whatsapp.com/send?text={title}%20{url}',
		email: 'mailto:?subject={title}&body={text}%20{url}',
	};

	/**
	 * Constructor
	 *
	 * @param {string|HTMLElement} selector - The share block container selector or element.
	 * @param {Object} options - Configuration options.
	 */
	constructor(selector, options = {}) {
		this.container = typeof selector === 'string' ? document.querySelector(selector) : selector;

		if (!this.container) {
			console.warn('GiftFlowShare: Share block element not found.');
			return;
		}

		this.options = { ...GiftFlowShare.defaults, ...options };
		this.feedbackTimeout = null;

		this.cacheElements();
		this.bindEvents();
	}

	/**
	 * Cache DOM elements
	 */
	cacheElements() {
		this.copyButton = this.container.querySelector(this.options.copyButtonSelector);
		this.feedback = this.container.querySelector(this.options.feedbackSelector);
		this.shareButtons = this.container.querySelectorAll(this.options.shareButtonSelector);
	}

	/**
	 * Bind event listeners
	 */
	bindEvents() {
		// Copy button
		if (this.copyButton) {
			this.copyButton.addEventListener('click', (e) => {
				e.preventDefault();
				this.copyToClipboard();
			});
		}

		// Share buttons
		this.shareButtons.forEach((button) => {
			button.addEventListener('click', (e) => {
				e.preventDefault();
				const network = button.getAttribute(this.options.networkAttribute);
				const url = button.getAttribute(this.options.urlAttribute);
				this.share(network, url);
			});
		});
	}

	/**
	 * Get the share URL
	 *
	 * @returns {string} The URL to share.
	 */
	getShareUrl() {
		return this.options.shareUrl || window.location.href;
	}

	/**
	 * Get the share title
	 *
	 * @returns {string} The title to share.
	 */
	getShareTitle() {
		return this.options.shareTitle || document.title;
	}

	/**
	 * Get the share text
	 *
	 * @returns {string} The text to share.
	 */
	getShareText() {
		return this.options.shareText || '';
	}

	/**
	 * Copy URL to clipboard
	 *
	 * @param {string} url - Optional URL to copy. Defaults to share URL.
	 * @returns {Promise<boolean>} Promise resolving to success status.
	 */
	async copyToClipboard(url = null) {
		const textToCopy = url || this.getShareUrl();

		try {
			if (navigator.clipboard && window.isSecureContext) {
				await navigator.clipboard.writeText(textToCopy);
				this.showFeedback();
				this.dispatchEvent('copied', { url: textToCopy });
				return true;
			} else {
				return this.fallbackCopy(textToCopy);
			}
		} catch (err) {
			console.error('GiftFlowShare: Failed to copy:', err);
			return this.fallbackCopy(textToCopy);
		}
	}

	/**
	 * Fallback copy method for older browsers
	 *
	 * @param {string} text - Text to copy.
	 * @returns {boolean} Success status.
	 */
	fallbackCopy(text) {
		const textArea = document.createElement('textarea');
		textArea.value = text;

		// Make it invisible
		Object.assign(textArea.style, {
			position: 'fixed',
			left: '-999999px',
			top: '-999999px',
			opacity: '0',
		});

		document.body.appendChild(textArea);
		textArea.focus();
		textArea.select();

		try {
			const success = document.execCommand('copy');
			if (success) {
				this.showFeedback();
				this.dispatchEvent('copied', { url: text });
			}
			return success;
		} catch (err) {
			console.error('GiftFlowShare: Fallback copy failed:', err);
			return false;
		} finally {
			document.body.removeChild(textArea);
		}
	}

	/**
	 * Show copy feedback message
	 */
	showFeedback() {
		if (!this.feedback) {
			return;
		}

		// Clear any existing timeout
		if (this.feedbackTimeout) {
			clearTimeout(this.feedbackTimeout);
		}

		this.feedback.style.display = 'block';
		this.feedback.classList.add('is-visible');

		this.feedbackTimeout = setTimeout(() => {
			this.feedback.style.display = 'none';
			this.feedback.classList.remove('is-visible');
		}, this.options.feedbackDuration);
	}

	/**
	 * Share to a social network
	 *
	 * @param {string} network - The network name (facebook, twitter, etc.).
	 * @param {string} url - Optional URL to share. Defaults to share URL.
	 * @returns {Window|null} The popup window or null.
	 */
	share(network, url = null) {
		const shareUrl = url || this.getShareUrl();
		const shareTitle = this.getShareTitle();
		const shareText = this.getShareText();

		// Use Web Share API if available and network is 'native'
		if (network === 'native' && navigator.share) {
			return this.nativeShare(shareUrl, shareTitle, shareText);
		}

		// Get network URL template
		const template = GiftFlowShare.networks[network?.toLowerCase()];

		if (!template) {
			console.warn(`GiftFlowShare: Unknown network "${network}"`);
			return null;
		}

		// Build share URL
		const networkUrl = template
			.replace('{url}', encodeURIComponent(shareUrl))
			.replace('{title}', encodeURIComponent(shareTitle))
			.replace('{text}', encodeURIComponent(shareText));

		// Handle email differently
		if (network.toLowerCase() === 'email') {
			window.location.href = networkUrl;
			this.dispatchEvent('shared', { network, url: shareUrl });
			return null;
		}

		// Open popup
		const popup = this.openPopup(networkUrl, network);

		this.dispatchEvent('shared', { network, url: shareUrl, popup });

		return popup;
	}

	/**
	 * Use native Web Share API
	 *
	 * @param {string} url - URL to share.
	 * @param {string} title - Title to share.
	 * @param {string} text - Text to share.
	 * @returns {Promise} Share promise.
	 */
	async nativeShare(url, title, text) {
		try {
			await navigator.share({
				title,
				text,
				url,
			});
			this.dispatchEvent('shared', { network: 'native', url });
			return true;
		} catch (err) {
			if (err.name !== 'AbortError') {
				console.error('GiftFlowShare: Native share failed:', err);
			}
			return false;
		}
	}

	/**
	 * Open a popup window
	 *
	 * @param {string} url - URL to open.
	 * @param {string} name - Window name.
	 * @returns {Window|null} The popup window.
	 */
	openPopup(url, name) {
		const { popupWidth, popupHeight } = this.options;

		// Center the popup
		const left = (window.innerWidth - popupWidth) / 2 + window.screenX;
		const top = (window.innerHeight - popupHeight) / 2 + window.screenY;

		const features = [
			`width=${popupWidth}`,
			`height=${popupHeight}`,
			`left=${left}`,
			`top=${top}`,
			'toolbar=no',
			'menubar=no',
			'scrollbars=yes',
			'resizable=yes',
		].join(',');

		return window.open(url, name, features);
	}

	/**
	 * Dispatch a custom event
	 *
	 * @param {string} eventName - Event name (without prefix).
	 * @param {Object} detail - Event detail data.
	 */
	dispatchEvent(eventName, detail = {}) {
		this.container.dispatchEvent(
			new CustomEvent(`giftflow:share:${eventName}`, {
				detail: { ...detail, instance: this },
				bubbles: true,
			})
		);
	}

	/**
	 * Check if native sharing is supported
	 *
	 * @returns {boolean} True if Web Share API is available.
	 */
	static isNativeShareSupported() {
		return typeof navigator.share === 'function';
	}

	/**
	 * Add a custom network
	 *
	 * @param {string} name - Network name.
	 * @param {string} urlTemplate - URL template with {url}, {title}, {text} placeholders.
	 */
	static addNetwork(name, urlTemplate) {
		GiftFlowShare.networks[name.toLowerCase()] = urlTemplate;
	}

	/**
	 * Destroy the instance and clean up
	 */
	destroy() {
		if (this.feedbackTimeout) {
			clearTimeout(this.feedbackTimeout);
		}

		// Clone and replace to remove event listeners
		if (this.copyButton) {
			this.copyButton.replaceWith(this.copyButton.cloneNode(true));
		}

		this.shareButtons.forEach((button) => {
			button.replaceWith(button.cloneNode(true));
		});

		this.container = null;
		this.copyButton = null;
		this.feedback = null;
		this.shareButtons = null;
	}

	/**
	 * Initialize all share blocks matching a selector
	 *
	 * @param {string} selector - Selector for share block containers.
	 * @param {Object} options - Configuration options.
	 * @returns {GiftFlowShare[]} Array of share instances.
	 */
	static initAll(selector, options = {}) {
		const instances = [];
		document.querySelectorAll(selector).forEach((element) => {
			instances.push(new GiftFlowShare(element, options));
		});
		return instances;
	}
}

// Legacy function support (backwards compatibility)
function giftflowCopyUrlToClipboard(url, button) {
	const container = button.closest('.giftflow-share');
	if (container && container._giftflowShare) {
		container._giftflowShare.copyToClipboard(url);
	} else {
		// Standalone copy
		const tempShare = new GiftFlowShare(container || document.body);
		tempShare.copyToClipboard(url);
	}
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
	window.giftflowShareBlocks = GiftFlowShare.initAll('.giftflow-share');

	// Store instance reference on elements
	window.giftflowShareBlocks.forEach((instance) => {
		if (instance.container) {
			instance.container._giftflowShare = instance;
		}
	});
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
	module.exports = GiftFlowShare;
}

// Make available globally
window.GiftFlowShare = GiftFlowShare;
