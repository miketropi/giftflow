/**
 * GiftFlow Modal Library
 * A clean, simple, and easy-to-use modal system with AJAX support
 * 
 * QUICK START EXAMPLES:
 * 
 * Basic Modal:
 * const modal = new GiftFlowModal({
 *     content: '<h2>Hello World!</h2><p>This is a simple modal.</p>',
 *     closeButton: true
 * });
 * modal.open();
 * 
 * AJAX Modal:
 * const ajaxModal = new GiftFlowModal({
 *     ajax: true,
 *     ajaxUrl: '/api/get-content',
 *     onLoad: (content, modal) => console.log('Content loaded:', content)
 * });
 * ajaxModal.open();
 * 
 * Quick Dialogs:
 * GiftFlowModal.alert('Operation completed!', 'Success');
 * const confirmed = await GiftFlowModal.confirm('Are you sure?');
 * const value = await GiftFlowModal.prompt('Enter your name:', 'John Doe');
 * 
 * Custom Modal:
 * const customModal = new GiftFlowModal({
 *     content: '<p>Custom sized modal</p>',
 *     width: '800px',
 *     animation: 'zoom',
 *     duration: 500
 * });
 * customModal.open();
 * 
 * For complete documentation and examples, see:
 * wp-content/plugins/giftflow/assets/js/util/README.md
 */

/**
 * GiftFlow Modal Library
 * A clean, simple, and easy-to-use modal system with AJAX support
 */
class GiftFlowModal {
    constructor(options = {}) {
        this.options = {
            // Modal options
            id: options.id || 'giftflow-modal',
            className: options.className || 'giftflow-modal',
            overlay: options.overlay !== false,
            closeOnOverlay: options.closeOnOverlay !== false,
            closeOnEscape: options.closeOnEscape !== false,
            closeButton: options.closeButton !== false,
            
            // Animation options
            animation: options.animation || 'fade', // 'fade', 'slide', 'zoom'
            duration: options.duration || 300,
            easing: options.easing || 'ease-in-out',
            
            // Content options
            content: options.content || '',
            ajax: options.ajax || false,
            ajaxUrl: options.ajaxUrl || '',
            ajaxData: options.ajaxData || {},
            ajaxMethod: options.ajaxMethod || 'GET',
            
            // Size options
            width: options.width || 'auto',
            maxWidth: options.maxWidth || '90vw',
            height: options.height || 'auto',
            maxHeight: options.maxHeight || '90vh',
            
            // Position options
            position: options.position || 'center', // 'center', 'top', 'bottom'
            
            // Callbacks
            onOpen: options.onOpen || null,
            onClose: options.onClose || null,
            onLoad: options.onLoad || null,
            onError: options.onError || null,
            
            // Auto-close options
            autoClose: options.autoClose || false,
            autoCloseDelay: options.autoCloseDelay || 5000,
            
            // Accessibility
            ariaLabel: options.ariaLabel || 'Modal dialog',
            focusTrap: options.focusTrap !== false,
            
            ...options
        };
        
        this.isOpen = false;
        this.modalElement = null;
        this.overlayElement = null;
        this.contentElement = null;
        this.closeButtonElement = null;
        this.focusableElements = [];
        this.lastFocusedElement = null;
        this.autoCloseTimer = null;
        
        this.init();
    }
    
    /**
     * Initialize the modal
     */
    init() {
        this.createModal();
        this.bindEvents();
        
        if (this.options.ajax && this.options.ajaxUrl) {
            this.loadAjaxContent();
        }
    }
    
