/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _asyncToGenerator)
/* harmony export */ });
function asyncGeneratorStep(n, t, e, r, o, a, c) {
  try {
    var i = n[a](c),
      u = i.value;
  } catch (n) {
    return void e(n);
  }
  i.done ? t(u) : Promise.resolve(u).then(r, o);
}
function _asyncToGenerator(n) {
  return function () {
    var t = this,
      e = arguments;
    return new Promise(function (r, o) {
      var a = n.apply(t, e);
      function _next(n) {
        asyncGeneratorStep(a, r, o, _next, _throw, "next", n);
      }
      function _throw(n) {
        asyncGeneratorStep(a, r, o, _next, _throw, "throw", n);
      }
      _next(void 0);
    });
  };
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/classCallCheck.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _classCallCheck)
/* harmony export */ });
function _classCallCheck(a, n) {
  if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function");
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/createClass.js":
/*!****************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/createClass.js ***!
  \****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _createClass)
/* harmony export */ });
/* harmony import */ var _toPropertyKey_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./toPropertyKey.js */ "./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js");

function _defineProperties(e, r) {
  for (var t = 0; t < r.length; t++) {
    var o = r[t];
    o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, (0,_toPropertyKey_js__WEBPACK_IMPORTED_MODULE_0__["default"])(o.key), o);
  }
}
function _createClass(e, r, t) {
  return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", {
    writable: !1
  }), e;
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/toPrimitive.js":
/*!****************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/toPrimitive.js ***!
  \****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ toPrimitive)
/* harmony export */ });
/* harmony import */ var _typeof_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./typeof.js */ "./node_modules/@babel/runtime/helpers/esm/typeof.js");

function toPrimitive(t, r) {
  if ("object" != (0,_typeof_js__WEBPACK_IMPORTED_MODULE_0__["default"])(t) || !t) return t;
  var e = t[Symbol.toPrimitive];
  if (void 0 !== e) {
    var i = e.call(t, r || "default");
    if ("object" != (0,_typeof_js__WEBPACK_IMPORTED_MODULE_0__["default"])(i)) return i;
    throw new TypeError("@@toPrimitive must return a primitive value.");
  }
  return ("string" === r ? String : Number)(t);
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js ***!
  \******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ toPropertyKey)
/* harmony export */ });
/* harmony import */ var _typeof_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./typeof.js */ "./node_modules/@babel/runtime/helpers/esm/typeof.js");
/* harmony import */ var _toPrimitive_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./toPrimitive.js */ "./node_modules/@babel/runtime/helpers/esm/toPrimitive.js");


function toPropertyKey(t) {
  var i = (0,_toPrimitive_js__WEBPACK_IMPORTED_MODULE_1__["default"])(t, "string");
  return "symbol" == (0,_typeof_js__WEBPACK_IMPORTED_MODULE_0__["default"])(i) ? i : i + "";
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/typeof.js":
/*!***********************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/typeof.js ***!
  \***********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _typeof)
/* harmony export */ });
function _typeof(o) {
  "@babel/helpers - typeof";

  return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) {
    return typeof o;
  } : function (o) {
    return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o;
  }, _typeof(o);
}


/***/ }),

/***/ "./node_modules/@stripe/stripe-js/dist/index.mjs":
/*!*******************************************************!*\
  !*** ./node_modules/@stripe/stripe-js/dist/index.mjs ***!
  \*******************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   loadStripe: () => (/* binding */ loadStripe)
/* harmony export */ });
var RELEASE_TRAIN = 'basil';

var runtimeVersionToUrlVersion = function runtimeVersionToUrlVersion(version) {
  return version === 3 ? 'v3' : version;
};

var ORIGIN = 'https://js.stripe.com';
var STRIPE_JS_URL = "".concat(ORIGIN, "/").concat(RELEASE_TRAIN, "/stripe.js");
var V3_URL_REGEX = /^https:\/\/js\.stripe\.com\/v3\/?(\?.*)?$/;
var STRIPE_JS_URL_REGEX = /^https:\/\/js\.stripe\.com\/(v3|[a-z]+)\/stripe\.js(\?.*)?$/;
var EXISTING_SCRIPT_MESSAGE = 'loadStripe.setLoadParameters was called but an existing Stripe.js script already exists in the document; existing script parameters will be used';

