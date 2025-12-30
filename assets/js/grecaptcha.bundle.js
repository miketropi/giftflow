/******/ (() => { // webpackBootstrap
/*!*********************************!*\
  !*** ./assets/js/grecaptcha.js ***!
  \*********************************/
/**
 * Google reCAPTCHA
 * 
 * @param {Object} w - Window object.
 */

/**
 * Google reCAPTCHA site key.
 */
var _giftflowGoogleRecapt = giftflowGoogleRecaptcha,
  site_key = _giftflowGoogleRecapt.site_key;

/**
 * Ready function.
 */
grecaptcha.ready(function () {
  // listen donation form loaded.
  document.addEventListener('donationFormLoaded', function (e) {
    // get self and form.
    var _e$detail = e.detail,
      self = _e$detail.self,
      form = _e$detail.form;

    // execute grecaptcha.
    grecaptcha.execute(site_key, {
      action: 'submit'
    }).then(function (token) {
      form.querySelector('input[name="recaptcha_token"]').value = token;
      self.onSetField('recaptcha_token', token);
    });
  });
});
/******/ })()
;