    /**
     * Create modal HTML structure
     */
    createModal() {
        // Create overlay
        if (this.options.overlay) {
            this.overlayElement = document.createElement('div');
            this.overlayElement.className = 'giftflow-modal__overlay';
            this.overlayElement.setAttribute('aria-hidden', 'true');
        }
        
        // Create modal container
        this.modalElement = document.createElement('div');
        this.modalElement.id = this.options.id;
        this.modalElement.className = `giftflow-modal ${this.options.className}`;
        this.modalElement.setAttribute('role', 'dialog');
        this.modalElement.setAttribute('aria-modal', 'true');
        this.modalElement.setAttribute('aria-label', this.options.ariaLabel);
        this.modalElement.setAttribute('tabindex', '-1');
        
        // Set modal dimensions
        if (this.options.width !== 'auto') {
            this.modalElement.style.width = this.options.width;
        }
        if (this.options.maxWidth !== '90vw') {
            this.modalElement.style.maxWidth = this.options.maxWidth;
        }
        if (this.options.height !== 'auto') {
            this.modalElement.style.height = this.options.height;
        }
        if (this.options.maxHeight !== '90vh') {
            this.modalElement.style.maxHeight = this.options.maxHeight;
        }
        
        // Create modal content
        this.contentElement = document.createElement('div');
        this.contentElement.className = 'giftflow-modal__content';
        
        // Add close button if enabled
        if (this.options.closeButton) {
            this.closeButtonElement = document.createElement('button');
            this.closeButtonElement.className = 'giftflow-modal__close';
            this.closeButtonElement.setAttribute('type', 'button');
            this.closeButtonElement.setAttribute('aria-label', 'Close modal');
            this.closeButtonElement.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>`;
            this.modalElement.appendChild(this.closeButtonElement);
        }
        
        // Add content
        if (this.options.content && !this.options.ajax) {
            this.contentElement.innerHTML = this.options.content;
        }
        
        this.modalElement.appendChild(this.contentElement);
        
        // Append to DOM
        if (this.options.overlay) {
            this.overlayElement.appendChild(this.modalElement);
            document.body.appendChild(this.overlayElement);
        } else {
            document.body.appendChild(this.modalElement);
        }
        
        // Add animation classes
        this.modalElement.classList.add(`giftflow-modal--${this.options.animation}`);
    }
    
    /**
     * Bind event listeners
     */
    bindEvents() {
        // Close button click
        if (this.closeButtonElement) {
            this.closeButtonElement.addEventListener('click', () => this.close());
        }
        
        // Overlay click
        if (this.options.overlay && this.options.closeOnOverlay) {
            this.overlayElement.addEventListener('click', (e) => {
                if (e.target === this.overlayElement) {
                    this.close();
                }
            });
        }
        
        // Escape key
        if (this.options.closeOnEscape) {
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            });
        }
        
        // Focus trap
        if (this.options.focusTrap) {
            this.modalElement.addEventListener('keydown', (e) => {
                if (e.key === 'Tab') {
                    this.handleTabKey(e);
                }
            });
        }
    }
    
    /**
     * Handle tab key for focus trapping
     */
    handleTabKey(e) {
        this.focusableElements = this.modalElement.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        const firstElement = this.focusableElements[0];
        const lastElement = this.focusableElements[this.focusableElements.length - 1];
        
        if (e.shiftKey) {
            if (document.activeElement === firstElement) {
                e.preventDefault();
                lastElement.focus();
            }
        } else {
            if (document.activeElement === lastElement) {
                e.preventDefault();
                firstElement.focus();
            }
        }
    }
    
    /**
     * Load content via AJAX
     */
    async loadAjaxContent() {
        try {
            this.showLoading();
            // return;
            const response = await fetch(this.options.ajaxUrl, {
                method: this.options.ajaxMethod,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: this.options.ajaxMethod === 'POST' ? JSON.stringify(this.options.ajaxData) : undefined,
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const content = await response.text();
            this.contentElement.innerHTML = content;
            
            if (this.options.onLoad) {
                this.options.onLoad(content, this);
            }
            
        } catch (error) {
            console.error('Modal AJAX error:', error);
            this.contentElement.innerHTML = '<div class="giftflow-modal__error">Failed to load content</div>';
            
            if (this.options.onError) {
                this.options.onError(error, this);
            }
        }
    }
    
    /**
     * Show loading state
     */
    showLoading() {
        this.contentElement.innerHTML = '<div class="giftflow-modal__loading">Loading...</div>';
    }
    
    /**
     * Open the modal
     */
    open() {
        if (this.isOpen) return;
        
        this.isOpen = true;
        this.lastFocusedElement = document.activeElement;
        
        // Show modal
        if (this.options.overlay) {
            this.overlayElement.style.display = 'flex';
            this.overlayElement.setAttribute('aria-hidden', 'false');
        }
        
        this.modalElement.style.display = 'block';
        
        // Trigger animation
        requestAnimationFrame(() => {
            this.modalElement.classList.add('giftflow-modal--open');
            if (this.options.overlay) {
                this.overlayElement.classList.add('giftflow-modal__overlay--open');
            }
        });
        
        // Focus modal
        this.modalElement.focus();
        
        // Set auto-close timer
        if (this.options.autoClose) {
            this.autoCloseTimer = setTimeout(() => {
                this.close();
            }, this.options.autoCloseDelay);
        }
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        // Call onOpen callback
        if (this.options.onOpen) {
            this.options.onOpen(this);
        }
    }
    
    /**
     * Close the modal
     */
    close() {
        if (!this.isOpen) return;
        
        this.isOpen = false;
        
        // Clear auto-close timer
        if (this.autoCloseTimer) {
            clearTimeout(this.autoCloseTimer);
            this.autoCloseTimer = null;
        }
        
        // Trigger close animation
        this.modalElement.classList.remove('giftflow-modal--open');
        if (this.options.overlay) {
            this.overlayElement.classList.remove('giftflow-modal__overlay--open');
        }
        
        // Wait for animation to complete
        setTimeout(() => {
            this.modalElement.style.display = 'none';
            if (this.options.overlay) {
                this.overlayElement.style.display = 'none';
                this.overlayElement.setAttribute('aria-hidden', 'true');
            }
            
            // Restore body scroll
            document.body.style.overflow = '';
            
            // Restore focus
            if (this.lastFocusedElement) {
                this.lastFocusedElement.focus();
            }
            
            // Call onClose callback
            if (this.options.onClose) {
                this.options.onClose(this);
            }
        }, this.options.duration);
    }
    
    /**
     * Update modal content
     */
    setContent(content) {
        this.contentElement.innerHTML = content;
    }
    
    /**
     * Update modal options
     */
    updateOptions(newOptions) {
        this.options = { ...this.options, ...newOptions };
        
        // Update dimensions if changed
        if (newOptions.width !== undefined) {
            this.modalElement.style.width = newOptions.width;
        }
        if (newOptions.maxWidth !== undefined) {
            this.modalElement.style.maxWidth = newOptions.maxWidth;
        }
        if (newOptions.height !== undefined) {
            this.modalElement.style.height = newOptions.height;
        }
        if (newOptions.maxHeight !== undefined) {
            this.modalElement.style.maxHeight = newOptions.maxHeight;
        }
    }
    
    /**
     * Destroy the modal
     */
    destroy() {
        this.close();
        
        if (this.autoCloseTimer) {
            clearTimeout(this.autoCloseTimer);
        }
        
        if (this.modalElement && this.modalElement.parentNode) {
            this.modalElement.parentNode.removeChild(this.modalElement);
        }
        
        if (this.overlayElement && this.overlayElement.parentNode) {
            this.overlayElement.parentNode.removeChild(this.overlayElement);
        }
        
        this.modalElement = null;
        this.overlayElement = null;
        this.contentElement = null;
        this.closeButtonElement = null;
    }
}

/**
 * Static methods for easy modal creation
 */
GiftFlowModal.create = function(options) {
    return new GiftFlowModal(options);
};

GiftFlowModal.alert = function(message, title = 'Alert', options = {}) {
    const modal = new GiftFlowModal({
        content: `
            <div class="giftflow-modal__header">
                <h3>${title}</h3>
            </div>
            <div class="giftflow-modal__body">
                <p>${message}</p>
            </div>
            <div class="giftflow-modal__footer">
                <button class="giftflow-modal__btn giftflow-modal__btn--primary" onclick="this.closest('.giftflow-modal').giftflowModal.close()">
                    OK
                </button>
            </div>
        `,
        closeButton: true,
        closeOnOverlay: false,
        closeOnEscape: true,
        ...options
    });
    
    // Store modal reference for the close button
    modal.modalElement.giftflowModal = modal;
    
    modal.open();
    return modal;
};

GiftFlowModal.confirm = function(message, title = 'Confirm', options = {}) {
    return new Promise((resolve) => {
        const modal = new GiftFlowModal({
            content: `
                <div class="giftflow-modal__header">
                    <h3>${title}</h3>
                </div>
                <div class="giftflow-modal__body">
                    <p>${message}</p>
                </div>
                <div class="giftflow-modal__footer">
                    <button class="giftflow-modal__btn giftflow-modal__btn--secondary" onclick="this.closest('.giftflow-modal').giftflowModal.confirmResult(false)">
                        Cancel
                    </button>
                    <button class="giftflow-modal__btn giftflow-modal__btn--primary" onclick="this.closest('.giftflow-modal').giftflowModal.confirmResult(true)">
                        OK
                    </button>
                </div>
            `,
            closeButton: true,
            closeOnOverlay: false,
            closeOnEscape: true,
            onClose: () => resolve(false),
            ...options
        });
        
        // Store modal reference and confirm method
        modal.modalElement.giftflowModal = modal;
        modal.confirmResult = (result) => {
            modal.close();
            resolve(result);
        };
        
        modal.open();
    });
};

GiftFlowModal.prompt = function(message, defaultValue = '', title = 'Input', options = {}) {
    return new Promise((resolve) => {
        const modal = new GiftFlowModal({
            content: `
                <div class="giftflow-modal__header">
                    <h3>${title}</h3>
                </div>
                <div class="giftflow-modal__body">
                    <p>${message}</p>
                    <input type="text" class="giftflow-modal__input" value="${defaultValue}" placeholder="Enter value...">
                </div>
                <div class="giftflow-modal__footer">
                    <button class="giftflow-modal__btn giftflow-modal__btn--secondary" onclick="this.closest('.giftflow-modal').giftflowModal.promptResult(null)">
                        Cancel
                    </button>
                    <button class="giftflow-modal__btn giftflow-modal__btn--primary" onclick="this.closest('.giftflow-modal').giftflowModal.promptResult(this.closest('.giftflow-modal').querySelector('.giftflow-modal__input').value)">
                        OK
                    </button>
                </div>
            `,
            closeButton: true,
            closeOnOverlay: false,
            closeOnEscape: true,
            onClose: () => resolve(null),
            ...options
        });
        
        // Store modal reference and prompt method
        modal.modalElement.giftflowModal = modal;
        modal.promptResult = (result) => {
            modal.close();
            resolve(result);
        };
        
        modal.open();
        
        // Focus input
        setTimeout(() => {
            const input = modal.modalElement.querySelector('.giftflow-modal__input');
            if (input) {
                input.focus();
                input.select();
            }
        }, 100);
    });
};

// Export for different module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = GiftFlowModal;
} else if (typeof define === 'function' && define.amd) {
    define(() => GiftFlowModal);
} else {
    window.GiftFlowModal = GiftFlowModal;
}
