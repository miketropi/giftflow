/**
 * Google reCAPTCHA
 * 
 * @param {Object} w - Window object.
 */

/**
 * Google reCAPTCHA site key.
 */
const { site_key } = giftflowGoogleRecaptcha;

/**
 * Ready function.
 */
grecaptcha.ready(function () {

  // listen donation form loaded.
  document.addEventListener('donationFormLoaded', (e) => {
    // get self and form.
    const { self, form } = e.detail;

    // execute grecaptcha.
    grecaptcha.execute(site_key, { action: 'submit' })
      .then(function (token) {
        form.querySelector('input[name="recaptcha_token"]').value = token;
        self.onSetField('recaptcha_token', token);
      });
  }); 
});