/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/js/util/async-event-hub.js":
/*!*******************************************!*\
  !*** ./assets/js/util/async-event-hub.js ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   AsyncEventHub: () => (/* binding */ AsyncEventHub)
/* harmony export */ });
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
function _createForOfIteratorHelper(r, e) {
  var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"];
  if (!t) {
    if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) {
      t && (r = t);
      var _n = 0,
        F = function F() {};
      return {
        s: F,
        n: function n() {
          return _n >= r.length ? {
            done: !0
          } : {
            done: !1,
            value: r[_n++]
          };
        },
        e: function e(r) {
          throw r;
        },
        f: F
      };
    }
    throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
  }
  var o,
    a = !0,
    u = !1;
  return {
    s: function s() {
      t = t.call(r);
    },
    n: function n() {
      var r = t.next();
      return a = r.done, r;
    },
    e: function e(r) {
      u = !0, o = r;
    },
    f: function f() {
      try {
        a || null == t["return"] || t["return"]();
      } finally {
        if (u) throw o;
      }
    }
  };
}
function _unsupportedIterableToArray(r, a) {
  if (r) {
    if ("string" == typeof r) return _arrayLikeToArray(r, a);
    var t = {}.toString.call(r).slice(8, -1);
    return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0;
  }
}
function _arrayLikeToArray(r, a) {
  (null == a || a > r.length) && (a = r.length);
  for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
  return n;
}
/**
 * AsyncEventHub is a class that allows you to register and emit events asynchronously.
 * 
 * @since 1.0.0
 * @author GiftFlow
 */

var AsyncEventHub = /*#__PURE__*/function () {
  function AsyncEventHub() {
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__["default"])(this, AsyncEventHub);
    this.events = new Map();
  }
  return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__["default"])(AsyncEventHub, [{
    key: "on",
    value: function on(eventName, handler) {
      var _this = this;
      if (!this.events.has(eventName)) {
        this.events.set(eventName, []);
      }
      this.events.get(eventName).push(handler);

      // unsubscribe
      return function () {
        var list = _this.events.get(eventName) || [];
        _this.events.set(eventName, list.filter(function (h) {
          return h !== handler;
        }));
      };
    }
  }, {
    key: "emit",
    value: function () {
      var _emit = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee(eventName, payload) {
        var options,
          handlers,
          _options$mode,
          mode,
          _options$stopOnFalse,
          stopOnFalse,
          _results,
          results,
          _iterator,
          _step,
          handler,
          result,
          _args = arguments,
          _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.p = _context.n) {
            case 0:
              options = _args.length > 2 && _args[2] !== undefined ? _args[2] : {};
              handlers = this.events.get(eventName) || [];
              _options$mode = options.mode, mode = _options$mode === void 0 ? 'series' : _options$mode, _options$stopOnFalse = options.stopOnFalse, stopOnFalse = _options$stopOnFalse === void 0 ? true : _options$stopOnFalse;
              if (!(mode === 'parallel')) {
                _context.n = 3;
                break;
              }
              _context.n = 1;
              return Promise.all(handlers.map(function (h) {
                return h(payload);
              }));
            case 1:
              _results = _context.v;
              if (!(stopOnFalse && _results.includes(false))) {
                _context.n = 2;
                break;
              }
              throw new Error("AsyncEventHub: \"".concat(eventName, "\" blocked"));
            case 2:
              return _context.a(2, _results);
            case 3:
              // series (default, safe)
              results = [];
              _iterator = _createForOfIteratorHelper(handlers);
              _context.p = 4;
              _iterator.s();
            case 5:
              if ((_step = _iterator.n()).done) {
                _context.n = 8;
                break;
              }
              handler = _step.value;
              _context.n = 6;
              return handler(payload);
            case 6:
              result = _context.v;
              results.push(result);
              if (!(stopOnFalse && result === false)) {
                _context.n = 7;
                break;
              }
              throw new Error("AsyncEventHub: \"".concat(eventName, "\" blocked"));
            case 7:
              _context.n = 5;
              break;
            case 8:
              _context.n = 10;
              break;
            case 9:
              _context.p = 9;
              _t = _context.v;
              _iterator.e(_t);
            case 10:
              _context.p = 10;
              _iterator.f();
              return _context.f(10);
            case 11:
              return _context.a(2, results);
          }
        }, _callee, this, [[4, 9, 10, 11]]);
      }));
      function emit(_x, _x2) {
        return _emit.apply(this, arguments);
      }
      return emit;
    }()
  }]);
}();