var isStripeJSURL = function isStripeJSURL(url) {
  return V3_URL_REGEX.test(url) || STRIPE_JS_URL_REGEX.test(url);
};

var findScript = function findScript() {
  var scripts = document.querySelectorAll("script[src^=\"".concat(ORIGIN, "\"]"));

  for (var i = 0; i < scripts.length; i++) {
    var script = scripts[i];

    if (!isStripeJSURL(script.src)) {
      continue;
    }

    return script;
  }

  return null;
};

var injectScript = function injectScript(params) {
  var queryString = params && !params.advancedFraudSignals ? '?advancedFraudSignals=false' : '';
  var script = document.createElement('script');
  script.src = "".concat(STRIPE_JS_URL).concat(queryString);
  var headOrBody = document.head || document.body;

  if (!headOrBody) {
    throw new Error('Expected document.body not to be null. Stripe.js requires a <body> element.');
  }

  headOrBody.appendChild(script);
  return script;
};

var registerWrapper = function registerWrapper(stripe, startTime) {
  if (!stripe || !stripe._registerWrapper) {
    return;
  }

  stripe._registerWrapper({
    name: 'stripe-js',
    version: "7.8.0",
    startTime: startTime
  });
};

var stripePromise$1 = null;
var onErrorListener = null;
var onLoadListener = null;

var onError = function onError(reject) {
  return function (cause) {
    reject(new Error('Failed to load Stripe.js', {
      cause: cause
    }));
  };
};

var onLoad = function onLoad(resolve, reject) {
  return function () {
    if (window.Stripe) {
      resolve(window.Stripe);
    } else {
      reject(new Error('Stripe.js not available'));
    }
  };
};

var loadScript = function loadScript(params) {
  // Ensure that we only attempt to load Stripe.js at most once
  if (stripePromise$1 !== null) {
    return stripePromise$1;
  }

  stripePromise$1 = new Promise(function (resolve, reject) {
    if (typeof window === 'undefined' || typeof document === 'undefined') {
      // Resolve to null when imported server side. This makes the module
      // safe to import in an isomorphic code base.
      resolve(null);
      return;
    }

    if (window.Stripe && params) {
      console.warn(EXISTING_SCRIPT_MESSAGE);
    }

    if (window.Stripe) {
      resolve(window.Stripe);
      return;
    }

    try {
      var script = findScript();

      if (script && params) {
        console.warn(EXISTING_SCRIPT_MESSAGE);
      } else if (!script) {
        script = injectScript(params);
      } else if (script && onLoadListener !== null && onErrorListener !== null) {
        var _script$parentNode;

        // remove event listeners
        script.removeEventListener('load', onLoadListener);
        script.removeEventListener('error', onErrorListener); // if script exists, but we are reloading due to an error,
        // reload script to trigger 'load' event

        (_script$parentNode = script.parentNode) === null || _script$parentNode === void 0 ? void 0 : _script$parentNode.removeChild(script);
        script = injectScript(params);
      }

      onLoadListener = onLoad(resolve, reject);
      onErrorListener = onError(reject);
      script.addEventListener('load', onLoadListener);
      script.addEventListener('error', onErrorListener);
    } catch (error) {
      reject(error);
      return;
    }
  }); // Resets stripePromise on error

  return stripePromise$1["catch"](function (error) {
    stripePromise$1 = null;
    return Promise.reject(error);
  });
};
var initStripe = function initStripe(maybeStripe, args, startTime) {
  if (maybeStripe === null) {
    return null;
  }

  var pk = args[0];
  var isTestKey = pk.match(/^pk_test/); // @ts-expect-error this is not publicly typed

  var version = runtimeVersionToUrlVersion(maybeStripe.version);
  var expectedVersion = RELEASE_TRAIN;

  if (isTestKey && version !== expectedVersion) {
    console.warn("Stripe.js@".concat(version, " was loaded on the page, but @stripe/stripe-js@").concat("7.8.0", " expected Stripe.js@").concat(expectedVersion, ". This may result in unexpected behavior. For more information, see https://docs.stripe.com/sdks/stripejs-versioning"));
  }

  var stripe = maybeStripe.apply(undefined, args);
  registerWrapper(stripe, startTime);
  return stripe;
}; // eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types

var stripePromise;
var loadCalled = false;

