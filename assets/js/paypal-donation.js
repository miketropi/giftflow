/**
 * PayPal Donation Integration
 * Uses PayPal JS SDK v6 for seamless payment processing
 */

((w) => {
	'use strict';

	// PayPal Donation class
	const PayPalDonation = class {

		/**
		 * Constructor
		 * 
		 * @param {Object} form - Form element.
		 * @param {Object} formObject - Form object.
		 * @returns {void}
		 */
		constructor(form, formObject) {
			this.form = form;
			this.formObject = formObject;
			this.paypalButtons = null;
			this.isInitialized = false;
			
			this.init();
		}

		/**
		 * Initialize PayPal buttons
		 * 
		 * @returns {void}
		 */
		async init() {
			const self = this;

			// Wait for PayPal SDK to load
			if (typeof paypal === 'undefined') {
				// Retry after a short delay
				setTimeout(() => {
					self.init();
				}, 100);
				return;
			}

			// Check if payment method is PayPal
			const paymentMethodInput = this.form.querySelector('input[name="payment_method"][value="paypal"]');
			if (!paymentMethodInput) {
				return;
			}

			// Get container
			const container = document.getElementById('giftflow-paypal-button-container');
			if (!container) {
				return;
			}

			// Only initialize once
			if (this.isInitialized) {
				return;
			}

			this.isInitialized = true;

			// Initialize PayPal buttons
			try {
				this.paypalButtons = paypal.Buttons({
					style: {
						layout: 'vertical',
						color: 'blue',
						shape: 'rect',
						label: 'paypal',
					},
					createOrder: async (data, actions) => {
						return await self.createOrder();
					},
					onApprove: async (data, actions) => {
						return await self.onApprove(data);
					},
					onCancel: (data) => {
						self.onCancel(data);
					},
					onError: (err) => {
						self.onError(err);
					},
				});

				// Render buttons
				this.paypalButtons.render('#giftflow-paypal-button-container').catch((err) => {
					console.error('PayPal buttons render error:', err);
				});

			} catch (error) {
				console.error('PayPal initialization error:', error);
			}

			// Listen for payment method changes
			const paymentMethodInputs = this.form.querySelectorAll('input[name="payment_method"]');
			paymentMethodInputs.forEach((input) => {
				input.addEventListener('change', () => {
					if (input.value === 'paypal') {
						// Show PayPal container
						const paypalContainer = this.form.querySelector('.donation-form__payment-method-description--paypal');
						if (paypalContainer) {
							paypalContainer.style.display = 'block';
						}
					} else {
						// Hide PayPal container
						const paypalContainer = this.form.querySelector('.donation-form__payment-method-description--paypal');
						if (paypalContainer) {
							paypalContainer.style.display = 'none';
						}
					}
				});
			});

			// Listen for form submission
			this.form.addEventListener('donationFormBeforeSubmit', async (e) => {
				const { self: formSelf, fields, resolve, reject } = e.detail;

				// If payment method is not PayPal, return
				if (fields?.payment_method && fields?.payment_method !== 'paypal') {
					resolve(null);
					return;
				}

				// PayPal payment is handled by the button click, so we just resolve
				// The actual payment happens in onApprove
				resolve(null);
			});
		}

    /**
     * Create PayPal order
     * 
     * @returns {Promise<string>} Order ID
     */
    async createOrder() {
      try {
        // Get form data
        const formData = new FormData(this.form);
        const donationAmount = formData.get('donation_amount') || 
                              this.formObject.fields?.donation_amount || 
                              '0';

        // Prepare request data with all donation information
        const requestData = {
          action: 'giftflow_paypal_create_order',
          nonce: giftflowPayPalDonation.nonce,
          amount: donationAmount,
          donor_name: formData.get('donor_name') || this.formObject.fields?.donor_name || '',
          donor_email: formData.get('donor_email') || this.formObject.fields?.donor_email || '',
          campaign_id: formData.get('campaign_id') || this.formObject.fields?.campaign_id || '',
          donation_type: formData.get('donation_type') || this.formObject.fields?.donation_type || '',
          recurring_interval: formData.get('recurring_interval') || this.formObject.fields?.recurring_interval || '',
          donor_message: formData.get('donor_message') || this.formObject.fields?.donor_message || '',
          anonymous_donation: formData.get('anonymous_donation') || this.formObject.fields?.anonymous_donation || '',
        };

        // Make AJAX request
        const response = await fetch(giftflowPayPalDonation.ajaxurl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams(requestData),
        });

        const result = await response.json();

        if (!result.success) {
          throw new Error(result.data?.message || 'Failed to create PayPal order');
        }

        return result.data.orderID;

      } catch (error) {
        console.error('PayPal create order error:', error);
        throw error;
      }
    }

    /**
     * Handle approved payment
     * 
     * @param {Object} data - PayPal approval data
     * @returns {Promise<void>}
     */
    async onApprove(data) {
      try {
        // Prepare request data (donation_id will be created after payment)
        const requestData = {
          action: 'giftflow_paypal_capture_order',
          nonce: giftflowPayPalDonation.nonce,
          orderID: data.orderID,
        };

        // Show processing message
        this.showProcessingMessage();

        // Make AJAX request to capture order
        const response = await fetch(giftflowPayPalDonation.ajaxurl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
          },
          body: new URLSearchParams(requestData),
        });

        const result = await response.json();

        if (!result.success) {
          throw new Error(result.data?.message || 'Failed to capture PayPal order');
        }

        // Payment successful - store donation_id if returned
        if (result.data?.donation_id) {
          // Store donation_id in form for potential use
          const donationIdInput = this.form.querySelector('input[name="donation_id"]');
          if (donationIdInput) {
            donationIdInput.value = result.data.donation_id;
          } else {
            // Create hidden input if it doesn't exist
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'donation_id';
            hiddenInput.value = result.data.donation_id;
            this.form.appendChild(hiddenInput);
          }
        }

        // Payment successful - trigger form submission
        this.showSuccessMessage();
        
        // Trigger form submission after a short delay
        // setTimeout(() => {
        //   if (this.formObject && typeof this.formObject.submit === 'function') {
        //     this.formObject.submit();
        //   } else {
        //     // Fallback: submit form directly
        //     this.form.submit();
        //   }
        // }, 1000);

      } catch (error) {
        console.error('PayPal capture error:', error);
        this.showErrorMessage(error.message || giftflowPayPalDonation.messages.error);
      }
    }

		/**
		 * Handle canceled payment
		 * 
		 * @param {Object} data - PayPal cancel data
		 * @returns {void}
		 */
		onCancel(data) {
			console.log('PayPal payment canceled:', data);
			this.showErrorMessage(giftflowPayPalDonation.messages.canceled);
		}

		/**
		 * Handle payment error
		 * 
		 * @param {Error} err - Error object
		 * @returns {void}
		 */
		onError(err) {
			console.error('PayPal error:', err);
			this.showErrorMessage(err.message || giftflowPayPalDonation.messages.error);
		}

		/**
		 * Show processing message
		 * 
		 * @returns {void}
		 */
		showProcessingMessage() {
			const container = document.getElementById('giftflow-paypal-button-container');
			if (container) {
				container.innerHTML = '<p style="text-align: center; padding: 20px;">' + 
					giftflowPayPalDonation.messages.processing + 
					'</p>';
			}
		}

		/**
		 * Show success message
		 * 
		 * @returns {void}
		 */
		showSuccessMessage() {
			const container = document.getElementById('giftflow-paypal-button-container');
			if (container) {
				container.innerHTML = '<p style="text-align: center; padding: 20px; color: green;">' + 
					'Payment successful!' + 
					'</p>';
			}
		}

		/**
		 * Show error message
		 * 
		 * @param {string} message - Error message
		 * @returns {void}
		 */
		showErrorMessage(message) {
			const container = document.getElementById('giftflow-paypal-button-container');
			if (container) {
				const errorDiv = document.createElement('div');
				errorDiv.className = 'paypal-error-message';
				errorDiv.style.cssText = 'text-align: center; padding: 15px; background: #fee; color: #c33; border: 1px solid #fcc; border-radius: 4px; margin: 10px 0;';
				errorDiv.textContent = message;
				
				container.innerHTML = '';
				container.appendChild(errorDiv);

				// Re-render buttons after error
				setTimeout(() => {
					if (this.paypalButtons) {
						this.paypalButtons.render('#giftflow-paypal-button-container').catch((err) => {
							console.error('PayPal buttons re-render error:', err);
						});
					}
				}, 2000);
			}
		}
	};

	// Initialize when form is loaded
	document.addEventListener('donationFormLoaded', (e) => {
		const { self, form } = e.detail;
		
		// Check if PayPal payment method exists
		const paypalInput = form.querySelector('input[name="payment_method"][value="paypal"]');
		if (paypalInput) {
			new PayPalDonation(form, self);
		}
	});

})(window);