/***/ }),

/***/ "./assets/js/util/helpers.js":
/*!***********************************!*\
  !*** ./assets/js/util/helpers.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   applySlideEffect: () => (/* binding */ applySlideEffect),
/* harmony export */   replaceContentBySelector: () => (/* binding */ replaceContentBySelector),
/* harmony export */   validateValue: () => (/* binding */ validateValue)
/* harmony export */ });
function _createForOfIteratorHelper(r, e) {
  var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"];
  if (!t) {
    if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) {
      t && (r = t);
      var _n = 0,
        F = function F() {};
      return {
        s: F,
        n: function n() {
          return _n >= r.length ? {
            done: !0
          } : {
            done: !1,
            value: r[_n++]
          };
        },
        e: function e(r) {
          throw r;
        },
        f: F
      };
    }
    throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
  }
  var o,
    a = !0,
    u = !1;
  return {
    s: function s() {
      t = t.call(r);
    },
    n: function n() {
      var r = t.next();
      return a = r.done, r;
    },
    e: function e(r) {
      u = !0, o = r;
    },
    f: function f() {
      try {
        a || null == t["return"] || t["return"]();
      } finally {
        if (u) throw o;
      }
    }
  };
}
function _unsupportedIterableToArray(r, a) {
  if (r) {
    if ("string" == typeof r) return _arrayLikeToArray(r, a);
    var t = {}.toString.call(r).slice(8, -1);
    return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0;
  }
}
function _arrayLikeToArray(r, a) {
  (null == a || a > r.length) && (a = r.length);
  for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
  return n;
}
var replaceContentBySelector = function replaceContentBySelector(selector, content) {
  var elem = document.querySelector(selector);
  if (elem) {
    elem.innerHTML = content;
  } else {
    console.error("Element not found for selector: ".concat(selector));
  }
};

/**
 * Apply a slideDown or slideUp effect to a DOM element.
 * @param {HTMLElement} dom - The target element.
 * @param {'slidedown'|'slideup'} effect - The effect type.
 * @param {number} duration - Duration in ms. Default: 300
 * @param {string} displayType - The display style to use (e.g., 'block', 'grid'). Default: 'block'
 */
function applySlideEffect(dom) {
  var effect = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'slidedown';
  var duration = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 300;
  var displayType = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'block';
  if (!dom) return;
  if (!['slidedown', 'slideup'].includes(effect)) {
    console.error('Invalid effect:', effect);
    return;
  }
  dom.style.overflow = 'hidden';
  if (effect === 'slidedown') {
    dom.style.display = displayType;
    var height = dom.scrollHeight;
    dom.style.height = '0px';

    // force reflow to ensure setting height is registered
    // eslint-disable-next-line no-unused-expressions
    dom.offsetHeight;
    dom.style.transition = "height ".concat(duration, "ms ease");
    dom.style.height = height + 'px';
    var _onEnd = function onEnd() {
      dom.style.display = displayType;
      dom.style.height = '';
      dom.style.overflow = '';
      dom.style.transition = '';
      dom.removeEventListener('transitionend', _onEnd);
    };
    dom.addEventListener('transitionend', _onEnd);
  } else if (effect === 'slideup') {
    // Remember current display style in case we want to restore it
    var prevDisplay = dom.style.display;
    var _height = dom.scrollHeight;
    dom.style.height = _height + 'px';

    // force reflow
    // eslint-disable-next-line no-unused-expressions
    dom.offsetHeight;
    dom.style.transition = "height ".concat(duration, "ms ease");
    dom.style.height = '0px';
    var _onEnd2 = function onEnd() {
      dom.style.display = 'none';
      dom.style.height = '';
      dom.style.overflow = '';
      dom.style.transition = '';
      dom.removeEventListener('transitionend', _onEnd2);
      // Optionally restore previous style if needed in future
    };
    dom.addEventListener('transitionend', _onEnd2);
  }
}