var getStripePromise = function getStripePromise() {
  if (stripePromise) {
    return stripePromise;
  }

  stripePromise = loadScript(null)["catch"](function (error) {
    // clear cache on error
    stripePromise = null;
    return Promise.reject(error);
  });
  return stripePromise;
}; // Execute our own script injection after a tick to give users time to do their
// own script injection.


Promise.resolve().then(function () {
  return getStripePromise();
})["catch"](function (error) {
  if (!loadCalled) {
    console.warn(error);
  }
});
var loadStripe = function loadStripe() {
  for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
    args[_key] = arguments[_key];
  }

  loadCalled = true;
  var startTime = Date.now(); // if previous attempts are unsuccessful, will re-load script

  return getStripePromise().then(function (maybeStripe) {
    return initStripe(maybeStripe, args, startTime);
  });
};




/***/ }),

/***/ "./node_modules/@stripe/stripe-js/lib/index.mjs":
/*!******************************************************!*\
  !*** ./node_modules/@stripe/stripe-js/lib/index.mjs ***!
  \******************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   loadStripe: () => (/* reexport safe */ _dist_index_mjs__WEBPACK_IMPORTED_MODULE_0__.loadStripe)
/* harmony export */ });
/* harmony import */ var _dist_index_mjs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../dist/index.mjs */ "./node_modules/@stripe/stripe-js/dist/index.mjs");



/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**************************************!*\
  !*** ./assets/js/stripe-donation.js ***!
  \**************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _stripe_stripe_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @stripe/stripe-js */ "./node_modules/@stripe/stripe-js/lib/index.mjs");



function _regenerator() {
  /*! regenerator-runtime -- Copyright (c) 2014-present, Facebook, Inc. -- license (MIT): https://github.com/babel/babel/blob/main/packages/babel-helpers/LICENSE */var e,
    t,
    r = "function" == typeof Symbol ? Symbol : {},
    n = r.iterator || "@@iterator",
    o = r.toStringTag || "@@toStringTag";
  function i(r, n, o, i) {
    var c = n && n.prototype instanceof Generator ? n : Generator,
      u = Object.create(c.prototype);
    return _regeneratorDefine2(u, "_invoke", function (r, n, o) {
      var i,
        c,
        u,
        f = 0,
        p = o || [],
        y = !1,
        G = {
          p: 0,
          n: 0,
          v: e,
          a: d,
          f: d.bind(e, 4),
          d: function d(t, r) {
            return i = t, c = 0, u = e, G.n = r, a;
          }
        };
      function d(r, n) {
        for (c = r, u = n, t = 0; !y && f && !o && t < p.length; t++) {
          var o,
            i = p[t],
            d = G.p,
            l = i[2];
          r > 3 ? (o = l === n) && (u = i[(c = i[4]) ? 5 : (c = 3, 3)], i[4] = i[5] = e) : i[0] <= d && ((o = r < 2 && d < i[1]) ? (c = 0, G.v = n, G.n = i[1]) : d < l && (o = r < 3 || i[0] > n || n > l) && (i[4] = r, i[5] = n, G.n = l, c = 0));
        }
        if (o || r > 1) return a;
        throw y = !0, n;
      }
      return function (o, p, l) {
        if (f > 1) throw TypeError("Generator is already running");
        for (y && 1 === p && d(p, l), c = p, u = l; (t = c < 2 ? e : u) || !y;) {
          i || (c ? c < 3 ? (c > 1 && (G.n = -1), d(c, u)) : G.n = u : G.v = u);
          try {
            if (f = 2, i) {
              if (c || (o = "next"), t = i[o]) {
                if (!(t = t.call(i, u))) throw TypeError("iterator result is not an object");
                if (!t.done) return t;
                u = t.value, c < 2 && (c = 0);
              } else 1 === c && (t = i["return"]) && t.call(i), c < 2 && (u = TypeError("The iterator does not provide a '" + o + "' method"), c = 1);
              i = e;
            } else if ((t = (y = G.n < 0) ? u : r.call(n, G)) !== a) break;
          } catch (t) {
            i = e, c = 1, u = t;
          } finally {
            f = 1;
          }
        }
        return {
          value: t,
          done: y
        };
      };
    }(r, o, i), !0), u;
  }
  var a = {};
  function Generator() {}
  function GeneratorFunction() {}
  function GeneratorFunctionPrototype() {}
  t = Object.getPrototypeOf;
  var c = [][n] ? t(t([][n]())) : (_regeneratorDefine2(t = {}, n, function () {
      return this;
    }), t),
    u = GeneratorFunctionPrototype.prototype = Generator.prototype = Object.create(c);
  function f(e) {
    return Object.setPrototypeOf ? Object.setPrototypeOf(e, GeneratorFunctionPrototype) : (e.__proto__ = GeneratorFunctionPrototype, _regeneratorDefine2(e, o, "GeneratorFunction")), e.prototype = Object.create(u), e;
  }
  return GeneratorFunction.prototype = GeneratorFunctionPrototype, _regeneratorDefine2(u, "constructor", GeneratorFunctionPrototype), _regeneratorDefine2(GeneratorFunctionPrototype, "constructor", GeneratorFunction), GeneratorFunction.displayName = "GeneratorFunction", _regeneratorDefine2(GeneratorFunctionPrototype, o, "GeneratorFunction"), _regeneratorDefine2(u), _regeneratorDefine2(u, o, "Generator"), _regeneratorDefine2(u, n, function () {
    return this;
  }), _regeneratorDefine2(u, "toString", function () {
    return "[object Generator]";
  }), (_regenerator = function _regenerator() {
    return {
      w: i,
      m: f
    };
  })();
}
function _regeneratorDefine2(e, r, n, t) {
  var i = Object.defineProperty;
  try {
    i({}, "", {});
  } catch (e) {
    i = 0;
  }
  _regeneratorDefine2 = function _regeneratorDefine(e, r, n, t) {
    function o(r, n) {
      _regeneratorDefine2(e, r, function (e) {
        return this._invoke(r, n, e);
      });
    }
    r ? i ? i(e, r, {
      value: n,
      enumerable: !t,
      configurable: !t,
      writable: !t
    }) : e[r] = n : (o("next", 0), o("throw", 1), o("return", 2));
  }, _regeneratorDefine2(e, r, n, t);
}
/**
 * Stripe Donation - Payment Intents & Payment Methods
 * 
 * Modern Stripe integration using Payment Intents API for
 * enhanced security and SCA (Strong Customer Authentication) support.
 * Includes Apple Pay, Google Pay, and other digital wallet support.
 */

