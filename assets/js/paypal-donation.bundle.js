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
  !*** ./assets/js/paypal-donation.js ***!
  \**************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");



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
 * PayPal Donation Integration
 * Uses PayPal JS SDK v6 for seamless payment processing
 */

(function (w) {
  'use strict';

  // PayPal Donation class
  var PayPalDonation = /*#__PURE__*/function () {
    /**
     * Constructor
     * 
     * @param {Object} form - Form element.
     * @param {Object} formObject - Form object.
     * @returns {void}
     */
    function PayPalDonation(form, formObject) {
      (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__["default"])(this, PayPalDonation);
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
    return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__["default"])(PayPalDonation, [{
      key: "init",
      value: function () {
        var _init = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee4() {
          var _this = this;
          var self, paymentMethodInput, container, paymentMethodInputs;
          return _regenerator().w(function (_context4) {
            while (1) switch (_context4.n) {
              case 0:
                self = this; // Wait for PayPal SDK to load
                if (!(typeof paypal === 'undefined')) {
                  _context4.n = 1;
                  break;
                }
                // Retry after a short delay
                setTimeout(function () {
                  self.init();
                }, 100);
                return _context4.a(2);
              case 1:
                // Check if payment method is PayPal
                paymentMethodInput = this.form.querySelector('input[name="payment_method"][value="paypal"]');
                if (paymentMethodInput) {
                  _context4.n = 2;
                  break;
                }
                return _context4.a(2);
              case 2:
                // Get container
                container = document.getElementById('giftflow-paypal-button-container');
                if (container) {
                  _context4.n = 3;
                  break;
                }
                return _context4.a(2);
              case 3:
                if (!this.isInitialized) {
                  _context4.n = 4;
                  break;
                }
                return _context4.a(2);
              case 4:
                this.isInitialized = true;

                // Initialize PayPal buttons
                try {
                  this.paypalButtons = paypal.Buttons({
                    style: {
                      layout: 'vertical',
                      color: 'blue',
                      shape: 'rect',
                      label: 'paypal'
                    },
                    createOrder: function () {
                      var _createOrder = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee(data, actions) {
                        return _regenerator().w(function (_context) {
                          while (1) switch (_context.n) {
                            case 0:
                              _context.n = 1;
                              return self.createOrder();
                            case 1:
                              return _context.a(2, _context.v);
                          }
                        }, _callee);
                      }));
                      function createOrder(_x, _x2) {
                        return _createOrder.apply(this, arguments);
                      }
                      return createOrder;
                    }(),
                    onApprove: function () {
                      var _onApprove = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee2(data, actions) {
                        return _regenerator().w(function (_context2) {
                          while (1) switch (_context2.n) {
                            case 0:
                              _context2.n = 1;
                              return self.onApprove(data);
                            case 1:
                              return _context2.a(2, _context2.v);
                          }
                        }, _callee2);
                      }));
                      function onApprove(_x3, _x4) {
                        return _onApprove.apply(this, arguments);
                      }
                      return onApprove;
                    }(),
                    onCancel: function onCancel(data) {
                      self.onCancel(data);
                    },
                    onError: function onError(err) {
                      self.onError(err);
                    }
                  });

                  // reset the container
                  this.form.querySelector('#giftflow-paypal-button-container').innerHTML = '';

                  // Render buttons
                  this.paypalButtons.render(this.form.querySelector('#giftflow-paypal-button-container'))["catch"](function (err) {
                    console.error('PayPal buttons render error:', err);
                  });
                } catch (error) {
                  console.error('PayPal initialization error:', error);
                }

                // Listen for payment method changes
                paymentMethodInputs = this.form.querySelectorAll('input[name="payment_method"]');
                paymentMethodInputs.forEach(function (input) {
                  input.addEventListener('change', function () {
                    if (input.value === 'paypal') {
                      // Show PayPal container
                      var paypalContainer = _this.form.querySelector('.donation-form__payment-method-description--paypal');
                      if (paypalContainer) {
                        paypalContainer.style.display = 'block';
                      }
                    } else {
                      // Hide PayPal container
                      var _paypalContainer = _this.form.querySelector('.donation-form__payment-method-description--paypal');
                      if (_paypalContainer) {
                        _paypalContainer.style.display = 'none';
                      }
                    }
                  });
                });

                // Listen for form submission
                this.form.addEventListener('donationFormBeforeSubmit', /*#__PURE__*/function () {
                  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee3(e) {
                    var _e$detail, formSelf, fields, resolve, reject;
                    return _regenerator().w(function (_context3) {
                      while (1) switch (_context3.n) {
                        case 0:
                          _e$detail = e.detail, formSelf = _e$detail.self, fields = _e$detail.fields, resolve = _e$detail.resolve, reject = _e$detail.reject; // If payment method is not PayPal, return
                          if (!(fields !== null && fields !== void 0 && fields.payment_method && (fields === null || fields === void 0 ? void 0 : fields.payment_method) !== 'paypal')) {
                            _context3.n = 1;
                            break;
                          }
                          resolve(null);
                          return _context3.a(2);
                        case 1:
                          // PayPal payment is handled by the button click, so we just resolve
                          // The actual payment happens in onApprove
                          resolve(null);
                        case 2:
                          return _context3.a(2);
                      }
                    }, _callee3);
                  }));
                  return function (_x5) {
                    return _ref.apply(this, arguments);
                  };
                }());
              case 5:
                return _context4.a(2);
            }
          }, _callee4, this);
        }));
        function init() {
          return _init.apply(this, arguments);
        }
        return init;
      }()
      /**
       * Create PayPal order
       * 
       * @returns {Promise<string>} Order ID
       */
    }, {
      key: "createOrder",
      value: function () {
        var _createOrder2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee5() {
          var _this$formObject$fiel, _this$formObject$fiel2, _this$formObject$fiel3, _this$formObject$fiel4, _this$formObject$fiel5, _this$formObject$fiel6, _this$formObject$fiel7, _this$formObject$fiel8, formData, donationAmount, requestData, response, result, _result$data, _t;
          return _regenerator().w(function (_context5) {
            while (1) switch (_context5.p = _context5.n) {
              case 0:
                _context5.p = 0;
                // Get form data
                formData = new FormData(this.form);
                donationAmount = formData.get('donation_amount') || ((_this$formObject$fiel = this.formObject.fields) === null || _this$formObject$fiel === void 0 ? void 0 : _this$formObject$fiel.donation_amount) || '0'; // Prepare request data with all donation information
                requestData = {
                  action: 'giftflow_paypal_create_order',
                  nonce: giftflowPayPalDonation.nonce,
                  amount: donationAmount,
                  donor_name: formData.get('donor_name') || ((_this$formObject$fiel2 = this.formObject.fields) === null || _this$formObject$fiel2 === void 0 ? void 0 : _this$formObject$fiel2.donor_name) || '',
                  donor_email: formData.get('donor_email') || ((_this$formObject$fiel3 = this.formObject.fields) === null || _this$formObject$fiel3 === void 0 ? void 0 : _this$formObject$fiel3.donor_email) || '',
                  campaign_id: formData.get('campaign_id') || ((_this$formObject$fiel4 = this.formObject.fields) === null || _this$formObject$fiel4 === void 0 ? void 0 : _this$formObject$fiel4.campaign_id) || '',
                  donation_type: formData.get('donation_type') || ((_this$formObject$fiel5 = this.formObject.fields) === null || _this$formObject$fiel5 === void 0 ? void 0 : _this$formObject$fiel5.donation_type) || '',
                  recurring_interval: formData.get('recurring_interval') || ((_this$formObject$fiel6 = this.formObject.fields) === null || _this$formObject$fiel6 === void 0 ? void 0 : _this$formObject$fiel6.recurring_interval) || '',
                  donor_message: formData.get('donor_message') || ((_this$formObject$fiel7 = this.formObject.fields) === null || _this$formObject$fiel7 === void 0 ? void 0 : _this$formObject$fiel7.donor_message) || '',
                  anonymous_donation: formData.get('anonymous_donation') || ((_this$formObject$fiel8 = this.formObject.fields) === null || _this$formObject$fiel8 === void 0 ? void 0 : _this$formObject$fiel8.anonymous_donation) || ''
                }; // Make AJAX request
                _context5.n = 1;
                return fetch(giftflowPayPalDonation.ajaxurl, {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                  },
                  body: new URLSearchParams(requestData)
                });
              case 1:
                response = _context5.v;
                _context5.n = 2;
                return response.json();
              case 2:
                result = _context5.v;
                if (result.success) {
                  _context5.n = 3;
                  break;
                }
                throw new Error(((_result$data = result.data) === null || _result$data === void 0 ? void 0 : _result$data.message) || 'Failed to create PayPal order');
              case 3:
                return _context5.a(2, result.data.orderID);
              case 4:
                _context5.p = 4;
                _t = _context5.v;
                console.error('PayPal create order error:', _t);
                throw _t;
              case 5:
                return _context5.a(2);
            }
          }, _callee5, this, [[0, 4]]);
        }));
        function createOrder() {
          return _createOrder2.apply(this, arguments);
        }
        return createOrder;
      }()
      /**
       * Handle approved payment
       * 
       * @param {Object} data - PayPal approval data
       * @returns {Promise<void>}
       */
    }, {
      key: "onApprove",
      value: function () {
        var _onApprove2 = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee6(data) {
          var _result$data3, requestData, response, result, _result$data2, donationIdInput, hiddenInput, _t2;
          return _regenerator().w(function (_context6) {
            while (1) switch (_context6.p = _context6.n) {
              case 0:
                _context6.p = 0;
                // Prepare request data (donation_id will be created after payment)
                requestData = {
                  action: 'giftflow_paypal_capture_order',
                  nonce: giftflowPayPalDonation.nonce,
                  orderID: data.orderID
                }; // Show processing message
                this.showProcessingMessage();

                // Make AJAX request to capture order
                _context6.n = 1;
                return fetch(giftflowPayPalDonation.ajaxurl, {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                  },
                  body: new URLSearchParams(requestData)
                });
              case 1:
                response = _context6.v;
                _context6.n = 2;
                return response.json();
              case 2:
                result = _context6.v;
                if (result.success) {
                  _context6.n = 3;
                  break;
                }
                throw new Error(((_result$data2 = result.data) === null || _result$data2 === void 0 ? void 0 : _result$data2.message) || 'Failed to capture PayPal order');
              case 3:
                // Payment successful - store donation_id if returned
                if ((_result$data3 = result.data) !== null && _result$data3 !== void 0 && _result$data3.donation_id) {
                  // Store donation_id in form for potential use
                  donationIdInput = this.form.querySelector('input[name="donation_id"]');
                  if (donationIdInput) {
                    donationIdInput.value = result.data.donation_id;
                  } else {
                    // Create hidden input if it doesn't exist
                    hiddenInput = document.createElement('input');
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
                _context6.n = 5;
                break;
              case 4:
                _context6.p = 4;
                _t2 = _context6.v;
                console.error('PayPal capture error:', _t2);
                this.showErrorMessage(_t2.message || giftflowPayPalDonation.messages.error);
              case 5:
                return _context6.a(2);
            }
          }, _callee6, this, [[0, 4]]);
        }));
        function onApprove(_x6) {
          return _onApprove2.apply(this, arguments);
        }
        return onApprove;
      }()
      /**
       * Handle canceled payment
       * 
       * @param {Object} data - PayPal cancel data
       * @returns {void}
       */
    }, {
      key: "onCancel",
      value: function onCancel(data) {
        var message = (data === null || data === void 0 ? void 0 : data.message) || giftflowPayPalDonation.messages.canceled;
        this.showErrorMessage(message + " If you want to continue, please reload the page and try again.");
      }

      /**
       * Handle payment error
       * 
       * @param {Error} err - Error object
       * @returns {void}
       */
    }, {
      key: "onError",
      value: function onError(err) {
        // console.error('PayPal error:', err);
        this.formObject.onShowErrorSection(err.message || giftflowPayPalDonation.messages.error);
        // this.showErrorMessage(err.message || giftflowPayPalDonation.messages.error);
      }

      /**
       * Show processing message
       * 
       * @returns {void}
       */
    }, {
      key: "showProcessingMessage",
      value: function showProcessingMessage() {
        var container = document.getElementById('giftflow-paypal-button-container');
        if (container) {
          container.innerHTML = '<p style="text-align: center; padding: 20px;">' + giftflowPayPalDonation.messages.processing + '</p>';
        }
      }

      /**
       * Show success message
       * 
       * @returns {void}
       */
    }, {
      key: "showSuccessMessage",
      value: function showSuccessMessage() {
        this.formObject.onShowThankYouSection();

        // const container = document.getElementById('giftflow-paypal-button-container');
        // if (container) {
        // 	container.innerHTML = '<p style="text-align: center; padding: 20px; color: green; border-radius: 4px; background: #e6f7e6; border: 1px solid #c6f0c6;">' + 
        // 		'Payment successful!' + 
        // 		'</p>';
        // }
      }

      /**
       * Show error message
       * 
       * @param {string} message - Error message
       * @returns {void}
       */
    }, {
      key: "showErrorMessage",
      value: function showErrorMessage(message) {
        var _this2 = this;
        // this.formObject.onShowErrorSection(message);

        var container = document.getElementById('giftflow-paypal-button-container');
        if (container) {
          var errorDiv = document.createElement('div');
          errorDiv.className = 'paypal-error-message';
          errorDiv.style.cssText = 'text-align: center; padding: 15px; background: #fee; color: #c33; border: 1px solid #fcc; border-radius: 4px; margin: 10px 0;';
          errorDiv.textContent = message;
          container.innerHTML = '';
          container.appendChild(errorDiv);

          // Re-render buttons after error
          setTimeout(function () {
            if (_this2.paypalButtons) {
              _this2.paypalButtons.render('#giftflow-paypal-button-container')["catch"](function (err) {
                console.error('PayPal buttons re-render error:', err);
              });
            }
          }, 2000);
        }
      }
    }]);
  }();

  // Initialize when form is loaded
  document.addEventListener('donationFormLoaded', function (e) {
    var _e$detail2 = e.detail,
      self = _e$detail2.self,
      form = _e$detail2.form;

    // Check if PayPal payment method exists
    var paypalInput = form.querySelector('input[name="payment_method"][value="paypal"]');
    if (paypalInput) {
      new PayPalDonation(form, self);
    }
  });
})(window);
})();

/******/ })()
;