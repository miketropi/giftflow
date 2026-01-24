/**
 * Stripe Donation - Payment Intents & Payment Methods
 * 
 * Modern Stripe integration using Payment Intents API for
 * enhanced security and SCA (Strong Customer Authentication) support.
 * Includes Apple Pay, Google Pay, and other digital wallet support.
 */
import {loadStripe} from '@stripe/stripe-js';
const STRIPE_PUBLIC_KEY = giftflowStripeDonation.stripe_publishable_key;

((w) => {
  'use strict';

  /**
   * Stripe Donation Class
   * Handles payment processing using Stripe Payment Intents
   * with support for Card, Apple Pay, and Google Pay
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

      // Initialize Apple Pay / Google Pay
      await this.initPaymentRequestButton();

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
              formSelf.eventHub.trigger('paymentConfirmationFailed', { 
                error: confirmError.message 
              });

              throw confirmError;
            }

            if (paymentIntent && paymentIntent.status === 'succeeded') {
              // Payment succeeded after 3D Secure
              formSelf.eventHub.trigger('paymentConfirmed', { 
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

    /**
     * Initialize Payment Request Button (Apple Pay / Google Pay)
     * 
     * @returns {Promise<void>}
     */
    async initPaymentRequestButton() {
      const self = this;

      // Get current donation amount from form
      const getDonationAmount = () => {
        const fields = self.formObject.fields;
        const amount = parseFloat(fields.donation_amount || 0);
        return Math.round(amount * 100); // Convert to cents
      };

      // Get currency from settings or default to USD
      const currency = (giftflowStripeDonation.currency || 'usd').toLowerCase();
      const countryCode = giftflowStripeDonation.country || 'US';

      // Create Payment Request
      this.paymentRequest = this.stripe.paymentRequest({
        country: countryCode,
        currency: currency,
        total: {
          label: giftflowStripeDonation.site_name || 'Donation',
          amount: getDonationAmount(),
        },
        requestPayerName: true,
        requestPayerEmail: true,
      });

      // Check if Payment Request is available (Apple Pay / Google Pay)
      const canMakePaymentResult = await this.paymentRequest.canMakePayment();

      if (!canMakePaymentResult) {
        console.log('Apple Pay / Google Pay not available on this device');
        return;
      }

      console.log('Payment Request available:', canMakePaymentResult);

      // support only for Apple Pay & Google pay canMakePaymentResult
      if (!canMakePaymentResult.applePay && !canMakePaymentResult.googlePay) {
        console.log('Apple Pay / Google Pay not available on this device');
        return;
      }

      // Create and mount Payment Request Button
      const elements = this.stripe.elements();
      this.paymentRequestButton = elements.create('paymentRequestButton', {
        paymentRequest: this.paymentRequest,
        style: {
          paymentRequestButton: {
            type: 'donate', // Can be 'default', 'donate', 'buy'
            theme: 'dark', // Can be 'dark', 'light', or 'light-outline'
            height: '48px',
          },
        },
      });

      // Check if button can be mounted
      const result = await this.paymentRequest.canMakePayment();
      if (result) {
        // Find or create container for Payment Request Button
        let $prButtonContainer = this.form.querySelector('#payment-request-button');
        
        if (!$prButtonContainer) {
          // Create container if it doesn't exist
          const $cardElement = this.form.querySelector('#STRIPE-CARD-ELEMENT');
          const $cardWrapper = $cardElement.closest('.donation-form__payment-method-description');
          
          $prButtonContainer = document.createElement('div');
          $prButtonContainer.id = 'payment-request-button';
          $prButtonContainer.className = 'payment-request-button-wrapper';
          
          // Insert before card fields
          const $cardNameField = $cardWrapper.querySelector('.donation-form__card-fields');
          if ($cardNameField) {
            $cardWrapper.insertBefore($prButtonContainer, $cardNameField);
          } else {
            $cardWrapper.insertBefore($prButtonContainer, $cardWrapper.firstChild);
          }

          // Add separator
          const $separator = document.createElement('div');
          $separator.className = 'payment-request-separator';
          $separator.innerHTML = '<span>or pay with card</span>';
          $cardWrapper.insertBefore($separator, $cardNameField);
        }

        // Mount the button
        this.paymentRequestButton.mount('#payment-request-button');

        console.log('Payment Request Button mounted successfully');
      }

      // Listen for amount changes and update payment request
      self.formObject.eventHub.on('donationAmountChanged', ({ amount }) => {
        if (this.paymentRequest) {
          this.paymentRequest.update({
            total: {
              label: giftflowStripeDonation.site_name || 'Donation',
              amount: Math.round(parseFloat(amount) * 100),
            },
          });
        }
      });

      // Handle payment method creation from wallet
      this.paymentRequest.on('paymentmethod', async (ev) => {
        console.log('Payment method received from wallet:', ev.paymentMethod);

        // Store the payment method
        self.selectedPaymentMethod = ev.paymentMethod;

        // Update form fields with payer information
        if (ev.payerName) {
          // self.formObject.onSetField('donor_name', ev.payerName);
          self.formObject.onSetField('card_name', ev.payerName);
        }

        if (ev.payerEmail) {
          self.formObject.onSetField('donor_email', ev.payerEmail);
        }

        try {
          // Trigger form submission programmatically
          // The form will use the stored payment method
          const submitButton = self.form.querySelector('[type="submit"]');
          
          if (submitButton) {
            // add .__skip-validate-field-inner
            self.form.querySelector('#STRIPE-CARD-ELEMENT')
              .closest('.donation-form__card-fields')
              .classList.add('__skip-validate-field-inner');

            // Store that we're using wallet payment
            self.formObject.onSetField('using_wallet_payment', 'true');
            
            // Trigger the form submission
            submitButton.click();

            // Complete the payment (success will be handled by webhook/backend)
            ev.complete('success');
          } else {
            throw new Error('Submit button not found');
          }
        } catch (error) {
          console.error('Wallet payment processing failed:', error);
          ev.complete('fail');

          // remove .__skip-validate-field-inner
          self.form.querySelector('#STRIPE-CARD-ELEMENT')
            .closest('.donation-form__card-fields')
            .classList.remove('__skip-validate-field-inner');
          
          // Clear the stored payment method
          self.selectedPaymentMethod = null;
        }
      });

      // Handle errors
      this.paymentRequest.on('cancel', () => {
        console.log('Payment Request canceled by user');
        self.selectedPaymentMethod = null;
      });
    }
  }

  // Initialize when donation form is loaded
  document.addEventListener('donationFormLoaded', (e) => {
    const { self, form } = e.detail;
    new StripeDonation(form, self);
  });

})(window);