/**
 * Validate a value according to given validation types.
 * @param {string} type - Comma-separated string of validation types, e.g. "required,email".
 * @param {any} value - Value to validate.
 * @param {Object|null} extraData - Optional extra data for some validations (min/max).
 * @returns {boolean} - True if passes all validations, false otherwise.
 */
function validateValue(type, value) {
  var extraData = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
  // Accept multiple comma-delimited validation types, pass if all pass
  var types = type ? type.split(',').map(function (s) {
    return s.trim();
  }) : [];
  var overallValid = true;
  var _iterator = _createForOfIteratorHelper(types),
    _step;
  try {
    for (_iterator.s(); !(_step = _iterator.n()).done;) {
      var t = _step.value;
      switch (t) {
        // email
        case 'email':
          if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) overallValid = false;
          break;

        // phone
        case 'phone':
          // starts with optional +, then digits, optional spaces/hyphens
          if (!/^\+?[0-9\s\-]+$/.test(value)) overallValid = false;
          break;

        // required
        case 'required':
          if (typeof value === 'undefined' || value === null || value.toString().trim() === '') overallValid = false;
          break;

        // number
        case 'number':
          if (isNaN(value) || value === '') overallValid = false;
          break;

        // min
        case 'min':
          var __min = parseInt((extraData === null || extraData === void 0 ? void 0 : extraData.min) || 0, 10);
          if (value < __min || value === '') overallValid = false;
          break;

        // max
        case 'max':
          var __max = parseInt((extraData === null || extraData === void 0 ? void 0 : extraData.max) || 0, 10);
          if (value > __max || value === '') overallValid = false;
          break;

        // default: always pass unknown validators
        default:
          // do nothing
          break;
      }
      if (!overallValid) break; // stop on first failure
    }
  } catch (err) {
    _iterator.e(err);
  } finally {
    _iterator.f();
  }
  return overallValid;
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _arrayLikeToArray)
/* harmony export */ });
function _arrayLikeToArray(r, a) {
  (null == a || a > r.length) && (a = r.length);
  for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e];
  return n;
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/arrayWithoutHoles.js":
/*!**********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/arrayWithoutHoles.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _arrayWithoutHoles)
/* harmony export */ });
/* harmony import */ var _arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./arrayLikeToArray.js */ "./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js");

function _arrayWithoutHoles(r) {
  if (Array.isArray(r)) return (0,_arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r);
}


/***/ }),

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

/***/ "./node_modules/@babel/runtime/helpers/esm/iterableToArray.js":
/*!********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/iterableToArray.js ***!
  \********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _iterableToArray)
/* harmony export */ });
function _iterableToArray(r) {
  if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r);
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js":
/*!**********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _nonIterableSpread)
/* harmony export */ });
function _nonIterableSpread() {
  throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js":
/*!**********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _toConsumableArray)
/* harmony export */ });
/* harmony import */ var _arrayWithoutHoles_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./arrayWithoutHoles.js */ "./node_modules/@babel/runtime/helpers/esm/arrayWithoutHoles.js");
/* harmony import */ var _iterableToArray_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./iterableToArray.js */ "./node_modules/@babel/runtime/helpers/esm/iterableToArray.js");
/* harmony import */ var _unsupportedIterableToArray_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./unsupportedIterableToArray.js */ "./node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js");
/* harmony import */ var _nonIterableSpread_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./nonIterableSpread.js */ "./node_modules/@babel/runtime/helpers/esm/nonIterableSpread.js");




function _toConsumableArray(r) {
  return (0,_arrayWithoutHoles_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r) || (0,_iterableToArray_js__WEBPACK_IMPORTED_MODULE_1__["default"])(r) || (0,_unsupportedIterableToArray_js__WEBPACK_IMPORTED_MODULE_2__["default"])(r) || (0,_nonIterableSpread_js__WEBPACK_IMPORTED_MODULE_3__["default"])();
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

/***/ "./node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js":
/*!*******************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js ***!
  \*******************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _unsupportedIterableToArray)
/* harmony export */ });
/* harmony import */ var _arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./arrayLikeToArray.js */ "./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js");