var STRIPE_PUBLIC_KEY = giftflowStripeDonation.stripe_publishable_key;
(function (w) {
  'use strict';

  /**
   * Stripe Donation Class
   * Handles payment processing using Stripe Payment Intents
   * with support for Card, Apple Pay, and Google Pay
   */
  var StripeDonation = /*#__PURE__*/function () {
    /**
     * Constructor
     * 
     * @param {Object} form - Form element.
     * @param {Object} formObject - Form object.
     * @returns {void}
     */
    function StripeDonation(form, formObject) {
      (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__["default"])(this, StripeDonation);
      this.form = form;
      this.formObject = formObject;
      this.stripe = null;
      this.cardElement = null;
      this.paymentRequest = null;
      this.paymentRequestButton = null;
      this.selectedPaymentMethod = null; // Store payment method from wallet

      this.init();
    }
    return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__["default"])(StripeDonation, [{
      key: "getSelf",
      value: function getSelf() {
        return this;
      }
    }, {
      key: "init",
      value: function () {
        var _init = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee3() {
          var _this = this;
          var self, appearance, $element, $wrapperField, $validateWrapper, $errorMessage;
          return _regenerator().w(function (_context3) {
            while (1) switch (_context3.n) {
              case 0:
                self = this; // Load Stripe.js
                _context3.n = 1;
                return (0,_stripe_stripe_js__WEBPACK_IMPORTED_MODULE_3__.loadStripe)(STRIPE_PUBLIC_KEY);
              case 1:
                this.stripe = _context3.v;
                if (this.stripe) {
                  _context3.n = 2;
                  break;
                }
                console.error('Failed to load Stripe.js');
                return _context3.a(2);
              case 2:
                // Create Elements instance
                appearance = {
                  theme: 'stripe',
                  variables: {
                    colorPrimary: '#0570de',
                    colorBackground: '#ffffff',
                    colorText: '#30313d',
                    colorDanger: '#df1b41',
                    fontFamily: 'system-ui, sans-serif',
                    spacingUnit: '4px',
                    borderRadius: '4px'
                  }
                };
                this.stripeElements = this.stripe.elements({
                  appearance: appearance
                });

                // Create Card Element
                this.cardElement = this.stripeElements.create('card', {
                  hidePostalCode: true,
                  style: {
                    base: {
                      fontSize: '16px',
                      color: '#32325d',
                      fontFamily: 'system-ui, -apple-system, sans-serif',
                      '::placeholder': {
                        color: '#aab7c4'
                      }
                    },
                    invalid: {
                      color: '#fa755a',
                      iconColor: '#fa755a'
                    }
                  }
                });

                // Get DOM elements
                $element = this.form.querySelector('#STRIPE-CARD-ELEMENT');
                $wrapperField = $element.closest('.donation-form__field');
                $validateWrapper = $wrapperField;
                $errorMessage = $wrapperField.querySelector('.custom-error-message .custom-error-message-text'); // Mount the Card Element
                this.cardElement.mount($element);

                // Handle real-time validation
                this.cardElement.on('change', function (event) {
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
                _context3.n = 3;
                return this.initPaymentRequestButton();
              case 3:
                // Handle form submission
                self.formObject.eventHub.on('donationFormBeforeSubmit', /*#__PURE__*/function () {
                  var _ref2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee(_ref) {
                    var formSelf, fields, paymentMethod, result, _t;
                    return _regenerator().w(function (_context) {
                      while (1) switch (_context.p = _context.n) {
                        case 0:
                          formSelf = _ref.self, fields = _ref.fields;
                          if (!(fields !== null && fields !== void 0 && fields.payment_method && (fields === null || fields === void 0 ? void 0 : fields.payment_method) !== 'stripe')) {
                            _context.n = 1;
                            break;
                          }
                          return _context.a(2);
                        case 1:
                          $validateWrapper.classList.remove('error', 'custom-error');
                          $errorMessage.textContent = '';
                          _context.p = 2;
                          if (!_this.getSelf().selectedPaymentMethod) {
                            _context.n = 3;
                            break;
                          }
                          paymentMethod = _this.getSelf().selectedPaymentMethod;
                          console.log('Using wallet payment method:', paymentMethod.id);
                          _context.n = 6;
                          break;
                        case 3:
                          _context.n = 4;
                          return _this.getSelf().stripe.createPaymentMethod({
                            type: 'card',
                            card: _this.getSelf().cardElement,
                            billing_details: {
                              name: fields.card_name || fields.donor_name || '',
                              email: fields.donor_email || ''
                            }
                          });
                        case 4:
                          result = _context.v;
                          if (!result.error) {
                            _context.n = 5;
                            break;
                          }
                          $validateWrapper.classList.add('error', 'custom-error');
                          $errorMessage.textContent = result.error.message;
                          throw result.error;
                        case 5:
                          paymentMethod = result.paymentMethod;
                        case 6:
                          // Set Payment Method ID for backend processing
                          formSelf.onSetField('payment_method_id', paymentMethod.id);
                          return _context.a(2, paymentMethod);
                        case 7:
                          _context.p = 7;
                          _t = _context.v;
                          console.error('Payment Method creation failed:', _t);
                          throw _t;
                        case 8:
                          return _context.a(2);
                      }
                    }, _callee, null, [[2, 7]]);
                  }));
                  return function (_x) {
                    return _ref2.apply(this, arguments);
                  };
                }());

                // Handle post-submission (for 3D Secure)
                self.formObject.eventHub.on('donationFormAfterSubmit', /*#__PURE__*/function () {
                  var _ref4 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee2(_ref3) {
                    var response, formSelf, paymentData, _yield$_this$getSelf$, confirmError, paymentIntent, _t2;
                    return _regenerator().w(function (_context2) {
                      while (1) switch (_context2.p = _context2.n) {
                        case 0:
                          response = _ref3.response, formSelf = _ref3.self;
                          if (!(!response || !response.data)) {
                            _context2.n = 1;
                            break;
                          }
                          return _context2.a(2);
                        case 1:
                          paymentData = response.data; // Handle requires_action status (3D Secure / SCA)
                          if (!(paymentData.requires_action && paymentData.client_secret)) {
                            _context2.n = 6;
                            break;
                          }
                          _context2.p = 2;
                          _context2.n = 3;
                          return _this.getSelf().stripe.confirmCardPayment(paymentData.client_secret);
                        case 3:
                          _yield$_this$getSelf$ = _context2.v;
                          confirmError = _yield$_this$getSelf$.error;
                          paymentIntent = _yield$_this$getSelf$.paymentIntent;
                          if (!confirmError) {
                            _context2.n = 4;
                            break;
                          }
                          // Display error to user
                          console.error('Payment confirmation failed:', confirmError);

                          // You can trigger a custom event or update UI here
                          formSelf.eventHub.trigger('paymentConfirmationFailed', {
                            error: confirmError.message
                          });
                          throw confirmError;
                        case 4:
                          if (paymentIntent && paymentIntent.status === 'succeeded') {
                            // Payment succeeded after 3D Secure
                            formSelf.eventHub.trigger('paymentConfirmed', {
                              paymentIntent: paymentIntent
                            });

                            // Optionally reload or redirect
                            window.location.href = paymentData.return_url || giftflowStripeDonation.return_url;
                          }
                          _context2.n = 6;
                          break;
                        case 5:
                          _context2.p = 5;
                          _t2 = _context2.v;
                          console.error('3D Secure confirmation error:', _t2);
                          throw _t2;
                        case 6:
                          return _context2.a(2);
                      }
                    }, _callee2, null, [[2, 5]]);
                  }));
                  return function (_x2) {
                    return _ref4.apply(this, arguments);
                  };
                }());
              case 4:
                return _context3.a(2);
            }
          }, _callee3, this);
        }));
        function init() {
          return _init.apply(this, arguments);
        }
        return init;
      }()
      /**
       * Initialize Payment Request Button (Apple Pay / Google Pay)
       * 
       * @returns {Promise<void>}
       */
    }, {
      key: "initPaymentRequestButton",
      value: function () {
        var _initPaymentRequestButton = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee5() {
          var _this2 = this;
          var self, getDonationAmount, currency, countryCode, canMakePaymentResult, elements, result, $prButtonContainer, $cardElement, $cardWrapper, $cardNameField, $separator;
          return _regenerator().w(function (_context5) {
            while (1) switch (_context5.n) {
              case 0:
                self = this; // Get current donation amount from form
                getDonationAmount = function getDonationAmount() {
                  var fields = self.formObject.fields;
                  var amount = parseFloat(fields.donation_amount || 0);
                  return Math.round(amount * 100); // Convert to cents
                }; // Get currency from settings or default to USD
                currency = (giftflowStripeDonation.currency || 'usd').toLowerCase();
                countryCode = giftflowStripeDonation.country || 'US'; // Create Payment Request
                this.paymentRequest = this.stripe.paymentRequest({
                  country: countryCode,
                  currency: currency,
                  total: {
                    label: giftflowStripeDonation.site_name || 'Donation',
                    amount: getDonationAmount()
                  },
                  requestPayerName: true,
                  requestPayerEmail: true
                });

                // Check if Payment Request is available (Apple Pay / Google Pay)
                _context5.n = 1;
                return this.paymentRequest.canMakePayment();
              case 1:
                canMakePaymentResult = _context5.v;
                if (canMakePaymentResult) {
                  _context5.n = 2;
                  break;
                }
                console.log('Apple Pay / Google Pay not available on this device');
                return _context5.a(2);
              case 2:
                console.log('Payment Request available:', canMakePaymentResult);

                // support only for Apple Pay & Google pay canMakePaymentResult
                if (!(!canMakePaymentResult.applePay && !canMakePaymentResult.googlePay)) {
                  _context5.n = 3;
                  break;
                }
                console.log('Apple Pay / Google Pay not available on this device');
                return _context5.a(2);
              case 3:
                // Create and mount Payment Request Button
                elements = this.stripe.elements();
                this.paymentRequestButton = elements.create('paymentRequestButton', {
                  paymentRequest: this.paymentRequest,
                  style: {
                    paymentRequestButton: {
                      type: 'donate',
                      // Can be 'default', 'donate', 'buy'
                      theme: 'dark',
                      // Can be 'dark', 'light', or 'light-outline'
                      height: '48px'
                    }
                  }
                });

                // Check if button can be mounted
                _context5.n = 4;
                return this.paymentRequest.canMakePayment();
              case 4:
                result = _context5.v;
                if (result) {
                  // Find or create container for Payment Request Button
                  $prButtonContainer = this.form.querySelector('#payment-request-button');
                  if (!$prButtonContainer) {
                    // Create container if it doesn't exist
                    $cardElement = this.form.querySelector('#STRIPE-CARD-ELEMENT');
                    $cardWrapper = $cardElement.closest('.donation-form__payment-method-description');
                    $prButtonContainer = document.createElement('div');
                    $prButtonContainer.id = 'payment-request-button';
                    $prButtonContainer.className = 'payment-request-button-wrapper';

                    // Insert before card fields
                    $cardNameField = $cardWrapper.querySelector('.donation-form__card-fields');
                    if ($cardNameField) {
                      $cardWrapper.insertBefore($prButtonContainer, $cardNameField);
                    } else {
                      $cardWrapper.insertBefore($prButtonContainer, $cardWrapper.firstChild);
                    }

                    // Add separator
                    $separator = document.createElement('div');
                    $separator.className = 'payment-request-separator';
                    $separator.innerHTML = '<span>or pay with card</span>';
                    $cardWrapper.insertBefore($separator, $cardNameField);
                  }

                  // Mount the button
                  this.paymentRequestButton.mount('#payment-request-button');
                  console.log('Payment Request Button mounted successfully');
                }

                // Listen for amount changes and update payment request
                self.formObject.eventHub.on('donationAmountChanged', function (_ref5) {
                  var amount = _ref5.amount;
                  if (_this2.paymentRequest) {
                    _this2.paymentRequest.update({
                      total: {
                        label: giftflowStripeDonation.site_name || 'Donation',
                        amount: Math.round(parseFloat(amount) * 100)
                      }
                    });
                  }
                });

                // Handle payment method creation from wallet
                this.paymentRequest.on('paymentmethod', /*#__PURE__*/function () {
                  var _ref6 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee4(ev) {
                    var submitButton, _t3;
                    return _regenerator().w(function (_context4) {
                      while (1) switch (_context4.p = _context4.n) {
                        case 0:
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
                          _context4.p = 1;
                          // Trigger form submission programmatically
                          // The form will use the stored payment method
                          submitButton = self.form.querySelector('[type="submit"]');
                          if (!submitButton) {
                            _context4.n = 2;
                            break;
                          }
                          // add .__skip-validate-field-inner
                          self.form.querySelector('#STRIPE-CARD-ELEMENT').closest('.donation-form__card-fields').classList.add('__skip-validate-field-inner');

                          // Store that we're using wallet payment
                          self.formObject.onSetField('using_wallet_payment', 'true');

                          // Trigger the form submission
                          submitButton.click();

                          // Complete the payment (success will be handled by webhook/backend)
                          ev.complete('success');
                          _context4.n = 3;
                          break;
                        case 2:
                          throw new Error('Submit button not found');
                        case 3:
                          _context4.n = 5;
                          break;
                        case 4:
                          _context4.p = 4;
                          _t3 = _context4.v;
                          console.error('Wallet payment processing failed:', _t3);
                          ev.complete('fail');

                          // remove .__skip-validate-field-inner
                          self.form.querySelector('#STRIPE-CARD-ELEMENT').closest('.donation-form__card-fields').classList.remove('__skip-validate-field-inner');

                          // Clear the stored payment method
                          self.selectedPaymentMethod = null;
                        case 5:
                          return _context4.a(2);
                      }
                    }, _callee4, null, [[1, 4]]);
                  }));
                  return function (_x3) {
                    return _ref6.apply(this, arguments);
                  };
                }());

                // Handle errors
                this.paymentRequest.on('cancel', function () {
                  console.log('Payment Request canceled by user');
                  self.selectedPaymentMethod = null;
                });
              case 5:
                return _context5.a(2);
            }
          }, _callee5, this);
        }));
        function initPaymentRequestButton() {
          return _initPaymentRequestButton.apply(this, arguments);
        }
        return initPaymentRequestButton;
      }()
    }]);
  }();

  // Initialize when donation form is loaded
  document.addEventListener('donationFormLoaded', function (e) {
    var _e$detail = e.detail,
      self = _e$detail.self,
      form = _e$detail.form;
    new StripeDonation(form, self);
  });
})(window);
})();

/******/ })()
;