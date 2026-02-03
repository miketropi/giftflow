import { applySlideEffect, validateValue } from './util/helpers';
import { AsyncEventHub } from './util/async-event-hub';

/**
 * Donation Form
 * 
 * @param {Object} w - Window object.
 */
(async (w) => {
	'use strict';

	// make donationForm class.
	const donationForm = class {

		/**
		 * Constructor
		 * 
		 * @param {Object} donationForm - Donation form element.
		 * @param {Object} options - Options.
		 * @returns {void}
		 */
    constructor(donationForm, options) {
			this.fields = {};
			this.form = donationForm;
			this.options = options;
			this.totalSteps = this.form.querySelectorAll('.donation-form__step-panel').length;
			this.currentStep = 1;
			this.eventHub = new AsyncEventHub();

			this.init(donationForm, options);
    }
    
    init(donationForm, options) {
			const self = this;

			// set default payment method selected.
			let methodSelected = this.form.querySelector(`input[name="payment_method"][value="${options.paymentMethodSelected}"]`);
			if (methodSelected) {
				methodSelected.checked = true;
			}

			this.setInitFields(donationForm);
			this.onListenerFormFieldUpdate();

			// create event trigger on load form to document.
			document.dispatchEvent(new CustomEvent('donationFormLoaded', {
				detail: {
					self: self,
					form: self.form
				}
			}));

			// on change amount field.
			this.form.addEventListener('input', async (event) => {
				if (event.target.name === 'donation_amount') {
					this.onUpdateAmountField(event.target.value);
				}

				if (event.target.name === 'payment_method') {
					this.onChangePaymentMethod(event.target.value);
				}
			});

			// on click Preset Amount.
			this.form.addEventListener('click', (event) => {
				if (event.target.classList.contains('donation-form__preset-amount')) {
					this.onClickPresetAmount(event);
				}
			});

			// on click next step.
			this.form.addEventListener('click', (event) => {
				// is contains class and is element had class donation-form__button--next.
				const isNextButton = event.target.classList.contains('donation-form__button--next') && event.target.tagName === 'BUTTON';
				if (isNextButton) {
					const stepPass = this.onValidateFieldsCurrentStep();

					if (stepPass) {
						this.onNextStep();
					}
				}
			});

			// on click previous step.
			this.form.addEventListener('click', (event) => {
				// is contains class and is element had class donation-form__button--back.
				const isBackButton = event.target.classList.contains('donation-form__button--back') && event.target.tagName === 'BUTTON';

				if (isBackButton) {
					this.onPreviousStep();
				}
			});

			// disable enter key submit form.
			this.form.addEventListener('keydown', (e) => {
				if (e.key === 'Enter' && ['INPUT', 'SELECT'].includes(e.target.tagName)) {
					e.preventDefault();
					return false;
				}
			});

			// on submit form.
			this.form.addEventListener('submit', (event) => {
				event.preventDefault();
				event.stopPropagation();

				// submit form.
				this.onSubmitForm();
			});

    }

		getFields() {
			return this.fields;
		}

		onSetLoading(status) {
			const self = this;

			self.form.classList.toggle('gfw-elem-loading-spinner', status);

			self.form.querySelector('.donation-form__button--submit').classList.toggle('loading', status);
			self.form.querySelector('.donation-form__button--submit').disabled = status;
		}

		async onSubmitForm() {
			const self = this;
			self.onSetLoading(true);

			// validate fields.
			const pass = self.onValidateFieldsCurrentStep();
			// console.log('pass', pass);
			if (!pass) {
				self.onSetLoading(false);
				return;
			}

			// do hooks before submit support async function after submit.
			// await self.onDoHooks();
			try {
				// await self.onDoHooks();
				await self.eventHub.emit('donationFormBeforeSubmit', {
					self: self,
					fields: self.getFields()
				});
			} catch (error) {
				console.error('Error in onDoHooks:', error);
				self.onSetLoading(false);
				return;
			}

			// send data
			// console.log('getFields', self.getFields());
			const response = await self.onSendData(self.getFields());
			// console.log('onSubmitForm', response);

			if(!response || !response.success) {
				// show error section.
				self.form.querySelector('.donation-form__step-panel.is-active').classList.remove('is-active');
				self.form.querySelector('#donation-error').classList.add('is-active');

				// set error message.
				const errorMessage = self.form.querySelector('#donation-error .donation-form__error-message');
				if (errorMessage) {
					errorMessage.innerHTML = `
						<h3 class="donation-form__error-title">Error</h3>
						<p class="donation-form__error-text">${response?.data?.message || 'An error occurred. Please try again.'}</p>
					`;
				}

				self.onSetLoading(false);
				return;
			}

			if (response && response.success) {
				// show thank you section.
				self.form.querySelector('.donation-form__step-panel.is-active').classList.remove('is-active');
				self.form.querySelector('#donation-thank-you').classList.add('is-active');
				self.onSetLoading(false);
				return;
			}
			

			self.onSetLoading(false);
		}

		onShowThankYouSection() {
			const self = this;
			self.form.querySelector('.donation-form__step-panel.is-active').classList.remove('is-active');
			self.form.querySelector('#donation-thank-you').classList.add('is-active');
		}

		onShowErrorSection(message = null) {
			const self = this;
			self.form.querySelector('.donation-form__step-panel.is-active').classList.remove('is-active');
			const errorPanel = self.form.querySelector('#donation-error');
			errorPanel.classList.add('is-active');
			if (message) {
				const errorMessageEl = errorPanel.querySelector('.donation-form__error-message');
				if (errorMessageEl) {
					errorMessageEl.innerHTML = `
						<h3 class="donation-form__error-title">Error</h3>
						<p class="donation-form__error-text">${message}</p>
					`;
				}
			}
		}

		async onSendData(data) {
			let ajaxurl = `${window.giftflowDonationForms.ajaxurl}?action=giftflow_donation_form&wp_nonce=${data.wp_nonce}`;

			const response = await fetch(ajaxurl, {
				method: 'POST',
				body: JSON.stringify(data),
				headers: {
					'Content-Type': 'application/json'
				}
			}).then(response => response.json())
				.catch(error => error);

			return response;
		}

		async onDoHooks() {
			const self = this;
			
			// allow developer add hooks from outside support async function and return promise.
			return new Promise((resolve, reject) => {
				self.form.dispatchEvent(new CustomEvent('donationFormBeforeSubmit', {
					detail: {
						self: self,
						fields: self.fields,
						resolve,
						reject
					}
				}));
			});
		}

		onSetField(name, value) {
			this.fields[name] = value;
			// console.log('onSetField', name, value, this.fields);
		}

		onNextStep() {
			const self = this;
			self.currentStep++;

			// nav.
			self.form.querySelector('.donation-form__step-link.is-active').classList.remove('is-active');
			self.form.querySelector(`.donation-form__step-item.nav-step-${self.currentStep} .donation-form__step-link`).classList.add('is-active');

			// panel.
			self.form.querySelector('.donation-form__step-panel.is-active').classList.remove('is-active');
			self.form.querySelector('.donation-form__step-panel.step-' + self.currentStep).classList.add('is-active');

			// change payment method.
			this.onChangePaymentMethod(self.fields.payment_method);
		}

		onPreviousStep() {
			const self = this;
			self.currentStep--;

			// nav.
			self.form.querySelector('.donation-form__step-link.is-active').classList.remove('is-active');
			self.form.querySelector(`.donation-form__step-item.nav-step-${self.currentStep} .donation-form__step-link`).classList.add('is-active');

			// panel.
			self.form.querySelector('.donation-form__step-panel.is-active').classList.remove('is-active');
			self.form.querySelector('.donation-form__step-panel.step-' + self.currentStep).classList.add('is-active');
		}

		setInitFields(donationForm) {
			const self = this;
			const fields = donationForm.querySelectorAll('input[name]');
			fields.forEach((field) => {

				let value = field.value;

				// validate event.target is checkbox field.
				if (field.type === 'checkbox') {
					value = field.checked;
				}

				// validate event.target is radio field.
				if (field.type === 'radio') {
					// get field name.
					const fieldName = field.name;
					// const fieldValue = field.value;
					value = self.form.querySelector(`input[name="${fieldName}"]:checked`).value;
				}

				this.fields[field.name] = value;
			});
		}

		onListenerFormFieldUpdate() {
			const self = this;
			this.form.addEventListener('change', async (event) => {
				self.fields[event.target.name] = event.target.value;
				let value = event.target.value;

				// validate event.target is checkbox field.
				if (event.target.type === 'checkbox') {
					value = event.target.checked;
				}

				// validate event.target is radio field.
				if (event.target.type === 'radio') {
					const fieldName = event.target.name;
					value = self.form.querySelector(`input[name="${fieldName}"]:checked`).value;
				}

				// emit event donationAmountChanged if event.target.name is donation_amount.
				if (event.target.name === 'donation_amount') {
					await this.eventHub.emit('donationAmountChanged', {
						amount: event.target.value
					});
				}

				// update UI by field.
				self.onUpdateUIByField(event.target.name, value);
			});
		}

		onChangePaymentMethod(methodId) {
			const paymentMethodDescription = this.form.querySelector(`.donation-form__payment-method-item.payment-method-${methodId}`);
			this.form.querySelectorAll(`.donation-form__payment-method-item:not(.payment-method-${methodId})`).forEach((paymentMethodDescription) => {
				// remove class is-active.
				paymentMethodDescription.classList.remove('is-active')
				paymentMethodDescription.querySelector('.donation-form__payment-method-description').classList.add('__skip-validate-field-inner'); 
				applySlideEffect(paymentMethodDescription.querySelector('.donation-form__payment-method-description'), 'slideup');
			});

			if (paymentMethodDescription) {
				paymentMethodDescription.classList.add('is-active')
				paymentMethodDescription.querySelector('.donation-form__payment-method-description').classList.remove('__skip-validate-field-inner'); 
				applySlideEffect(paymentMethodDescription.querySelector('.donation-form__payment-method-description'), 'slidedown', 300, 'grid');
			}
		}		

		onUpdateUIByField(field, value) {

			const inputField = this.form.querySelector(`input[name="${field}"]`);
			if (!inputField) {
				return;
			}

			const wrapperField = inputField.closest('.donation-form__field');
			if (!wrapperField) {
				const type = inputField.dataset.validate;
				const extraData = inputField.dataset.extraData ? JSON.parse(inputField.dataset.extraData) : null;
				
				if(!this.onValidateValue(type, value, extraData)) {
					inputField.classList.add('error'); 
					this.onUpdateOutputField(field, '');
				} else {
					inputField.classList.remove('error');
					this.onUpdateOutputField(field, value);
				}

				return;
			}

			if (inputField.dataset.validate) {
				const extraData = inputField.dataset.extraData ? JSON.parse(inputField.dataset.extraData) : null;
				const pass = this.onValidateValue(inputField.dataset.validate, value, extraData);
				if (!pass) {
					// inputField.classList.add('error');
					wrapperField.classList.add('error');
					this.onUpdateOutputField(field, '');
				} else {
					// inputField.classList.remove('error');
					wrapperField.classList.remove('error');
					this.onUpdateOutputField(field, value);
				}
			}
		}

		onUpdateOutputField(field, value) {
			const outputField = this.form.querySelectorAll(`[data-output="${field}"]`);
			if (!outputField || outputField.length === 0) {
				return;
			}

			// if outputField is array, loop through it.
			if (outputField.length > 1) {
				outputField.forEach((output) => {
					const formatTemplate = output.dataset.formatTemplate;
					let __v = value;
					if (formatTemplate) {
						__v = formatTemplate.replace('{{value}}', value);
					}

					// update output value.
					this.updateOutputValue(output, __v);
				});
				return;
			}
		}

		updateOutputValue(output, value) {
			if (output.tagName === 'INPUT' || output.tagName === 'TEXTAREA') {
				// if output is input or textarea, set value.
				output.value = value;
				output.setAttribute('value', value);
			} else {
				// if output is not input or textarea, set text content.
				output.textContent = value;
			}
		}

		// on click Preset Amount
		onClickPresetAmount(event) {
			event.preventDefault();
			event.stopPropagation();

			const self = this;
			const amount = event.target.dataset.amount;
			self.form.querySelector('input[name="donation_amount"]').value = amount;
			self.form.querySelector('input[name="donation_amount"]').setAttribute('value', amount);

			const changeEvent = new Event('change', { bubbles: true });
			self.form.querySelector('input[name="donation_amount"]').dispatchEvent(changeEvent);
			
			// Update UI by field
			this.onUpdateUIByField('donation_amount', amount);

			event.target.classList.add('active')
			self.form.querySelectorAll('.donation-form__preset-amount').forEach((presetAmount) => {
				if (presetAmount !== event.target) {
					presetAmount.classList.remove('active');
				}
			});
		}

		// on update amout field
		onUpdateAmountField(value) {
			// remove active
			this.form.querySelectorAll('.donation-form__preset-amount').forEach((presetAmount) => {
				presetAmount.classList.remove('active');
			});
		}

		onValidateFieldsCurrentStep() {
			const self = this;
			const currentStepWrapper = this.form.querySelector('.donation-form__step-panel.is-active');
			let pass = true;
			
			if (!currentStepWrapper) {
				return;
			}

			// get fields.
			const fields = currentStepWrapper.querySelectorAll('input[name][data-validate]');
			
			// filter fields by skip validate field inner.
			[...fields]
				.filter( f => {
					const wrapperBySkipValidate = f.closest('.__skip-validate-field-inner')
					if(wrapperBySkipValidate) {
						return false;
					}

					return true;
				})
				.forEach((field) => {
					const fieldName = field.name;
					const fieldValue = field.value;
					const fieldValidate = field.dataset.validate;
					
					const extraData = field.dataset.extraData ? JSON.parse(field.dataset.extraData) : null;
					if(!this.onValidateValue(fieldValidate, fieldValue, extraData)) {
						pass = false;
					}
					
					self.onUpdateUIByField(fieldName, fieldValue);
				});

			// get fields by custom validate.
			[...currentStepWrapper.querySelectorAll('[data-custom-validate="true"]')]
				.filter(s => {
					const wrapperBySkipValidate = s.closest('.__skip-validate-field-inner')
					if(wrapperBySkipValidate) {
						return false;
					}

					return true;
				})
				.forEach((field) => {
					const status = field.dataset.customValidateStatus;

					if(status === 'false') {
						pass = false;

						// add error class to field
						field.classList.add('error', 'custom-error');
					}
				});

			return pass;
		}
		
		/**
		 * Validate field by type
		 * 
		 * @param {string} type - Validation type.
		 * @param {any} value - Value to validate.
		 * @param {Object|null} extraData - Extra data for some validations (min/max).
		 * @returns {boolean} - True if passes all validations, false otherwise.
		 */
		onValidateValue(type, value, extraData = null) {
			return validateValue(type, value, extraData);
		}
	}

	w.donationForm_Class = donationForm;

	// make custom event trigger donation form and how to use it	
	/**
	* Custom event to trigger donation form initialization
	* 
	* Usage:
	* document.dispatchEvent(new CustomEvent('initDonationForm', {
	*   detail: {
	*     formSelector: '.my-custom-donation-form', // Optional: target specific forms
	*     options: {} // Optional: pass configuration options
	*   }
	* }));
	*/
	document.addEventListener('initDonationForm', (event) => {
		const { formSelector, options } = event.detail || {};
		
		if (formSelector) {
			// Initialize specific forms matching the selector
			document.querySelectorAll(formSelector).forEach((form) => {
				new donationForm(form, options);
			});
		} else {
			// Initialize all donation forms if no selector provided
			document.querySelectorAll('.donation-form').forEach((form) => {
				new donationForm(form, options);
			});
		}
		
		// console.log('Donation forms initialized via custom event');
	});

	const initDonationForm = (formSelector, options) => {
		document.dispatchEvent(new CustomEvent('initDonationForm', {
			detail: {
				formSelector,
				options
			}
		}));
	}

	w.initDonationForm = initDonationForm;

	// dom loaded
	document.addEventListener('DOMContentLoaded', () => {
		// initialize all donation forms
		initDonationForm('.donation-form', {
			paymentMethodSelected: 'stripe',
		});
	});

})(window)