function _unsupportedIterableToArray(r, a) {
  if (r) {
    if ("string" == typeof r) return (0,_arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r, a);
    var t = {}.toString.call(r).slice(8, -1);
    return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? (0,_arrayLikeToArray_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r, a) : void 0;
  }
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
/*!****************************!*\
  !*** ./assets/js/forms.js ***!
  \****************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/toConsumableArray */ "./node_modules/@babel/runtime/helpers/esm/toConsumableArray.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _util_helpers__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./util/helpers */ "./assets/js/util/helpers.js");
/* harmony import */ var _util_async_event_hub__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./util/async-event-hub */ "./assets/js/util/async-event-hub.js");




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
 * Donation Form
 * 
 * @param {Object} w - Window object.
 */
(function () {
  var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])(/*#__PURE__*/_regenerator().m(function _callee4(w) {
    'use strict';

    // make donationForm class.
    var donationForm, initDonationForm;
    return _regenerator().w(function (_context4) {
      while (1) switch (_context4.n) {
        case 0:
          donationForm = /*#__PURE__*/function () {
            /**
             * Constructor
             * 
             * @param {Object} donationForm - Donation form element.
             * @param {Object} options - Options.
             * @returns {void}
             */
            function donationForm(_donationForm, options) {
              (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__["default"])(this, donationForm);
              this.fields = {};
              this.form = _donationForm;
              this.options = options;
              this.totalSteps = this.form.querySelectorAll('.donation-form__step-panel').length;
              this.currentStep = 1;
              this.eventHub = new _util_async_event_hub__WEBPACK_IMPORTED_MODULE_5__.AsyncEventHub();
              this.init(_donationForm, options);
            }
            return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__["default"])(donationForm, [{
              key: "init",
              value: function init(_donationForm2, options) {
                var _this = this;
                var self = this;

                // set default payment method selected.
                var methodSelected = this.form.querySelector("input[name=\"payment_method\"][value=\"".concat(options.paymentMethodSelected, "\"]"));
                if (methodSelected) {
                  methodSelected.checked = true;
                }
                this.setInitFields(_donationForm2);
                this.onListenerFormFieldUpdate();

                // create event trigger on load form to document.
                document.dispatchEvent(new CustomEvent('donationFormLoaded', {
                  detail: {
                    self: self,
                    form: self.form
                  }
                }));

                // on change amount field.
                this.form.addEventListener('input', function (event) {
                  if (event.target.name === 'donation_amount') {
                    _this.onUpdateAmountField(event.target.value);
                  }
                  if (event.target.name === 'payment_method') {
                    _this.onChangePaymentMethod(event.target.value);
                  }
                });

                // on click Preset Amount.
                this.form.addEventListener('click', function (event) {
                  if (event.target.classList.contains('donation-form__preset-amount')) {
                    _this.onClickPresetAmount(event);
                  }
                });

                // on click next step.
                this.form.addEventListener('click', function (event) {
                  // is contains class and is element had class donation-form__button--next.
                  var isNextButton = event.target.classList.contains('donation-form__button--next') && event.target.tagName === 'BUTTON';
                  if (isNextButton) {
                    var stepPass = _this.onValidateFieldsCurrentStep();
                    if (stepPass) {
                      _this.onNextStep();
                    }
                  }
                });

                // on click previous step.
                this.form.addEventListener('click', function (event) {
                  // is contains class and is element had class donation-form__button--back.
                  var isBackButton = event.target.classList.contains('donation-form__button--back') && event.target.tagName === 'BUTTON';
                  if (isBackButton) {
                    _this.onPreviousStep();
                  }
                });

                // disable enter key submit form.
                this.form.addEventListener('keydown', function (e) {
                  if (e.key === 'Enter' && ['INPUT', 'SELECT'].includes(e.target.tagName)) {
                    e.preventDefault();
                    return false;
                  }
                });

                // on submit form.
                this.form.addEventListener('submit', function (event) {
                  event.preventDefault();
                  event.stopPropagation();

                  // submit form.
                  _this.onSubmitForm();
                });
              }
            }, {
              key: "getFields",
              value: function getFields() {
                return this.fields;
              }
            }, {
              key: "onSetLoading",
              value: function onSetLoading(status) {
                var self = this;
                self.form.querySelector('.donation-form__button--submit').classList.toggle('loading', status);
                self.form.querySelector('.donation-form__button--submit').disabled = status;
              }
            }, {
              key: "onSubmitForm",
              value: function () {
                var _onSubmitForm = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])(/*#__PURE__*/_regenerator().m(function _callee() {
                  var self, pass, response, errorMessage, _response$data, _t;
                  return _regenerator().w(function (_context) {
                    while (1) switch (_context.p = _context.n) {
                      case 0:
                        self = this;
                        self.onSetLoading(true);

                        // validate fields.
                        pass = self.onValidateFieldsCurrentStep(); // console.log('pass', pass);
                        if (pass) {
                          _context.n = 1;
                          break;
                        }
                        self.onSetLoading(false);
                        return _context.a(2);
                      case 1:
                        _context.p = 1;
                        _context.n = 2;
                        return self.eventHub.emit('donationFormBeforeSubmit', {
                          self: self,
                          fields: self.getFields()
                        });
                      case 2:
                        _context.n = 4;
                        break;
                      case 3:
                        _context.p = 3;
                        _t = _context.v;
                        console.error('Error in onDoHooks:', _t);
                        self.onSetLoading(false);
                        return _context.a(2);
                      case 4:
                        _context.n = 5;
                        return self.onSendData(self.getFields());
                      case 5:
                        response = _context.v;
                        if (!(!response || !response.success)) {
                          _context.n = 6;
                          break;
                        }
                        // show error section.
                        self.form.querySelector('.donation-form__step-panel.is-active').classList.remove('is-active');
                        self.form.querySelector('#donation-error').classList.add('is-active');

                        // set error message.
                        errorMessage = self.form.querySelector('#donation-error .donation-form__error-message');
                        if (errorMessage) {
                          errorMessage.innerHTML = "\n\t\t\t\t\t\t<h3 class=\"donation-form__error-title\">Error</h3>\n\t\t\t\t\t\t<p class=\"donation-form__error-text\">".concat((response === null || response === void 0 || (_response$data = response.data) === null || _response$data === void 0 ? void 0 : _response$data.message) || 'An error occurred. Please try again.', "</p>\n\t\t\t\t\t");
                        }
                        self.onSetLoading(false);
                        return _context.a(2);
                      case 6:
                        if (!(response && response.success)) {
                          _context.n = 7;
                          break;
                        }
                        // show thank you section.
                        self.form.querySelector('.donation-form__step-panel.is-active').classList.remove('is-active');
                        self.form.querySelector('#donation-thank-you').classList.add('is-active');
                        self.onSetLoading(false);
                        return _context.a(2);
                      case 7:
                        self.onSetLoading(false);
                      case 8:
                        return _context.a(2);
                    }
                  }, _callee, this, [[1, 3]]);
                }));
                function onSubmitForm() {
                  return _onSubmitForm.apply(this, arguments);
                }
                return onSubmitForm;
              }()
            }, {
              key: "onShowThankYouSection",
              value: function onShowThankYouSection() {
                var self = this;
                self.form.querySelector('.donation-form__step-panel.is-active').classList.remove('is-active');
                self.form.querySelector('#donation-thank-you').classList.add('is-active');
              }
            }, {
              key: "onShowErrorSection",
              value: function onShowErrorSection() {
                var message = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
                var self = this;
                self.form.querySelector('.donation-form__step-panel.is-active').classList.remove('is-active');
                var errorPanel = self.form.querySelector('#donation-error');
                errorPanel.classList.add('is-active');
                if (message) {
                  var errorMessageEl = errorPanel.querySelector('.donation-form__error-message');
                  if (errorMessageEl) {
                    errorMessageEl.innerHTML = "\n\t\t\t\t\t\t<h3 class=\"donation-form__error-title\">Error</h3>\n\t\t\t\t\t\t<p class=\"donation-form__error-text\">".concat(message, "</p>\n\t\t\t\t\t");
                  }
                }
              }
            }, {
              key: "onSendData",
              value: function () {
                var _onSendData = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])(/*#__PURE__*/_regenerator().m(function _callee2(data) {
                  var ajaxurl, response;
                  return _regenerator().w(function (_context2) {
                    while (1) switch (_context2.n) {
                      case 0:
                        ajaxurl = "".concat(window.giftflowDonationForms.ajaxurl, "?action=giftflow_donation_form&wp_nonce=").concat(data.wp_nonce);
                        _context2.n = 1;
                        return fetch(ajaxurl, {
                          method: 'POST',
                          body: JSON.stringify(data),
                          headers: {
                            'Content-Type': 'application/json'
                          }
                        }).then(function (response) {
                          return response.json();
                        })["catch"](function (error) {
                          return error;
                        });
                      case 1:
                        response = _context2.v;
                        return _context2.a(2, response);
                    }
                  }, _callee2);
                }));
                function onSendData(_x2) {
                  return _onSendData.apply(this, arguments);
                }
                return onSendData;
              }()
            }, {
              key: "onDoHooks",
              value: function () {
                var _onDoHooks = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_3__["default"])(/*#__PURE__*/_regenerator().m(function _callee3() {
                  var self;
                  return _regenerator().w(function (_context3) {
                    while (1) switch (_context3.n) {
                      case 0:
                        self = this; // allow developer add hooks from outside support async function and return promise.
                        return _context3.a(2, new Promise(function (resolve, reject) {
                          self.form.dispatchEvent(new CustomEvent('donationFormBeforeSubmit', {
                            detail: {
                              self: self,
                              fields: self.fields,
                              resolve: resolve,
                              reject: reject
                            }
                          }));
                        }));
                    }
                  }, _callee3, this);
                }));
                function onDoHooks() {
                  return _onDoHooks.apply(this, arguments);
                }
                return onDoHooks;
              }()
            }, {
              key: "onSetField",
              value: function onSetField(name, value) {
                this.fields[name] = value;
                // console.log('onSetField', name, value, this.fields);
              }
            }, {
              key: "onNextStep",
              value: function onNextStep() {
                var self = this;
                self.currentStep++;

                // nav.
                self.form.querySelector('.donation-form__step-link.is-active').classList.remove('is-active');
                self.form.querySelector(".donation-form__step-item.nav-step-".concat(self.currentStep, " .donation-form__step-link")).classList.add('is-active');

                // panel.
                self.form.querySelector('.donation-form__step-panel.is-active').classList.remove('is-active');
                self.form.querySelector('.donation-form__step-panel.step-' + self.currentStep).classList.add('is-active');

                // change payment method.
                this.onChangePaymentMethod(self.fields.payment_method);
              }
            }, {
              key: "onPreviousStep",
              value: function onPreviousStep() {
                var self = this;
                self.currentStep--;

                // nav.
                self.form.querySelector('.donation-form__step-link.is-active').classList.remove('is-active');
                self.form.querySelector(".donation-form__step-item.nav-step-".concat(self.currentStep, " .donation-form__step-link")).classList.add('is-active');

                // panel.
                self.form.querySelector('.donation-form__step-panel.is-active').classList.remove('is-active');
                self.form.querySelector('.donation-form__step-panel.step-' + self.currentStep).classList.add('is-active');
              }
            }, {
              key: "setInitFields",
              value: function setInitFields(_donationForm3) {
                var _this2 = this;
                var self = this;
                var fields = _donationForm3.querySelectorAll('input[name]');
                fields.forEach(function (field) {
                  var value = field.value;

                  // validate event.target is checkbox field.
                  if (field.type === 'checkbox') {
                    value = field.checked;
                  }

                  // validate event.target is radio field.
                  if (field.type === 'radio') {
                    // get field name.
                    var fieldName = field.name;
                    // const fieldValue = field.value;
                    value = self.form.querySelector("input[name=\"".concat(fieldName, "\"]:checked")).value;
                  }
                  _this2.fields[field.name] = value;
                });
              }
            }, {
              key: "onListenerFormFieldUpdate",
              value: function onListenerFormFieldUpdate() {
                var self = this;
                this.form.addEventListener('change', function (event) {
                  self.fields[event.target.name] = event.target.value;
                  var value = event.target.value;

                  // console.log(event.target.name, value);

                  // validate event.target is checkbox field.
                  if (event.target.type === 'checkbox') {
                    value = event.target.checked;
                  }

                  // validate event.target is radio field.
                  if (event.target.type === 'radio') {
                    var fieldName = event.target.name;
                    value = self.form.querySelector("input[name=\"".concat(fieldName, "\"]:checked")).value;
                  }

                  // update UI by field.
                  self.onUpdateUIByField(event.target.name, value);
                });
              }
            }, {
              key: "onChangePaymentMethod",
              value: function onChangePaymentMethod(methodId) {
                var paymentMethodDescription = this.form.querySelector(".donation-form__payment-method-item.payment-method-".concat(methodId));
                this.form.querySelectorAll(".donation-form__payment-method-item:not(.payment-method-".concat(methodId, ")")).forEach(function (paymentMethodDescription) {
                  // remove class is-active.
                  paymentMethodDescription.classList.remove('is-active');
                  paymentMethodDescription.querySelector('.donation-form__payment-method-description').classList.add('__skip-validate-field-inner');
                  (0,_util_helpers__WEBPACK_IMPORTED_MODULE_4__.applySlideEffect)(paymentMethodDescription.querySelector('.donation-form__payment-method-description'), 'slideup');
                });
                if (paymentMethodDescription) {
                  paymentMethodDescription.classList.add('is-active');
                  paymentMethodDescription.querySelector('.donation-form__payment-method-description').classList.remove('__skip-validate-field-inner');
                  (0,_util_helpers__WEBPACK_IMPORTED_MODULE_4__.applySlideEffect)(paymentMethodDescription.querySelector('.donation-form__payment-method-description'), 'slidedown', 300, 'grid');
                }
              }
            }, {
              key: "onUpdateUIByField",
              value: function onUpdateUIByField(field, value) {
                var inputField = this.form.querySelector("input[name=\"".concat(field, "\"]"));
                if (!inputField) {
                  return;
                }
                var wrapperField = inputField.closest('.donation-form__field');
                if (!wrapperField) {
                  var type = inputField.dataset.validate;
                  var extraData = inputField.dataset.extraData ? JSON.parse(inputField.dataset.extraData) : null;
                  if (!this.onValidateValue(type, value, extraData)) {
                    inputField.classList.add('error');
                    this.onUpdateOutputField(field, '');
                  } else {
                    inputField.classList.remove('error');
                    this.onUpdateOutputField(field, value);
                  }
                  return;
                }
                if (inputField.dataset.validate) {
                  var _extraData = inputField.dataset.extraData ? JSON.parse(inputField.dataset.extraData) : null;
                  var pass = this.onValidateValue(inputField.dataset.validate, value, _extraData);
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
            }, {
              key: "onUpdateOutputField",
              value: function onUpdateOutputField(field, value) {
                var _this3 = this;
                var outputField = this.form.querySelectorAll("[data-output=\"".concat(field, "\"]"));
                if (!outputField || outputField.length === 0) {
                  return;
                }

                // if outputField is array, loop through it.
                if (outputField.length > 1) {
                  outputField.forEach(function (output) {
                    var formatTemplate = output.dataset.formatTemplate;
                    var __v = value;
                    if (formatTemplate) {
                      __v = formatTemplate.replace('{{value}}', value);
                    }

                    // update output value.
                    _this3.updateOutputValue(output, __v);
                  });
                  return;
                }
              }
            }, {
              key: "updateOutputValue",
              value: function updateOutputValue(output, value) {
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
            }, {
              key: "onClickPresetAmount",
              value: function onClickPresetAmount(event) {
                event.preventDefault();
                event.stopPropagation();
                var self = this;
                var amount = event.target.dataset.amount;
                self.form.querySelector('input[name="donation_amount"]').value = amount;
                self.form.querySelector('input[name="donation_amount"]').setAttribute('value', amount);
                var changeEvent = new Event('change', {
                  bubbles: true
                });
                self.form.querySelector('input[name="donation_amount"]').dispatchEvent(changeEvent);

                // Update UI by field
                this.onUpdateUIByField('donation_amount', amount);
                event.target.classList.add('active');
                self.form.querySelectorAll('.donation-form__preset-amount').forEach(function (presetAmount) {
                  if (presetAmount !== event.target) {
                    presetAmount.classList.remove('active');
                  }
                });
              }

              // on update amout field
            }, {
              key: "onUpdateAmountField",
              value: function onUpdateAmountField(value) {
                // remove active
                this.form.querySelectorAll('.donation-form__preset-amount').forEach(function (presetAmount) {
                  presetAmount.classList.remove('active');
                });
              }
            }, {
              key: "onValidateFieldsCurrentStep",
              value: function onValidateFieldsCurrentStep() {
                var _this4 = this;
                var self = this;
                var currentStepWrapper = this.form.querySelector('.donation-form__step-panel.is-active');
                var pass = true;
                if (!currentStepWrapper) {
                  return;
                }

                // get fields.
                var fields = currentStepWrapper.querySelectorAll('input[name][data-validate]');

                // filter fields by skip validate field inner.
                (0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__["default"])(fields).filter(function (f) {
                  var wrapperBySkipValidate = f.closest('.__skip-validate-field-inner');
                  if (wrapperBySkipValidate) {
                    return false;
                  }
                  return true;
                }).forEach(function (field) {
                  var fieldName = field.name;
                  var fieldValue = field.value;
                  var fieldValidate = field.dataset.validate;
                  var extraData = field.dataset.extraData ? JSON.parse(field.dataset.extraData) : null;
                  if (!_this4.onValidateValue(fieldValidate, fieldValue, extraData)) {
                    pass = false;
                  }
                  self.onUpdateUIByField(fieldName, fieldValue);
                });

                // get fields by custom validate.
                (0,_babel_runtime_helpers_toConsumableArray__WEBPACK_IMPORTED_MODULE_0__["default"])(currentStepWrapper.querySelectorAll('[data-custom-validate="true"]')).filter(function (s) {
                  var wrapperBySkipValidate = s.closest('.__skip-validate-field-inner');
                  if (wrapperBySkipValidate) {
                    return false;
                  }
                  return true;
                }).forEach(function (field) {
                  var status = field.dataset.customValidateStatus;
                  if (status === 'false') {
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
            }, {
              key: "onValidateValue",
              value: function onValidateValue(type, value) {
                var extraData = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : null;
                return (0,_util_helpers__WEBPACK_IMPORTED_MODULE_4__.validateValue)(type, value, extraData);
              }
            }]);
          }();
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
          document.addEventListener('initDonationForm', function (event) {
            var _ref2 = event.detail || {},
              formSelector = _ref2.formSelector,
              options = _ref2.options;
            if (formSelector) {
              // Initialize specific forms matching the selector
              document.querySelectorAll(formSelector).forEach(function (form) {
                new donationForm(form, options);
              });
            } else {
              // Initialize all donation forms if no selector provided
              document.querySelectorAll('.donation-form').forEach(function (form) {
                new donationForm(form, options);
              });
            }
            console.log('Donation forms initialized via custom event');
          });
          initDonationForm = function initDonationForm(formSelector, options) {
            document.dispatchEvent(new CustomEvent('initDonationForm', {
              detail: {
                formSelector: formSelector,
                options: options
              }
            }));
          };
          w.initDonationForm = initDonationForm;

          // dom loaded
          document.addEventListener('DOMContentLoaded', function () {
            // initialize all donation forms
            initDonationForm('.donation-form', {
              paymentMethodSelected: 'stripe'
            });
          });
        case 1:
          return _context4.a(2);
      }
    }, _callee4);
  }));
  return function (_x) {
    return _ref.apply(this, arguments);
  };
})()(window);
})();

/******/ })()
;