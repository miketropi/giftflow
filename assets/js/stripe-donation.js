/**
 * Stripe Donation - Payment Intents & Payment Methods
 * 
 * Modern Stripe integration using Payment Intents API for
 * enhanced security and SCA (Strong Customer Authentication) support.
 */
import {loadStripe} from '@stripe/stripe-js';
const STRIPE_PUBLIC_KEY = giftflowStripeDonation.stripe_publishable_key;

((w) => {
  'use strict';

  /**
   * Stripe Donation Class
   * Handles payment processing using Stripe Payment Intents
   * with support for Card payments.
   * 
   * Apple Pay and Google Pay support is available in GiftFlow Pro.
   */
  const StripeDonation = class { 

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
      this.stripe = null;
      this.cardElement = null;
      this.paymentRequest = null;
      this.paymentRequestButton = null;
      this.selectedPaymentMethod = null; // Store payment method from wallet
      this.stripeElements = null;

      this.init();
    }

    getSelf() {
      return this;
    }

    async init() {
      const self = this;
      
      // Load Stripe.js
      this.stripe = await loadStripe(STRIPE_PUBLIC_KEY);
      
      if (!this.stripe) {
        console.error('Failed to load Stripe.js');
        return;
      }

      // Create Elements instance
      const appearance = {
        theme: 'stripe',
        variables: {
          colorPrimary: '#0570de',
          colorBackground: '#ffffff',
          colorText: '#30313d',
          colorDanger: '#df1b41',
          fontFamily: 'system-ui, sans-serif',
          spacingUnit: '4px',
          borderRadius: '4px',
        },
      };

      this.stripeElements = this.stripe.elements({ appearance });

      // Create Card Element
      this.cardElement = this.stripeElements.create('card', {
        hidePostalCode: true,
        style: {
          base: {
            fontSize: '16px',
            color: '#32325d',
            fontFamily: 'system-ui, -apple-system, sans-serif',
            '::placeholder': {
              color: '#aab7c4',
            },
          },
          invalid: {
            color: '#fa755a',
            iconColor: '#fa755a',
          },
        },
      });

      // Get DOM elements
      const $element = this.form.querySelector('#STRIPE-CARD-ELEMENT');
      const $wrapperField = $element.closest('.donation-form__field');
      const $validateWrapper = $wrapperField;
      const $errorMessage = $wrapperField.querySelector('.custom-error-message .custom-error-message-text');
      
      // Mount the Card Element
      this.cardElement.mount($element);

      // Handle real-time validation
      this.cardElement.on('change', (event) => {
        if (event.complete) {
          $validateWrapper.dataset.customValidateStatus = 'true';
          $validateWrapper.classList.remove('error', 'custom-error');
          $errorMessage.textContent = '';
        } else {
          $validateWrapper.dataset.customValidateStatus = 'false';

          if (event.error) {
            $validateWrapper.classList.add('error', 'custom-error');
            $errorMessage.textContent = event.error.message;
          } else {
            $validateWrapper.classList.remove('error', 'custom-error');
            $errorMessage.textContent = '';
          }
        }
      });

      // Emit event for Pro plugins to hook into (e.g., Apple Pay / Google Pay)
      await self.formObject.eventHub.emit('stripeDonationInitialized', {
        self: self,
      });

      // Handle form submission
      self.formObject.eventHub.on('donationFormBeforeSubmit', async ({ self: formSelf, fields }) => {
        if (fields?.payment_method && fields?.payment_method !== 'stripe') {
          return;
        }

        $validateWrapper.classList.remove('error', 'custom-error');
        $errorMessage.textContent = '';

        try {
          let paymentMethod;

          // Check if payment method was already created via Apple Pay / Google Pay
          if (this.getSelf().selectedPaymentMethod) {
            paymentMethod = this.getSelf().selectedPaymentMethod;
            console.log('Using wallet payment method:', paymentMethod.id);
          } else {
            // Create Payment Method from card element
            const result = await this.getSelf().stripe.createPaymentMethod({
              type: 'card',
              card: this.getSelf().cardElement,
              billing_details: {
                name: fields.card_name || fields.donor_name || '',
                email: fields.donor_email || '',
              },
            });

            if (result.error) {
              $validateWrapper.classList.add('error', 'custom-error');
              $errorMessage.textContent = result.error.message;
              throw result.error;
            }

            paymentMethod = result.paymentMethod;
          }

          // Set Payment Method ID for backend processing
          formSelf.onSetField('payment_method_id', paymentMethod.id);

          return paymentMethod;
        } catch (error) {
          console.error('Payment Method creation failed:', error);
          throw error;
        }
      });

      // Handle post-submission (for 3D Secure)
      self.formObject.eventHub.on('donationFormAfterSubmit', async ({ response, self: formSelf }) => {
        if (!response || !response.data) {
          return;
        }

        const paymentData = response.data;

        // Handle requires_action status (3D Secure / SCA)
        if (paymentData.requires_action && paymentData.client_secret) {
          try {
            const { error: confirmError, paymentIntent } = await this.getSelf().stripe.confirmCardPayment(
              paymentData.client_secret
            );

            if (confirmError) {
              // Display error to user
              console.error('Payment confirmation failed:', confirmError);
              
              // You can trigger a custom event or update UI here
              formSelf.eventHub.emit('paymentConfirmationFailed', { 
                error: confirmError.message 
              });

              throw confirmError;
            }

            if (paymentIntent && paymentIntent.status === 'succeeded') {
              // Payment succeeded after 3D Secure
              formSelf.eventHub.emit('paymentConfirmed', { 
                paymentIntent 
              });

              // Optionally reload or redirect
              window.location.href = paymentData.return_url || giftflowStripeDonation.return_url;
            }
          } catch (error) {
            console.error('3D Secure confirmation error:', error);
            throw error;
          }
        }
      });
    }
  }

  // Initialize when donation form is loaded
  document.addEventListener('donationFormLoaded', (e) => {
    const { self, form } = e.detail;
    new StripeDonation(form, self);
  });

})(window);