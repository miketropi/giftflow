/**
 * Stripe Donation 
 * 
 */
import {loadStripe} from '@stripe/stripe-js';
const STRIPE_PUBLIC_KEY = giftflowStripeDonation.stripe_publishable_key;

((w) => {
  'use strict';

  // make stripeDonation class
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

      this.init();
    }

    getSelf() {
      return this;
    }

    async init() {
      const self = this;
      this.stripe = await loadStripe(STRIPE_PUBLIC_KEY);
      this.stripeElements = this.stripe.elements();

      const cardElement = this.stripeElements.create('card');
      const $element = this.form.querySelector('#STRIPE-CARD-ELEMENT');
      const $wrapper = $element.closest('.donation-form__payment-method-description');
      const $wrapperField = $element.closest('.donation-form__field');
      const $validateWrapper = $wrapperField; //$wrapperField.querySelector('[data-custom-validate="true"]');
      const $errorMessage = $wrapperField.querySelector('.custom-error-message .custom-error-message-text');
      
      cardElement.mount($element);

      cardElement.on('change', async (event) => {
        // console.log('event', event);

        if (event.complete) {
          // console.log(self.formObject.fields);
          // console.log('Card information is complete.');
          
          $validateWrapper.dataset.customValidateStatus = 'true';
          $validateWrapper.classList.remove('error', 'custom-error');
        } else {
          // console.log('Card information is incomplete.');
          $validateWrapper.dataset.customValidateStatus = 'false';

          // add error message
          if(event.error) {
            $validateWrapper.classList.add('error', 'custom-error');
            $errorMessage.textContent = event.error.message;
          } else {
            $validateWrapper.classList.remove('error', 'custom-error');
            $errorMessage.textContent = '';
          }
        }
      });

      // add event listener to form
      this.form.addEventListener('donationFormBeforeSubmit', async (e) => {
        const { self, fields, resolve, reject } = e.detail;

        // if payment method is not stripe, return.
        if(fields?.payment_method && fields?.payment_method !== 'stripe') {
          resolve(null);
          return;
        }

        $validateWrapper.classList.remove('error', 'custom-error');
        $errorMessage.textContent = '';

        // create token.
        const { token, error } = await this.getSelf().stripe.createToken(cardElement);
        if(error) {
          $validateWrapper.classList.add('error', 'custom-error');
          $errorMessage.textContent = error.message;
          reject(error);
          return;
        }

        // set token.
        self.onSetField('stripe_token', token.id);
        resolve(token);
        return;
      });
    }
  }

  document.addEventListener('donationFormLoaded', (e) => {
    const { self, form } = e.detail;
    new StripeDonation(form, self);
  });

})(window);