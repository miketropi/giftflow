/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./admin/css/admin.scss":
/*!******************************!*\
  !*** ./admin/css/admin.scss ***!
  \******************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/css/block-campaign-single-content.scss":
/*!*******************************************************!*\
  !*** ./assets/css/block-campaign-single-content.scss ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/css/block-campaign-status-bar.scss":
/*!***************************************************!*\
  !*** ./assets/css/block-campaign-status-bar.scss ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/css/common.scss":
/*!********************************!*\
  !*** ./assets/css/common.scss ***!
  \********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/css/donation-form.scss":
/*!***************************************!*\
  !*** ./assets/css/donation-form.scss ***!
  \***************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./assets/js/common.js":
/*!*****************************!*\
  !*** ./assets/js/common.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _util_comment_form_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./util/comment-form.js */ "./assets/js/util/comment-form.js");
/* harmony import */ var _util_comment_form_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_util_comment_form_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _util_modal_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./util/modal.js */ "./assets/js/util/modal.js");
/* harmony import */ var _util_helpers_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./util/helpers.js */ "./assets/js/util/helpers.js");
/* harmony import */ var _util_donation_button_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./util/donation-button.js */ "./assets/js/util/donation-button.js");
/* harmony import */ var photoswipe_lightbox__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! photoswipe/lightbox */ "./node_modules/photoswipe/dist/photoswipe-lightbox.esm.js");
/* harmony import */ var photoswipe__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! photoswipe */ "./node_modules/photoswipe/dist/photoswipe.esm.js");
/* harmony import */ var photoswipe_style_css__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! photoswipe/style.css */ "./node_modules/photoswipe/dist/photoswipe.css");

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
 * GiftFlow Common JS
 */





// photoswipe


(function (w, $) {
  "use strict";

  var _giftflow_common = giftflow_common,
    ajax_url = _giftflow_common.ajax_url,
    nonce = _giftflow_common.nonce;
  w.giftflow = w.giftflow || {};
  var gfw = w.giftflow;

  // load donation list
  gfw.loadDonationListPaginationTemplate_Handle = /*#__PURE__*/function () {
    var _ref = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee(elem) {
      var _elem$dataset, campaign, page, container, res, _res$data, __html, __replace_content_selector;
      return _regenerator().w(function (_context) {
        while (1) switch (_context.n) {
          case 0:
            _elem$dataset = elem.dataset, campaign = _elem$dataset.campaign, page = _elem$dataset.page;
            if (!(!campaign || !page)) {
              _context.n = 1;
              break;
            }
            console.error('Missing campaign or page data attributes');
            return _context.a(2);
          case 1:
            container = elem.closest(".__donations-list-by-campaign-".concat(campaign));
            if (container) {
              _context.n = 2;
              break;
            }
            console.error('Container element not found');
            return _context.a(2);
          case 2:
            container.classList.add('gfw-loading-spinner');
            _context.n = 3;
            return $.ajax({
              url: ajax_url,
              type: 'POST',
              data: {
                action: 'giftflow_get_pagination_donation_list_html',
                campaign: campaign,
                page: page,
                nonce: nonce
              }
            });
          case 3:
            res = _context.v;
            container.classList.remove('gfw-loading-spinner');

            // res successful
            if (res.success) {
              _res$data = res.data, __html = _res$data.__html, __replace_content_selector = _res$data.__replace_content_selector;
              if (__replace_content_selector) {
                (0,_util_helpers_js__WEBPACK_IMPORTED_MODULE_3__.replaceContentBySelector)(__replace_content_selector, __html);
              }
            } else {
              console.error('Error loading donation list pagination template');
            }
          case 4:
            return _context.a(2);
        }
      }, _callee);
    }));
    return function (_x) {
      return _ref.apply(this, arguments);
    };
  }();
  gfw.donationButton_Handle = _util_donation_button_js__WEBPACK_IMPORTED_MODULE_4__["default"];

  // lightbox
  gfw.lightbox_initialize = function () {
    var galleryElements = document.querySelector('.giftflow-campaign-single-images:not(.giftflow-campaign-single-images--placeholder)');
    if (!galleryElements || galleryElements.length === 0) {
      return;
    }
    var sourceData = Array.from(galleryElements.querySelectorAll('.giftflow-campaign-single-images-image')).map(function (element) {
      return {
        src: element.dataset.pswpSrc,
        width: element.dataset.pswpWidth,
        height: element.dataset.pswpHeight
      };
    });
    var lightbox = new photoswipe_lightbox__WEBPACK_IMPORTED_MODULE_5__["default"]({
      dataSource: sourceData,
      pswpModule: photoswipe__WEBPACK_IMPORTED_MODULE_6__["default"]
    });
    lightbox.init();
    galleryElements.querySelector('.giftflow-campaign-single-images-lightbox-open-btn').addEventListener('click', function () {
      lightbox.loadAndOpen();
    });
  };

  // dom loaded
  document.addEventListener('DOMContentLoaded', function () {
    gfw.lightbox_initialize();
  });
})(window, jQuery);

/***/ }),

/***/ "./assets/js/util/comment-form.js":
/*!****************************************!*\
  !*** ./assets/js/util/comment-form.js ***!
  \****************************************/
/***/ (() => {

/**
 * Comment Form JS
 */

(function (w, $) {
  'use strict';

  var replyCommentHandle = function replyCommentHandle() {
    var originalFormPosition = null;
    var originalFormTitle = null;
    var cancelReplyHtml = "<small><a rel=\"nofollow\" id=\"cancel-comment-reply-link\" href=\"#\">Cancel reply</a></small>";
    $(document).on('click', '.gfw-campaign-comments-list .comment-reply-link', function (e) {
      e.preventDefault();
      var commentId = $(this).data('commentid');
      var titleReply = $(this).data('replyto') || 'Leave a Reply';
      var form = $('#respond');
      var parentInput = form.find('input[name="comment_parent"]');
      if (!commentId || !form.length || !parentInput.length) {
        console.error('Missing comment ID or form elements');
        return;
      }

      // Store original position if not already stored
      if (!originalFormPosition) {
        originalFormPosition = form.parent();
      }
      if (!originalFormTitle) {
        originalFormTitle = form.find('#reply-title').html();
      }

      // set parent comment ID
      parentInput.val(commentId);

      // set form title titleReply
      form.find('#reply-title').html("".concat(titleReply, " ").concat(cancelReplyHtml));

      // move form
      var commentElement = $("#comment-".concat(commentId, " > .comment-body"));
      if (commentElement.length) {
        commentElement.after(form);
        form.find('textarea').focus();
      }
    });

    // Add cancel reply handler
    $(document).on('click', '#cancel-comment-reply-link', function (e) {
      e.preventDefault();
      var form = $('#respond');
      var parentInput = form.find('input[name="comment_parent"]');

      // Reset parent comment ID
      parentInput.val('0');

      // Return form to original position
      if (originalFormPosition) {
        originalFormPosition.append(form);
      }

      // Reset form title
      if (originalFormTitle) {
        form.find('#reply-title').html(originalFormTitle);
      }
    });
  };
  $(function () {
    replyCommentHandle();
  });
})(window, jQuery);

/***/ }),

/***/ "./assets/js/util/donation-button.js":
/*!*******************************************!*\
  !*** ./assets/js/util/donation-button.js ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ donationButton_Handle)
/* harmony export */ });
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");

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
var _giftflow_common = giftflow_common,
  ajax_url = _giftflow_common.ajax_url,
  nonce = _giftflow_common.nonce;
function donationButton_Handle(_x) {
  return _donationButton_Handle.apply(this, arguments);
}
function _donationButton_Handle() {
  _donationButton_Handle = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee(el) {
    var _el$dataset, campaignId, campaignTitle, ajaxModal;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.n) {
        case 0:
          _el$dataset = el.dataset, campaignId = _el$dataset.campaignId, campaignTitle = _el$dataset.campaignTitle;
          ajaxModal = new GiftFlowModal({
            ajax: true,
            ajaxUrl: "".concat(ajax_url, "?action=giftflow_get_campaign_donation_form&campaign_id=").concat(campaignId, "&nonce=").concat(nonce),
            onLoad: function onLoad(content, modal) {
              // console.log('Content loaded:', modal);

              var donationForm = modal.contentElement.querySelector('form.donation-form');
              if (donationForm) {
                new window.donationForm_Class(donationForm, {
                  paymentMethodSelected: 'stripe'
                });
              }
            },
            className: 'modal-transparent-wrapper',
            width: '800px',
            onClose: function onClose(_) {
              _.destroy();
            }
          });
          ajaxModal.open();
        case 1:
          return _context.a(2);
      }
    }, _callee);
  }));
  return _donationButton_Handle.apply(this, arguments);
}

/***/ }),

/***/ "./assets/js/util/helpers.js":
/*!***********************************!*\
  !*** ./assets/js/util/helpers.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
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

/***/ "./assets/js/util/modal.js":
/*!*********************************!*\
  !*** ./assets/js/util/modal.js ***!
  \*********************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* module decorator */ module = __webpack_require__.hmd(module);




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
function ownKeys(e, r) {
  var t = Object.keys(e);
  if (Object.getOwnPropertySymbols) {
    var o = Object.getOwnPropertySymbols(e);
    r && (o = o.filter(function (r) {
      return Object.getOwnPropertyDescriptor(e, r).enumerable;
    })), t.push.apply(t, o);
  }
  return t;
}
function _objectSpread(e) {
  for (var r = 1; r < arguments.length; r++) {
    var t = null != arguments[r] ? arguments[r] : {};
    r % 2 ? ownKeys(Object(t), !0).forEach(function (r) {
      (0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_1__["default"])(e, r, t[r]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) {
      Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r));
    });
  }
  return e;
}
/**
 * GiftFlow Modal Library
 * A clean, simple, and easy-to-use modal system with AJAX support
 * 
 * QUICK START EXAMPLES:
 * 
 * Basic Modal:
 * const modal = new GiftFlowModal({
 *     content: '<h2>Hello World!</h2><p>This is a simple modal.</p>',
 *     closeButton: true
 * });
 * modal.open();
 * 
 * AJAX Modal:
 * const ajaxModal = new GiftFlowModal({
 *     ajax: true,
 *     ajaxUrl: '/api/get-content',
 *     onLoad: (content, modal) => console.log('Content loaded:', content)
 * });
 * ajaxModal.open();
 * 
 * Quick Dialogs:
 * GiftFlowModal.alert('Operation completed!', 'Success');
 * const confirmed = await GiftFlowModal.confirm('Are you sure?');
 * const value = await GiftFlowModal.prompt('Enter your name:', 'John Doe');
 * 
 * Custom Modal:
 * const customModal = new GiftFlowModal({
 *     content: '<p>Custom sized modal</p>',
 *     width: '800px',
 *     animation: 'zoom',
 *     duration: 500
 * });
 * customModal.open();
 * 
 * For complete documentation and examples, see:
 * wp-content/plugins/giftflow/assets/js/util/README.md
 */
/**
 * GiftFlow Modal Library
 * A clean, simple, and easy-to-use modal system with AJAX support
 */
var GiftFlowModal = /*#__PURE__*/function () {
  function GiftFlowModal() {
    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_2__["default"])(this, GiftFlowModal);
    this.options = _objectSpread({
      // Modal options
      id: options.id || 'giftflow-modal',
      className: options.className || 'giftflow-modal',
      overlay: options.overlay !== false,
      closeOnOverlay: options.closeOnOverlay !== false,
      closeOnEscape: options.closeOnEscape !== false,
      closeButton: options.closeButton !== false,
      // Animation options
      animation: options.animation || 'fade',
      // 'fade', 'slide', 'zoom'
      duration: options.duration || 300,
      easing: options.easing || 'ease-in-out',
      // Content options
      content: options.content || '',
      ajax: options.ajax || false,
      ajaxUrl: options.ajaxUrl || '',
      ajaxData: options.ajaxData || {},
      ajaxMethod: options.ajaxMethod || 'GET',
      // Size options
      width: options.width || 'auto',
      maxWidth: options.maxWidth || '90vw',
      height: options.height || 'auto',
      maxHeight: options.maxHeight || '90vh',
      // Position options
      position: options.position || 'center',
      // 'center', 'top', 'bottom'

      // Callbacks
      onOpen: options.onOpen || null,
      onClose: options.onClose || null,
      onLoad: options.onLoad || null,
      onError: options.onError || null,
      // Auto-close options
      autoClose: options.autoClose || false,
      autoCloseDelay: options.autoCloseDelay || 5000,
      // Accessibility
      ariaLabel: options.ariaLabel || 'Modal dialog',
      focusTrap: options.focusTrap !== false
    }, options);
    this.isOpen = false;
    this.modalElement = null;
    this.overlayElement = null;
    this.contentElement = null;
    this.closeButtonElement = null;
    this.focusableElements = [];
    this.lastFocusedElement = null;
    this.autoCloseTimer = null;
    this.init();
  }

  /**
   * Initialize the modal
   */
  return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_3__["default"])(GiftFlowModal, [{
    key: "init",
    value: function init() {
      this.createModal();
      this.bindEvents();
      if (this.options.ajax && this.options.ajaxUrl) {
        this.loadAjaxContent();
      }
    }

    /**
     * Create modal HTML structure
     */
  }, {
    key: "createModal",
    value: function createModal() {
      // Create overlay
      if (this.options.overlay) {
        this.overlayElement = document.createElement('div');
        this.overlayElement.className = 'giftflow-modal__overlay';
        this.overlayElement.setAttribute('aria-hidden', 'true');
      }

      // Create modal container
      this.modalElement = document.createElement('div');
      this.modalElement.id = this.options.id;
      this.modalElement.className = "giftflow-modal ".concat(this.options.className);
      this.modalElement.setAttribute('role', 'dialog');
      this.modalElement.setAttribute('aria-modal', 'true');
      this.modalElement.setAttribute('aria-label', this.options.ariaLabel);
      this.modalElement.setAttribute('tabindex', '-1');

      // Set modal dimensions
      if (this.options.width !== 'auto') {
        this.modalElement.style.width = this.options.width;
      }
      if (this.options.maxWidth !== '90vw') {
        this.modalElement.style.maxWidth = this.options.maxWidth;
      }
      if (this.options.height !== 'auto') {
        this.modalElement.style.height = this.options.height;
      }
      if (this.options.maxHeight !== '90vh') {
        this.modalElement.style.maxHeight = this.options.maxHeight;
      }

      // Create modal content
      this.contentElement = document.createElement('div');
      this.contentElement.className = 'giftflow-modal__content';

      // Add close button if enabled
      if (this.options.closeButton) {
        this.closeButtonElement = document.createElement('button');
        this.closeButtonElement.className = 'giftflow-modal__close';
        this.closeButtonElement.setAttribute('type', 'button');
        this.closeButtonElement.setAttribute('aria-label', 'Close modal');
        this.closeButtonElement.innerHTML = "<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"lucide lucide-x-icon lucide-x\"><path d=\"M18 6 6 18\"/><path d=\"m6 6 12 12\"/></svg>";
        this.modalElement.appendChild(this.closeButtonElement);
      }

      // Add content
      if (this.options.content && !this.options.ajax) {
        this.contentElement.innerHTML = this.options.content;
      }
      this.modalElement.appendChild(this.contentElement);

      // Append to DOM
      if (this.options.overlay) {
        this.overlayElement.appendChild(this.modalElement);
        document.body.appendChild(this.overlayElement);
      } else {
        document.body.appendChild(this.modalElement);
      }

      // Add animation classes
      this.modalElement.classList.add("giftflow-modal--".concat(this.options.animation));
    }

    /**
     * Bind event listeners
     */
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      var _this = this;
      // Close button click
      if (this.closeButtonElement) {
        this.closeButtonElement.addEventListener('click', function () {
          return _this.close();
        });
      }

      // Overlay click
      if (this.options.overlay && this.options.closeOnOverlay) {
        this.overlayElement.addEventListener('click', function (e) {
          if (e.target === _this.overlayElement) {
            _this.close();
          }
        });
      }

      // Escape key
      if (this.options.closeOnEscape) {
        document.addEventListener('keydown', function (e) {
          if (e.key === 'Escape' && _this.isOpen) {
            _this.close();
          }
        });
      }

      // Focus trap
      if (this.options.focusTrap) {
        this.modalElement.addEventListener('keydown', function (e) {
          if (e.key === 'Tab') {
            _this.handleTabKey(e);
          }
        });
      }
    }

    /**
     * Handle tab key for focus trapping
     */
  }, {
    key: "handleTabKey",
    value: function handleTabKey(e) {
      this.focusableElements = this.modalElement.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
      var firstElement = this.focusableElements[0];
      var lastElement = this.focusableElements[this.focusableElements.length - 1];
      if (e.shiftKey) {
        if (document.activeElement === firstElement) {
          e.preventDefault();
          lastElement.focus();
        }
      } else {
        if (document.activeElement === lastElement) {
          e.preventDefault();
          firstElement.focus();
        }
      }
    }

    /**
     * Load content via AJAX
     */
  }, {
    key: "loadAjaxContent",
    value: function () {
      var _loadAjaxContent = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee() {
        var response, content, _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.p = _context.n) {
            case 0:
              _context.p = 0;
              this.showLoading();
              _context.n = 1;
              return fetch(this.options.ajaxUrl, {
                method: this.options.ajaxMethod,
                headers: {
                  'Content-Type': 'application/json'
                },
                body: this.options.ajaxMethod === 'POST' ? JSON.stringify(this.options.ajaxData) : undefined
              });
            case 1:
              response = _context.v;
              if (response.ok) {
                _context.n = 2;
                break;
              }
              throw new Error("HTTP error! status: ".concat(response.status));
            case 2:
              _context.n = 3;
              return response.text();
            case 3:
              content = _context.v;
              this.contentElement.innerHTML = content;
              if (this.options.onLoad) {
                this.options.onLoad(content, this);
              }
              _context.n = 5;
              break;
            case 4:
              _context.p = 4;
              _t = _context.v;
              console.error('Modal AJAX error:', _t);
              this.contentElement.innerHTML = '<div class="giftflow-modal__error">Failed to load content</div>';
              if (this.options.onError) {
                this.options.onError(_t, this);
              }
            case 5:
              return _context.a(2);
          }
        }, _callee, this, [[0, 4]]);
      }));
      function loadAjaxContent() {
        return _loadAjaxContent.apply(this, arguments);
      }
      return loadAjaxContent;
    }()
    /**
     * Show loading state
     */
  }, {
    key: "showLoading",
    value: function showLoading() {
      this.contentElement.innerHTML = '<div class="giftflow-modal__loading">Loading...</div>';
    }

    /**
     * Open the modal
     */
  }, {
    key: "open",
    value: function open() {
      var _this2 = this;
      if (this.isOpen) return;
      this.isOpen = true;
      this.lastFocusedElement = document.activeElement;

      // Show modal
      if (this.options.overlay) {
        this.overlayElement.style.display = 'flex';
        this.overlayElement.setAttribute('aria-hidden', 'false');
      }
      this.modalElement.style.display = 'block';

      // Trigger animation
      requestAnimationFrame(function () {
        _this2.modalElement.classList.add('giftflow-modal--open');
        if (_this2.options.overlay) {
          _this2.overlayElement.classList.add('giftflow-modal__overlay--open');
        }
      });

      // Focus modal
      this.modalElement.focus();

      // Set auto-close timer
      if (this.options.autoClose) {
        this.autoCloseTimer = setTimeout(function () {
          _this2.close();
        }, this.options.autoCloseDelay);
      }

      // Prevent body scroll
      document.body.style.overflow = 'hidden';

      // Call onOpen callback
      if (this.options.onOpen) {
        this.options.onOpen(this);
      }
    }

    /**
     * Close the modal
     */
  }, {
    key: "close",
    value: function close() {
      var _this3 = this;
      if (!this.isOpen) return;
      this.isOpen = false;

      // Clear auto-close timer
      if (this.autoCloseTimer) {
        clearTimeout(this.autoCloseTimer);
        this.autoCloseTimer = null;
      }

      // Trigger close animation
      this.modalElement.classList.remove('giftflow-modal--open');
      if (this.options.overlay) {
        this.overlayElement.classList.remove('giftflow-modal__overlay--open');
      }

      // Wait for animation to complete
      setTimeout(function () {
        _this3.modalElement.style.display = 'none';
        if (_this3.options.overlay) {
          _this3.overlayElement.style.display = 'none';
          _this3.overlayElement.setAttribute('aria-hidden', 'true');
        }

        // Restore body scroll
        document.body.style.overflow = '';

        // Restore focus
        if (_this3.lastFocusedElement) {
          _this3.lastFocusedElement.focus();
        }

        // Call onClose callback
        if (_this3.options.onClose) {
          _this3.options.onClose(_this3);
        }
      }, this.options.duration);
    }

    /**
     * Update modal content
     */
  }, {
    key: "setContent",
    value: function setContent(content) {
      this.contentElement.innerHTML = content;
    }

    /**
     * Update modal options
     */
  }, {
    key: "updateOptions",
    value: function updateOptions(newOptions) {
      this.options = _objectSpread(_objectSpread({}, this.options), newOptions);

      // Update dimensions if changed
      if (newOptions.width !== undefined) {
        this.modalElement.style.width = newOptions.width;
      }
      if (newOptions.maxWidth !== undefined) {
        this.modalElement.style.maxWidth = newOptions.maxWidth;
      }
      if (newOptions.height !== undefined) {
        this.modalElement.style.height = newOptions.height;
      }
      if (newOptions.maxHeight !== undefined) {
        this.modalElement.style.maxHeight = newOptions.maxHeight;
      }
    }

    /**
     * Destroy the modal
     */
  }, {
    key: "destroy",
    value: function destroy() {
      this.close();
      if (this.autoCloseTimer) {
        clearTimeout(this.autoCloseTimer);
      }
      if (this.modalElement && this.modalElement.parentNode) {
        this.modalElement.parentNode.removeChild(this.modalElement);
      }
      if (this.overlayElement && this.overlayElement.parentNode) {
        this.overlayElement.parentNode.removeChild(this.overlayElement);
      }
      this.modalElement = null;
      this.overlayElement = null;
      this.contentElement = null;
      this.closeButtonElement = null;
    }
  }]);
}();
/**
 * Static methods for easy modal creation
 */
GiftFlowModal.create = function (options) {
  return new GiftFlowModal(options);
};
GiftFlowModal.alert = function (message) {
  var title = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'Alert';
  var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
  var modal = new GiftFlowModal(_objectSpread({
    content: "\n            <div class=\"giftflow-modal__header\">\n                <h3>".concat(title, "</h3>\n            </div>\n            <div class=\"giftflow-modal__body\">\n                <p>").concat(message, "</p>\n            </div>\n            <div class=\"giftflow-modal__footer\">\n                <button class=\"giftflow-modal__btn giftflow-modal__btn--primary\" onclick=\"this.closest('.giftflow-modal').giftflowModal.close()\">\n                    OK\n                </button>\n            </div>\n        "),
    closeButton: true,
    closeOnOverlay: false,
    closeOnEscape: true
  }, options));

  // Store modal reference for the close button
  modal.modalElement.giftflowModal = modal;
  modal.open();
  return modal;
};
GiftFlowModal.confirm = function (message) {
  var title = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'Confirm';
  var options = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
  return new Promise(function (resolve) {
    var modal = new GiftFlowModal(_objectSpread({
      content: "\n                <div class=\"giftflow-modal__header\">\n                    <h3>".concat(title, "</h3>\n                </div>\n                <div class=\"giftflow-modal__body\">\n                    <p>").concat(message, "</p>\n                </div>\n                <div class=\"giftflow-modal__footer\">\n                    <button class=\"giftflow-modal__btn giftflow-modal__btn--secondary\" onclick=\"this.closest('.giftflow-modal').giftflowModal.confirmResult(false)\">\n                        Cancel\n                    </button>\n                    <button class=\"giftflow-modal__btn giftflow-modal__btn--primary\" onclick=\"this.closest('.giftflow-modal').giftflowModal.confirmResult(true)\">\n                        OK\n                    </button>\n                </div>\n            "),
      closeButton: true,
      closeOnOverlay: false,
      closeOnEscape: true,
      onClose: function onClose() {
        return resolve(false);
      }
    }, options));

    // Store modal reference and confirm method
    modal.modalElement.giftflowModal = modal;
    modal.confirmResult = function (result) {
      modal.close();
      resolve(result);
    };
    modal.open();
  });
};
GiftFlowModal.prompt = function (message) {
  var defaultValue = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';
  var title = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'Input';
  var options = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : {};
  return new Promise(function (resolve) {
    var modal = new GiftFlowModal(_objectSpread({
      content: "\n                <div class=\"giftflow-modal__header\">\n                    <h3>".concat(title, "</h3>\n                </div>\n                <div class=\"giftflow-modal__body\">\n                    <p>").concat(message, "</p>\n                    <input type=\"text\" class=\"giftflow-modal__input\" value=\"").concat(defaultValue, "\" placeholder=\"Enter value...\">\n                </div>\n                <div class=\"giftflow-modal__footer\">\n                    <button class=\"giftflow-modal__btn giftflow-modal__btn--secondary\" onclick=\"this.closest('.giftflow-modal').giftflowModal.promptResult(null)\">\n                        Cancel\n                    </button>\n                    <button class=\"giftflow-modal__btn giftflow-modal__btn--primary\" onclick=\"this.closest('.giftflow-modal').giftflowModal.promptResult(this.closest('.giftflow-modal').querySelector('.giftflow-modal__input').value)\">\n                        OK\n                    </button>\n                </div>\n            "),
      closeButton: true,
      closeOnOverlay: false,
      closeOnEscape: true,
      onClose: function onClose() {
        return resolve(null);
      }
    }, options));

    // Store modal reference and prompt method
    modal.modalElement.giftflowModal = modal;
    modal.promptResult = function (result) {
      modal.close();
      resolve(result);
    };
    modal.open();

    // Focus input
    setTimeout(function () {
      var input = modal.modalElement.querySelector('.giftflow-modal__input');
      if (input) {
        input.focus();
        input.select();
      }
    }, 100);
  });
};

// Export for different module systems
if ( true && module.exports) {
  module.exports = GiftFlowModal;
} else if (typeof define === 'function' && __webpack_require__.amdO) {
  define(function () {
    return GiftFlowModal;
  });
} else {
  window.GiftFlowModal = GiftFlowModal;
}

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
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

"use strict";
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

"use strict";
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

/***/ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/defineProperty.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _defineProperty)
/* harmony export */ });
/* harmony import */ var _toPropertyKey_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./toPropertyKey.js */ "./node_modules/@babel/runtime/helpers/esm/toPropertyKey.js");

function _defineProperty(e, r, t) {
  return (r = (0,_toPropertyKey_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r)) in e ? Object.defineProperty(e, r, {
    value: t,
    enumerable: !0,
    configurable: !0,
    writable: !0
  }) : e[r] = t, e;
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/toPrimitive.js":
/*!****************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/toPrimitive.js ***!
  \****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
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

"use strict";
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

"use strict";
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

/***/ "./node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[10].oneOf[1].use[1]!./node_modules/postcss-loader/dist/cjs.js??ruleSet[1].rules[10].oneOf[1].use[2]!./node_modules/photoswipe/dist/photoswipe.css":
/*!*****************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[10].oneOf[1].use[1]!./node_modules/postcss-loader/dist/cjs.js??ruleSet[1].rules[10].oneOf[1].use[2]!./node_modules/photoswipe/dist/photoswipe.css ***!
  \*****************************************************************************************************************************************************************************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../../css-loader/dist/runtime/api.js */ "./node_modules/css-loader/dist/runtime/api.js");
/* harmony import */ var _css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0__);
// Imports

var ___CSS_LOADER_EXPORT___ = _css_loader_dist_runtime_api_js__WEBPACK_IMPORTED_MODULE_0___default()(function(i){return i[1]});
// Module
___CSS_LOADER_EXPORT___.push([module.id, "/*! PhotoSwipe main CSS by Dmytro Semenov | photoswipe.com */\r\n\r\n.pswp {\r\n  --pswp-bg: #000;\r\n  --pswp-placeholder-bg: #222;\r\n  \r\n\r\n  --pswp-root-z-index: 100000;\r\n  \r\n  --pswp-preloader-color: rgba(79, 79, 79, 0.4);\r\n  --pswp-preloader-color-secondary: rgba(255, 255, 255, 0.9);\r\n  \r\n  /* defined via js:\r\n  --pswp-transition-duration: 333ms; */\r\n  \r\n  --pswp-icon-color: #fff;\r\n  --pswp-icon-color-secondary: #4f4f4f;\r\n  --pswp-icon-stroke-color: #4f4f4f;\r\n  --pswp-icon-stroke-width: 2px;\r\n\r\n  --pswp-error-text-color: var(--pswp-icon-color);\r\n}\r\n\r\n\r\n/*\r\n\tStyles for basic PhotoSwipe (pswp) functionality (sliding area, open/close transitions)\r\n*/\r\n\r\n.pswp {\r\n\tposition: fixed;\r\n\ttop: 0;\r\n\tleft: 0;\r\n\twidth: 100%;\r\n\theight: 100%;\r\n\tz-index: var(--pswp-root-z-index);\r\n\tdisplay: none;\r\n\ttouch-action: none;\r\n\toutline: 0;\r\n\topacity: 0.003;\r\n\tcontain: layout style size;\r\n\t-webkit-tap-highlight-color: rgba(0, 0, 0, 0);\r\n}\r\n\r\n/* Prevents focus outline on the root element,\r\n  (it may be focused initially) */\r\n.pswp:focus {\r\n  outline: 0;\r\n}\r\n\r\n.pswp * {\r\n  box-sizing: border-box;\r\n}\r\n\r\n.pswp img {\r\n  max-width: none;\r\n}\r\n\r\n.pswp--open {\r\n\tdisplay: block;\r\n}\r\n\r\n.pswp,\r\n.pswp__bg {\r\n\ttransform: translateZ(0);\r\n\twill-change: opacity;\r\n}\r\n\r\n.pswp__bg {\r\n  opacity: 0.005;\r\n\tbackground: var(--pswp-bg);\r\n}\r\n\r\n.pswp,\r\n.pswp__scroll-wrap {\r\n\toverflow: hidden;\r\n}\r\n\r\n.pswp__scroll-wrap,\r\n.pswp__bg,\r\n.pswp__container,\r\n.pswp__item,\r\n.pswp__content,\r\n.pswp__img,\r\n.pswp__zoom-wrap {\r\n\tposition: absolute;\r\n\ttop: 0;\r\n\tleft: 0;\r\n\twidth: 100%;\r\n\theight: 100%;\r\n}\r\n\r\n.pswp__img,\r\n.pswp__zoom-wrap {\r\n\twidth: auto;\r\n\theight: auto;\r\n}\r\n\r\n.pswp--click-to-zoom.pswp--zoom-allowed .pswp__img {\r\n\tcursor: zoom-in;\r\n}\r\n\r\n.pswp--click-to-zoom.pswp--zoomed-in .pswp__img {\r\n\tcursor: move;\r\n\tcursor: grab;\r\n}\r\n\r\n.pswp--click-to-zoom.pswp--zoomed-in .pswp__img:active {\r\n  cursor: grabbing;\r\n}\r\n\r\n/* :active to override grabbing cursor */\r\n.pswp--no-mouse-drag.pswp--zoomed-in .pswp__img,\r\n.pswp--no-mouse-drag.pswp--zoomed-in .pswp__img:active,\r\n.pswp__img {\r\n\tcursor: zoom-out;\r\n}\r\n\r\n\r\n/* Prevent selection and tap highlights */\r\n.pswp__container,\r\n.pswp__img,\r\n.pswp__button,\r\n.pswp__counter {\r\n\t-webkit-user-select: none;\r\n\t-moz-user-select: none;\r\n\tuser-select: none;\r\n}\r\n\r\n.pswp__item {\r\n\t/* z-index for fade transition */\r\n\tz-index: 1;\r\n\toverflow: hidden;\r\n}\r\n\r\n.pswp__hidden {\r\n\tdisplay: none !important;\r\n}\r\n\r\n/* Allow to click through pswp__content element, but not its children */\r\n.pswp__content {\r\n  pointer-events: none;\r\n}\r\n.pswp__content > * {\r\n  pointer-events: auto;\r\n}\r\n\r\n\r\n/*\r\n\r\n  PhotoSwipe UI\r\n\r\n*/\r\n\r\n/*\r\n\tError message appears when image is not loaded\r\n\t(JS option errorMsg controls markup)\r\n*/\r\n.pswp__error-msg-container {\r\n  display: grid;\r\n}\r\n.pswp__error-msg {\r\n\tmargin: auto;\r\n\tfont-size: 1em;\r\n\tline-height: 1;\r\n\tcolor: var(--pswp-error-text-color);\r\n}\r\n\r\n/*\r\nclass pswp__hide-on-close is applied to elements that\r\nshould hide (for example fade out) when PhotoSwipe is closed\r\nand show (for example fade in) when PhotoSwipe is opened\r\n */\r\n.pswp .pswp__hide-on-close {\r\n\topacity: 0.005;\r\n\twill-change: opacity;\r\n\ttransition: opacity var(--pswp-transition-duration) cubic-bezier(0.4, 0, 0.22, 1);\r\n\tz-index: 10; /* always overlap slide content */\r\n\tpointer-events: none; /* hidden elements should not be clickable */\r\n}\r\n\r\n/* class pswp--ui-visible is added when opening or closing transition starts */\r\n.pswp--ui-visible .pswp__hide-on-close {\r\n\topacity: 1;\r\n\tpointer-events: auto;\r\n}\r\n\r\n/* <button> styles, including css reset */\r\n.pswp__button {\r\n\tposition: relative;\r\n\tdisplay: block;\r\n\twidth: 50px;\r\n\theight: 60px;\r\n\tpadding: 0;\r\n\tmargin: 0;\r\n\toverflow: hidden;\r\n\tcursor: pointer;\r\n\tbackground: none;\r\n\tborder: 0;\r\n\tbox-shadow: none;\r\n\topacity: 0.85;\r\n\t-webkit-appearance: none;\r\n\t-webkit-touch-callout: none;\r\n}\r\n\r\n.pswp__button:hover,\r\n.pswp__button:active,\r\n.pswp__button:focus {\r\n  transition: none;\r\n  padding: 0;\r\n  background: none;\r\n  border: 0;\r\n  box-shadow: none;\r\n  opacity: 1;\r\n}\r\n\r\n.pswp__button:disabled {\r\n  opacity: 0.3;\r\n  cursor: auto;\r\n}\r\n\r\n.pswp__icn {\r\n  fill: var(--pswp-icon-color);\r\n  color: var(--pswp-icon-color-secondary);\r\n}\r\n\r\n.pswp__icn {\r\n  position: absolute;\r\n  top: 14px;\r\n  left: 9px;\r\n  width: 32px;\r\n  height: 32px;\r\n  overflow: hidden;\r\n  pointer-events: none;\r\n}\r\n\r\n.pswp__icn-shadow {\r\n  stroke: var(--pswp-icon-stroke-color);\r\n  stroke-width: var(--pswp-icon-stroke-width);\r\n  fill: none;\r\n}\r\n\r\n.pswp__icn:focus {\r\n\toutline: 0;\r\n}\r\n\r\n/*\r\n\tdiv element that matches size of large image,\r\n\tlarge image loads on top of it,\r\n\tused when msrc is not provided\r\n*/\r\ndiv.pswp__img--placeholder,\r\n.pswp__img--with-bg {\r\n\tbackground: var(--pswp-placeholder-bg);\r\n}\r\n\r\n.pswp__top-bar {\r\n\tposition: absolute;\r\n\tleft: 0;\r\n\ttop: 0;\r\n\twidth: 100%;\r\n\theight: 60px;\r\n\tdisplay: flex;\r\n  flex-direction: row;\r\n  justify-content: flex-end;\r\n\tz-index: 10;\r\n\r\n\t/* allow events to pass through top bar itself */\r\n\tpointer-events: none !important;\r\n}\r\n.pswp__top-bar > * {\r\n  pointer-events: auto;\r\n  /* this makes transition significantly more smooth,\r\n     even though inner elements are not animated */\r\n  will-change: opacity;\r\n}\r\n\r\n\r\n/*\r\n\r\n  Close button\r\n\r\n*/\r\n.pswp__button--close {\r\n  margin-right: 6px;\r\n}\r\n\r\n\r\n/*\r\n\r\n  Arrow buttons\r\n\r\n*/\r\n.pswp__button--arrow {\r\n  position: absolute;\r\n  top: 0;\r\n  width: 75px;\r\n  height: 100px;\r\n  top: 50%;\r\n  margin-top: -50px;\r\n}\r\n\r\n.pswp__button--arrow:disabled {\r\n  display: none;\r\n  cursor: default;\r\n}\r\n\r\n.pswp__button--arrow .pswp__icn {\r\n  top: 50%;\r\n  margin-top: -30px;\r\n  width: 60px;\r\n  height: 60px;\r\n  background: none;\r\n  border-radius: 0;\r\n}\r\n\r\n.pswp--one-slide .pswp__button--arrow {\r\n  display: none;\r\n}\r\n\r\n/* hide arrows on touch screens */\r\n.pswp--touch .pswp__button--arrow {\r\n  visibility: hidden;\r\n}\r\n\r\n/* show arrows only after mouse was used */\r\n.pswp--has_mouse .pswp__button--arrow {\r\n  visibility: visible;\r\n}\r\n\r\n.pswp__button--arrow--prev {\r\n  right: auto;\r\n  left: 0px;\r\n}\r\n\r\n.pswp__button--arrow--next {\r\n  right: 0px;\r\n}\r\n.pswp__button--arrow--next .pswp__icn {\r\n  left: auto;\r\n  right: 14px;\r\n  /* flip horizontally */\r\n  transform: scale(-1, 1);\r\n}\r\n\r\n/*\r\n\r\n  Zoom button\r\n\r\n*/\r\n.pswp__button--zoom {\r\n  display: none;\r\n}\r\n\r\n.pswp--zoom-allowed .pswp__button--zoom {\r\n  display: block;\r\n}\r\n\r\n/* \"+\" => \"-\" */\r\n.pswp--zoomed-in .pswp__zoom-icn-bar-v {\r\n  display: none;\r\n}\r\n\r\n\r\n/*\r\n\r\n  Loading indicator\r\n\r\n*/\r\n.pswp__preloader {\r\n  position: relative;\r\n  overflow: hidden;\r\n  width: 50px;\r\n  height: 60px;\r\n  margin-right: auto;\r\n}\r\n\r\n.pswp__preloader .pswp__icn {\r\n  opacity: 0;\r\n  transition: opacity 0.2s linear;\r\n  animation: pswp-clockwise 600ms linear infinite;\r\n}\r\n\r\n.pswp__preloader--active .pswp__icn {\r\n  opacity: 0.85;\r\n}\r\n\r\n@keyframes pswp-clockwise {\r\n  0% { transform: rotate(0deg); }\r\n  100% { transform: rotate(360deg); }\r\n}\r\n\r\n\r\n/*\r\n\r\n  \"1 of 10\" counter\r\n\r\n*/\r\n.pswp__counter {\r\n  height: 30px;\r\n  margin-top: 15px;\r\n  margin-inline-start: 20px;\r\n  font-size: 14px;\r\n  line-height: 30px;\r\n  color: var(--pswp-icon-color);\r\n  text-shadow: 1px 1px 3px var(--pswp-icon-color-secondary);\r\n  opacity: 0.85;\r\n}\r\n\r\n.pswp--one-slide .pswp__counter {\r\n  display: none;\r\n}\r\n", ""]);
// Exports
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (___CSS_LOADER_EXPORT___);


/***/ }),

/***/ "./node_modules/css-loader/dist/runtime/api.js":
/*!*****************************************************!*\
  !*** ./node_modules/css-loader/dist/runtime/api.js ***!
  \*****************************************************/
/***/ ((module) => {

"use strict";


/*
  MIT License http://www.opensource.org/licenses/mit-license.php
  Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
// eslint-disable-next-line func-names
module.exports = function (cssWithMappingToString) {
  var list = []; // return the list of modules as css string

  list.toString = function toString() {
    return this.map(function (item) {
      var content = cssWithMappingToString(item);

      if (item[2]) {
        return "@media ".concat(item[2], " {").concat(content, "}");
      }

      return content;
    }).join("");
  }; // import a list of modules into the list
  // eslint-disable-next-line func-names


  list.i = function (modules, mediaQuery, dedupe) {
    if (typeof modules === "string") {
      // eslint-disable-next-line no-param-reassign
      modules = [[null, modules, ""]];
    }

    var alreadyImportedModules = {};

    if (dedupe) {
      for (var i = 0; i < this.length; i++) {
        // eslint-disable-next-line prefer-destructuring
        var id = this[i][0];

        if (id != null) {
          alreadyImportedModules[id] = true;
        }
      }
    }

    for (var _i = 0; _i < modules.length; _i++) {
      var item = [].concat(modules[_i]);

      if (dedupe && alreadyImportedModules[item[0]]) {
        // eslint-disable-next-line no-continue
        continue;
      }

      if (mediaQuery) {
        if (!item[2]) {
          item[2] = mediaQuery;
        } else {
          item[2] = "".concat(mediaQuery, " and ").concat(item[2]);
        }
      }

      list.push(item);
    }
  };

  return list;
};

/***/ }),

/***/ "./node_modules/photoswipe/dist/photoswipe-lightbox.esm.js":
/*!*****************************************************************!*\
  !*** ./node_modules/photoswipe/dist/photoswipe-lightbox.esm.js ***!
  \*****************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ PhotoSwipeLightbox)
/* harmony export */ });
/*!
  * PhotoSwipe Lightbox 5.4.4 - https://photoswipe.com
  * (c) 2024 Dmytro Semenov
  */
/** @typedef {import('../photoswipe.js').Point} Point */

/**
 * @template {keyof HTMLElementTagNameMap} T
 * @param {string} className
 * @param {T} tagName
 * @param {Node} [appendToEl]
 * @returns {HTMLElementTagNameMap[T]}
 */
function createElement(className, tagName, appendToEl) {
  const el = document.createElement(tagName);

  if (className) {
    el.className = className;
  }

  if (appendToEl) {
    appendToEl.appendChild(el);
  }

  return el;
}
/**
 * Get transform string
 *
 * @param {number} x
 * @param {number} [y]
 * @param {number} [scale]
 * @returns {string}
 */

function toTransformString(x, y, scale) {
  let propValue = `translate3d(${x}px,${y || 0}px,0)`;

  if (scale !== undefined) {
    propValue += ` scale3d(${scale},${scale},1)`;
  }

  return propValue;
}
/**
 * Apply width and height CSS properties to element
 *
 * @param {HTMLElement} el
 * @param {string | number} w
 * @param {string | number} h
 */

function setWidthHeight(el, w, h) {
  el.style.width = typeof w === 'number' ? `${w}px` : w;
  el.style.height = typeof h === 'number' ? `${h}px` : h;
}
/** @typedef {LOAD_STATE[keyof LOAD_STATE]} LoadState */

/** @type {{ IDLE: 'idle'; LOADING: 'loading'; LOADED: 'loaded'; ERROR: 'error' }} */

const LOAD_STATE = {
  IDLE: 'idle',
  LOADING: 'loading',
  LOADED: 'loaded',
  ERROR: 'error'
};
/**
 * Check if click or keydown event was dispatched
 * with a special key or via mouse wheel.
 *
 * @param {MouseEvent | KeyboardEvent} e
 * @returns {boolean}
 */

function specialKeyUsed(e) {
  return 'button' in e && e.button === 1 || e.ctrlKey || e.metaKey || e.altKey || e.shiftKey;
}
/**
 * Parse `gallery` or `children` options.
 *
 * @param {import('../photoswipe.js').ElementProvider} [option]
 * @param {string} [legacySelector]
 * @param {HTMLElement | Document} [parent]
 * @returns HTMLElement[]
 */

function getElementsFromOption(option, legacySelector, parent = document) {
  /** @type {HTMLElement[]} */
  let elements = [];

  if (option instanceof Element) {
    elements = [option];
  } else if (option instanceof NodeList || Array.isArray(option)) {
    elements = Array.from(option);
  } else {
    const selector = typeof option === 'string' ? option : legacySelector;

    if (selector) {
      elements = Array.from(parent.querySelectorAll(selector));
    }
  }

  return elements;
}
/**
 * Check if variable is PhotoSwipe class
 *
 * @param {any} fn
 * @returns {boolean}
 */

function isPswpClass(fn) {
  return typeof fn === 'function' && fn.prototype && fn.prototype.goTo;
}
/**
 * Check if browser is Safari
 *
 * @returns {boolean}
 */

function isSafari() {
  return !!(navigator.vendor && navigator.vendor.match(/apple/i));
}

/** @typedef {import('../lightbox/lightbox.js').default} PhotoSwipeLightbox */

/** @typedef {import('../photoswipe.js').default} PhotoSwipe */

/** @typedef {import('../photoswipe.js').PhotoSwipeOptions} PhotoSwipeOptions */

/** @typedef {import('../photoswipe.js').DataSource} DataSource */

/** @typedef {import('../ui/ui-element.js').UIElementData} UIElementData */

/** @typedef {import('../slide/content.js').default} ContentDefault */

/** @typedef {import('../slide/slide.js').default} Slide */

/** @typedef {import('../slide/slide.js').SlideData} SlideData */

/** @typedef {import('../slide/zoom-level.js').default} ZoomLevel */

/** @typedef {import('../slide/get-thumb-bounds.js').Bounds} Bounds */

/**
 * Allow adding an arbitrary props to the Content
 * https://photoswipe.com/custom-content/#using-webp-image-format
 * @typedef {ContentDefault & Record<string, any>} Content
 */

/** @typedef {{ x?: number; y?: number }} Point */

/**
 * @typedef {Object} PhotoSwipeEventsMap https://photoswipe.com/events/
 *
 *
 * https://photoswipe.com/adding-ui-elements/
 *
 * @prop {undefined} uiRegister
 * @prop {{ data: UIElementData }} uiElementCreate
 *
 *
 * https://photoswipe.com/events/#initialization-events
 *
 * @prop {undefined} beforeOpen
 * @prop {undefined} firstUpdate
 * @prop {undefined} initialLayout
 * @prop {undefined} change
 * @prop {undefined} afterInit
 * @prop {undefined} bindEvents
 *
 *
 * https://photoswipe.com/events/#opening-or-closing-transition-events
 *
 * @prop {undefined} openingAnimationStart
 * @prop {undefined} openingAnimationEnd
 * @prop {undefined} closingAnimationStart
 * @prop {undefined} closingAnimationEnd
 *
 *
 * https://photoswipe.com/events/#closing-events
 *
 * @prop {undefined} close
 * @prop {undefined} destroy
 *
 *
 * https://photoswipe.com/events/#pointer-and-gesture-events
 *
 * @prop {{ originalEvent: PointerEvent }} pointerDown
 * @prop {{ originalEvent: PointerEvent }} pointerMove
 * @prop {{ originalEvent: PointerEvent }} pointerUp
 * @prop {{ bgOpacity: number }} pinchClose can be default prevented
 * @prop {{ panY: number }} verticalDrag can be default prevented
 *
 *
 * https://photoswipe.com/events/#slide-content-events
 *
 * @prop {{ content: Content }} contentInit
 * @prop {{ content: Content; isLazy: boolean }} contentLoad can be default prevented
 * @prop {{ content: Content; isLazy: boolean }} contentLoadImage can be default prevented
 * @prop {{ content: Content; slide: Slide; isError?: boolean }} loadComplete
 * @prop {{ content: Content; slide: Slide }} loadError
 * @prop {{ content: Content; width: number; height: number }} contentResize can be default prevented
 * @prop {{ content: Content; width: number; height: number; slide: Slide }} imageSizeChange
 * @prop {{ content: Content }} contentLazyLoad can be default prevented
 * @prop {{ content: Content }} contentAppend can be default prevented
 * @prop {{ content: Content }} contentActivate can be default prevented
 * @prop {{ content: Content }} contentDeactivate can be default prevented
 * @prop {{ content: Content }} contentRemove can be default prevented
 * @prop {{ content: Content }} contentDestroy can be default prevented
 *
 *
 * undocumented
 *
 * @prop {{ point: Point; originalEvent: PointerEvent }} imageClickAction can be default prevented
 * @prop {{ point: Point; originalEvent: PointerEvent }} bgClickAction can be default prevented
 * @prop {{ point: Point; originalEvent: PointerEvent }} tapAction can be default prevented
 * @prop {{ point: Point; originalEvent: PointerEvent }} doubleTapAction can be default prevented
 *
 * @prop {{ originalEvent: KeyboardEvent }} keydown can be default prevented
 * @prop {{ x: number; dragging: boolean }} moveMainScroll
 * @prop {{ slide: Slide }} firstZoomPan
 * @prop {{ slide: Slide | undefined, data: SlideData, index: number }} gettingData
 * @prop {undefined} beforeResize
 * @prop {undefined} resize
 * @prop {undefined} viewportSize
 * @prop {undefined} updateScrollOffset
 * @prop {{ slide: Slide }} slideInit
 * @prop {{ slide: Slide }} afterSetContent
 * @prop {{ slide: Slide }} slideLoad
 * @prop {{ slide: Slide }} appendHeavy can be default prevented
 * @prop {{ slide: Slide }} appendHeavyContent
 * @prop {{ slide: Slide }} slideActivate
 * @prop {{ slide: Slide }} slideDeactivate
 * @prop {{ slide: Slide }} slideDestroy
 * @prop {{ destZoomLevel: number, centerPoint: Point | undefined, transitionDuration: number | false | undefined }} beforeZoomTo
 * @prop {{ slide: Slide }} zoomPanUpdate
 * @prop {{ slide: Slide }} initialZoomPan
 * @prop {{ slide: Slide }} calcSlideSize
 * @prop {undefined} resolutionChanged
 * @prop {{ originalEvent: WheelEvent }} wheel can be default prevented
 * @prop {{ content: Content }} contentAppendImage can be default prevented
 * @prop {{ index: number; itemData: SlideData }} lazyLoadSlide can be default prevented
 * @prop {undefined} lazyLoad
 * @prop {{ slide: Slide }} calcBounds
 * @prop {{ zoomLevels: ZoomLevel, slideData: SlideData }} zoomLevelsUpdate
 *
 *
 * legacy
 *
 * @prop {undefined} init
 * @prop {undefined} initialZoomIn
 * @prop {undefined} initialZoomOut
 * @prop {undefined} initialZoomInEnd
 * @prop {undefined} initialZoomOutEnd
 * @prop {{ dataSource: DataSource | undefined, numItems: number }} numItems
 * @prop {{ itemData: SlideData; index: number }} itemData
 * @prop {{ index: number, itemData: SlideData, instance: PhotoSwipe }} thumbBounds
 */

/**
 * @typedef {Object} PhotoSwipeFiltersMap https://photoswipe.com/filters/
 *
 * @prop {(numItems: number, dataSource: DataSource | undefined) => number} numItems
 * Modify the total amount of slides. Example on Data sources page.
 * https://photoswipe.com/filters/#numitems
 *
 * @prop {(itemData: SlideData, index: number) => SlideData} itemData
 * Modify slide item data. Example on Data sources page.
 * https://photoswipe.com/filters/#itemdata
 *
 * @prop {(itemData: SlideData, element: HTMLElement, linkEl: HTMLAnchorElement) => SlideData} domItemData
 * Modify item data when it's parsed from DOM element. Example on Data sources page.
 * https://photoswipe.com/filters/#domitemdata
 *
 * @prop {(clickedIndex: number, e: MouseEvent, instance: PhotoSwipeLightbox) => number} clickedIndex
 * Modify clicked gallery item index.
 * https://photoswipe.com/filters/#clickedindex
 *
 * @prop {(placeholderSrc: string | false, content: Content) => string | false} placeholderSrc
 * Modify placeholder image source.
 * https://photoswipe.com/filters/#placeholdersrc
 *
 * @prop {(isContentLoading: boolean, content: Content) => boolean} isContentLoading
 * Modify if the content is currently loading.
 * https://photoswipe.com/filters/#iscontentloading
 *
 * @prop {(isContentZoomable: boolean, content: Content) => boolean} isContentZoomable
 * Modify if the content can be zoomed.
 * https://photoswipe.com/filters/#iscontentzoomable
 *
 * @prop {(useContentPlaceholder: boolean, content: Content) => boolean} useContentPlaceholder
 * Modify if the placeholder should be used for the content.
 * https://photoswipe.com/filters/#usecontentplaceholder
 *
 * @prop {(isKeepingPlaceholder: boolean, content: Content) => boolean} isKeepingPlaceholder
 * Modify if the placeholder should be kept after the content is loaded.
 * https://photoswipe.com/filters/#iskeepingplaceholder
 *
 *
 * @prop {(contentErrorElement: HTMLElement, content: Content) => HTMLElement} contentErrorElement
 * Modify an element when the content has error state (for example, if image cannot be loaded).
 * https://photoswipe.com/filters/#contenterrorelement
 *
 * @prop {(element: HTMLElement, data: UIElementData) => HTMLElement} uiElement
 * Modify a UI element that's being created.
 * https://photoswipe.com/filters/#uielement
 *
 * @prop {(thumbnail: HTMLElement | null | undefined, itemData: SlideData, index: number) => HTMLElement} thumbEl
 * Modify the thumbnail element from which opening zoom animation starts or ends.
 * https://photoswipe.com/filters/#thumbel
 *
 * @prop {(thumbBounds: Bounds | undefined, itemData: SlideData, index: number) => Bounds} thumbBounds
 * Modify the thumbnail bounds from which opening zoom animation starts or ends.
 * https://photoswipe.com/filters/#thumbbounds
 *
 * @prop {(srcsetSizesWidth: number, content: Content) => number} srcsetSizesWidth
 *
 * @prop {(preventPointerEvent: boolean, event: PointerEvent, pointerType: string) => boolean} preventPointerEvent
 *
 */

/**
 * @template {keyof PhotoSwipeFiltersMap} T
 * @typedef {{ fn: PhotoSwipeFiltersMap[T], priority: number }} Filter
 */

/**
 * @template {keyof PhotoSwipeEventsMap} T
 * @typedef {PhotoSwipeEventsMap[T] extends undefined ? PhotoSwipeEvent<T> : PhotoSwipeEvent<T> & PhotoSwipeEventsMap[T]} AugmentedEvent
 */

/**
 * @template {keyof PhotoSwipeEventsMap} T
 * @typedef {(event: AugmentedEvent<T>) => void} EventCallback
 */

/**
 * Base PhotoSwipe event object
 *
 * @template {keyof PhotoSwipeEventsMap} T
 */
class PhotoSwipeEvent {
  /**
   * @param {T} type
   * @param {PhotoSwipeEventsMap[T]} [details]
   */
  constructor(type, details) {
    this.type = type;
    this.defaultPrevented = false;

    if (details) {
      Object.assign(this, details);
    }
  }

  preventDefault() {
    this.defaultPrevented = true;
  }

}
/**
 * PhotoSwipe base class that can listen and dispatch for events.
 * Shared by PhotoSwipe Core and PhotoSwipe Lightbox, extended by base.js
 */


class Eventable {
  constructor() {
    /**
     * @type {{ [T in keyof PhotoSwipeEventsMap]?: ((event: AugmentedEvent<T>) => void)[] }}
     */
    this._listeners = {};
    /**
     * @type {{ [T in keyof PhotoSwipeFiltersMap]?: Filter<T>[] }}
     */

    this._filters = {};
    /** @type {PhotoSwipe | undefined} */

    this.pswp = undefined;
    /** @type {PhotoSwipeOptions | undefined} */

    this.options = undefined;
  }
  /**
   * @template {keyof PhotoSwipeFiltersMap} T
   * @param {T} name
   * @param {PhotoSwipeFiltersMap[T]} fn
   * @param {number} priority
   */


  addFilter(name, fn, priority = 100) {
    var _this$_filters$name, _this$_filters$name2, _this$pswp;

    if (!this._filters[name]) {
      this._filters[name] = [];
    }

    (_this$_filters$name = this._filters[name]) === null || _this$_filters$name === void 0 || _this$_filters$name.push({
      fn,
      priority
    });
    (_this$_filters$name2 = this._filters[name]) === null || _this$_filters$name2 === void 0 || _this$_filters$name2.sort((f1, f2) => f1.priority - f2.priority);
    (_this$pswp = this.pswp) === null || _this$pswp === void 0 || _this$pswp.addFilter(name, fn, priority);
  }
  /**
   * @template {keyof PhotoSwipeFiltersMap} T
   * @param {T} name
   * @param {PhotoSwipeFiltersMap[T]} fn
   */


  removeFilter(name, fn) {
    if (this._filters[name]) {
      // @ts-expect-error
      this._filters[name] = this._filters[name].filter(filter => filter.fn !== fn);
    }

    if (this.pswp) {
      this.pswp.removeFilter(name, fn);
    }
  }
  /**
   * @template {keyof PhotoSwipeFiltersMap} T
   * @param {T} name
   * @param {Parameters<PhotoSwipeFiltersMap[T]>} args
   * @returns {Parameters<PhotoSwipeFiltersMap[T]>[0]}
   */


  applyFilters(name, ...args) {
    var _this$_filters$name3;

    (_this$_filters$name3 = this._filters[name]) === null || _this$_filters$name3 === void 0 || _this$_filters$name3.forEach(filter => {
      // @ts-expect-error
      args[0] = filter.fn.apply(this, args);
    });
    return args[0];
  }
  /**
   * @template {keyof PhotoSwipeEventsMap} T
   * @param {T} name
   * @param {EventCallback<T>} fn
   */


  on(name, fn) {
    var _this$_listeners$name, _this$pswp2;

    if (!this._listeners[name]) {
      this._listeners[name] = [];
    }

    (_this$_listeners$name = this._listeners[name]) === null || _this$_listeners$name === void 0 || _this$_listeners$name.push(fn); // When binding events to lightbox,
    // also bind events to PhotoSwipe Core,
    // if it's open.

    (_this$pswp2 = this.pswp) === null || _this$pswp2 === void 0 || _this$pswp2.on(name, fn);
  }
  /**
   * @template {keyof PhotoSwipeEventsMap} T
   * @param {T} name
   * @param {EventCallback<T>} fn
   */


  off(name, fn) {
    var _this$pswp3;

    if (this._listeners[name]) {
      // @ts-expect-error
      this._listeners[name] = this._listeners[name].filter(listener => fn !== listener);
    }

    (_this$pswp3 = this.pswp) === null || _this$pswp3 === void 0 || _this$pswp3.off(name, fn);
  }
  /**
   * @template {keyof PhotoSwipeEventsMap} T
   * @param {T} name
   * @param {PhotoSwipeEventsMap[T]} [details]
   * @returns {AugmentedEvent<T>}
   */


  dispatch(name, details) {
    var _this$_listeners$name2;

    if (this.pswp) {
      return this.pswp.dispatch(name, details);
    }

    const event =
    /** @type {AugmentedEvent<T>} */
    new PhotoSwipeEvent(name, details);
    (_this$_listeners$name2 = this._listeners[name]) === null || _this$_listeners$name2 === void 0 || _this$_listeners$name2.forEach(listener => {
      listener.call(this, event);
    });
    return event;
  }

}

class Placeholder {
  /**
   * @param {string | false} imageSrc
   * @param {HTMLElement} container
   */
  constructor(imageSrc, container) {
    // Create placeholder
    // (stretched thumbnail or simple div behind the main image)

    /** @type {HTMLImageElement | HTMLDivElement | null} */
    this.element = createElement('pswp__img pswp__img--placeholder', imageSrc ? 'img' : 'div', container);

    if (imageSrc) {
      const imgEl =
      /** @type {HTMLImageElement} */
      this.element;
      imgEl.decoding = 'async';
      imgEl.alt = '';
      imgEl.src = imageSrc;
      imgEl.setAttribute('role', 'presentation');
    }

    this.element.setAttribute('aria-hidden', 'true');
  }
  /**
   * @param {number} width
   * @param {number} height
   */


  setDisplayedSize(width, height) {
    if (!this.element) {
      return;
    }

    if (this.element.tagName === 'IMG') {
      // Use transform scale() to modify img placeholder size
      // (instead of changing width/height directly).
      // This helps with performance, specifically in iOS15 Safari.
      setWidthHeight(this.element, 250, 'auto');
      this.element.style.transformOrigin = '0 0';
      this.element.style.transform = toTransformString(0, 0, width / 250);
    } else {
      setWidthHeight(this.element, width, height);
    }
  }

  destroy() {
    var _this$element;

    if ((_this$element = this.element) !== null && _this$element !== void 0 && _this$element.parentNode) {
      this.element.remove();
    }

    this.element = null;
  }

}

/** @typedef {import('./slide.js').default} Slide */

/** @typedef {import('./slide.js').SlideData} SlideData */

/** @typedef {import('../core/base.js').default} PhotoSwipeBase */

/** @typedef {import('../util/util.js').LoadState} LoadState */

class Content {
  /**
   * @param {SlideData} itemData Slide data
   * @param {PhotoSwipeBase} instance PhotoSwipe or PhotoSwipeLightbox instance
   * @param {number} index
   */
  constructor(itemData, instance, index) {
    this.instance = instance;
    this.data = itemData;
    this.index = index;
    /** @type {HTMLImageElement | HTMLDivElement | undefined} */

    this.element = undefined;
    /** @type {Placeholder | undefined} */

    this.placeholder = undefined;
    /** @type {Slide | undefined} */

    this.slide = undefined;
    this.displayedImageWidth = 0;
    this.displayedImageHeight = 0;
    this.width = Number(this.data.w) || Number(this.data.width) || 0;
    this.height = Number(this.data.h) || Number(this.data.height) || 0;
    this.isAttached = false;
    this.hasSlide = false;
    this.isDecoding = false;
    /** @type {LoadState} */

    this.state = LOAD_STATE.IDLE;

    if (this.data.type) {
      this.type = this.data.type;
    } else if (this.data.src) {
      this.type = 'image';
    } else {
      this.type = 'html';
    }

    this.instance.dispatch('contentInit', {
      content: this
    });
  }

  removePlaceholder() {
    if (this.placeholder && !this.keepPlaceholder()) {
      // With delay, as image might be loaded, but not rendered
      setTimeout(() => {
        if (this.placeholder) {
          this.placeholder.destroy();
          this.placeholder = undefined;
        }
      }, 1000);
    }
  }
  /**
   * Preload content
   *
   * @param {boolean} isLazy
   * @param {boolean} [reload]
   */


  load(isLazy, reload) {
    if (this.slide && this.usePlaceholder()) {
      if (!this.placeholder) {
        const placeholderSrc = this.instance.applyFilters('placeholderSrc', // use  image-based placeholder only for the first slide,
        // as rendering (even small stretched thumbnail) is an expensive operation
        this.data.msrc && this.slide.isFirstSlide ? this.data.msrc : false, this);
        this.placeholder = new Placeholder(placeholderSrc, this.slide.container);
      } else {
        const placeholderEl = this.placeholder.element; // Add placeholder to DOM if it was already created

        if (placeholderEl && !placeholderEl.parentElement) {
          this.slide.container.prepend(placeholderEl);
        }
      }
    }

    if (this.element && !reload) {
      return;
    }

    if (this.instance.dispatch('contentLoad', {
      content: this,
      isLazy
    }).defaultPrevented) {
      return;
    }

    if (this.isImageContent()) {
      this.element = createElement('pswp__img', 'img'); // Start loading only after width is defined, as sizes might depend on it.
      // Due to Safari feature, we must define sizes before srcset.

      if (this.displayedImageWidth) {
        this.loadImage(isLazy);
      }
    } else {
      this.element = createElement('pswp__content', 'div');
      this.element.innerHTML = this.data.html || '';
    }

    if (reload && this.slide) {
      this.slide.updateContentSize(true);
    }
  }
  /**
   * Preload image
   *
   * @param {boolean} isLazy
   */


  loadImage(isLazy) {
    var _this$data$src, _this$data$alt;

    if (!this.isImageContent() || !this.element || this.instance.dispatch('contentLoadImage', {
      content: this,
      isLazy
    }).defaultPrevented) {
      return;
    }

    const imageElement =
    /** @type HTMLImageElement */
    this.element;
    this.updateSrcsetSizes();

    if (this.data.srcset) {
      imageElement.srcset = this.data.srcset;
    }

    imageElement.src = (_this$data$src = this.data.src) !== null && _this$data$src !== void 0 ? _this$data$src : '';
    imageElement.alt = (_this$data$alt = this.data.alt) !== null && _this$data$alt !== void 0 ? _this$data$alt : '';
    this.state = LOAD_STATE.LOADING;

    if (imageElement.complete) {
      this.onLoaded();
    } else {
      imageElement.onload = () => {
        this.onLoaded();
      };

      imageElement.onerror = () => {
        this.onError();
      };
    }
  }
  /**
   * Assign slide to content
   *
   * @param {Slide} slide
   */


  setSlide(slide) {
    this.slide = slide;
    this.hasSlide = true;
    this.instance = slide.pswp; // todo: do we need to unset slide?
  }
  /**
   * Content load success handler
   */


  onLoaded() {
    this.state = LOAD_STATE.LOADED;

    if (this.slide && this.element) {
      this.instance.dispatch('loadComplete', {
        slide: this.slide,
        content: this
      }); // if content is reloaded

      if (this.slide.isActive && this.slide.heavyAppended && !this.element.parentNode) {
        this.append();
        this.slide.updateContentSize(true);
      }

      if (this.state === LOAD_STATE.LOADED || this.state === LOAD_STATE.ERROR) {
        this.removePlaceholder();
      }
    }
  }
  /**
   * Content load error handler
   */


  onError() {
    this.state = LOAD_STATE.ERROR;

    if (this.slide) {
      this.displayError();
      this.instance.dispatch('loadComplete', {
        slide: this.slide,
        isError: true,
        content: this
      });
      this.instance.dispatch('loadError', {
        slide: this.slide,
        content: this
      });
    }
  }
  /**
   * @returns {Boolean} If the content is currently loading
   */


  isLoading() {
    return this.instance.applyFilters('isContentLoading', this.state === LOAD_STATE.LOADING, this);
  }
  /**
   * @returns {Boolean} If the content is in error state
   */


  isError() {
    return this.state === LOAD_STATE.ERROR;
  }
  /**
   * @returns {boolean} If the content is image
   */


  isImageContent() {
    return this.type === 'image';
  }
  /**
   * Update content size
   *
   * @param {Number} width
   * @param {Number} height
   */


  setDisplayedSize(width, height) {
    if (!this.element) {
      return;
    }

    if (this.placeholder) {
      this.placeholder.setDisplayedSize(width, height);
    }

    if (this.instance.dispatch('contentResize', {
      content: this,
      width,
      height
    }).defaultPrevented) {
      return;
    }

    setWidthHeight(this.element, width, height);

    if (this.isImageContent() && !this.isError()) {
      const isInitialSizeUpdate = !this.displayedImageWidth && width;
      this.displayedImageWidth = width;
      this.displayedImageHeight = height;

      if (isInitialSizeUpdate) {
        this.loadImage(false);
      } else {
        this.updateSrcsetSizes();
      }

      if (this.slide) {
        this.instance.dispatch('imageSizeChange', {
          slide: this.slide,
          width,
          height,
          content: this
        });
      }
    }
  }
  /**
   * @returns {boolean} If the content can be zoomed
   */


  isZoomable() {
    return this.instance.applyFilters('isContentZoomable', this.isImageContent() && this.state !== LOAD_STATE.ERROR, this);
  }
  /**
   * Update image srcset sizes attribute based on width and height
   */


  updateSrcsetSizes() {
    // Handle srcset sizes attribute.
    //
    // Never lower quality, if it was increased previously.
    // Chrome does this automatically, Firefox and Safari do not,
    // so we store largest used size in dataset.
    if (!this.isImageContent() || !this.element || !this.data.srcset) {
      return;
    }

    const image =
    /** @type HTMLImageElement */
    this.element;
    const sizesWidth = this.instance.applyFilters('srcsetSizesWidth', this.displayedImageWidth, this);

    if (!image.dataset.largestUsedSize || sizesWidth > parseInt(image.dataset.largestUsedSize, 10)) {
      image.sizes = sizesWidth + 'px';
      image.dataset.largestUsedSize = String(sizesWidth);
    }
  }
  /**
   * @returns {boolean} If content should use a placeholder (from msrc by default)
   */


  usePlaceholder() {
    return this.instance.applyFilters('useContentPlaceholder', this.isImageContent(), this);
  }
  /**
   * Preload content with lazy-loading param
   */


  lazyLoad() {
    if (this.instance.dispatch('contentLazyLoad', {
      content: this
    }).defaultPrevented) {
      return;
    }

    this.load(true);
  }
  /**
   * @returns {boolean} If placeholder should be kept after content is loaded
   */


  keepPlaceholder() {
    return this.instance.applyFilters('isKeepingPlaceholder', this.isLoading(), this);
  }
  /**
   * Destroy the content
   */


  destroy() {
    this.hasSlide = false;
    this.slide = undefined;

    if (this.instance.dispatch('contentDestroy', {
      content: this
    }).defaultPrevented) {
      return;
    }

    this.remove();

    if (this.placeholder) {
      this.placeholder.destroy();
      this.placeholder = undefined;
    }

    if (this.isImageContent() && this.element) {
      this.element.onload = null;
      this.element.onerror = null;
      this.element = undefined;
    }
  }
  /**
   * Display error message
   */


  displayError() {
    if (this.slide) {
      var _this$instance$option, _this$instance$option2;

      let errorMsgEl = createElement('pswp__error-msg', 'div');
      errorMsgEl.innerText = (_this$instance$option = (_this$instance$option2 = this.instance.options) === null || _this$instance$option2 === void 0 ? void 0 : _this$instance$option2.errorMsg) !== null && _this$instance$option !== void 0 ? _this$instance$option : '';
      errorMsgEl =
      /** @type {HTMLDivElement} */
      this.instance.applyFilters('contentErrorElement', errorMsgEl, this);
      this.element = createElement('pswp__content pswp__error-msg-container', 'div');
      this.element.appendChild(errorMsgEl);
      this.slide.container.innerText = '';
      this.slide.container.appendChild(this.element);
      this.slide.updateContentSize(true);
      this.removePlaceholder();
    }
  }
  /**
   * Append the content
   */


  append() {
    if (this.isAttached || !this.element) {
      return;
    }

    this.isAttached = true;

    if (this.state === LOAD_STATE.ERROR) {
      this.displayError();
      return;
    }

    if (this.instance.dispatch('contentAppend', {
      content: this
    }).defaultPrevented) {
      return;
    }

    const supportsDecode = ('decode' in this.element);

    if (this.isImageContent()) {
      // Use decode() on nearby slides
      //
      // Nearby slide images are in DOM and not hidden via display:none.
      // However, they are placed offscreen (to the left and right side).
      //
      // Some browsers do not composite the image until it's actually visible,
      // using decode() helps.
      //
      // You might ask "why dont you just decode() and then append all images",
      // that's because I want to show image before it's fully loaded,
      // as browser can render parts of image while it is loading.
      // We do not do this in Safari due to partial loading bug.
      if (supportsDecode && this.slide && (!this.slide.isActive || isSafari())) {
        this.isDecoding = true; // purposefully using finally instead of then,
        // as if srcset sizes changes dynamically - it may cause decode error

        /** @type {HTMLImageElement} */

        this.element.decode().catch(() => {}).finally(() => {
          this.isDecoding = false;
          this.appendImage();
        });
      } else {
        this.appendImage();
      }
    } else if (this.slide && !this.element.parentNode) {
      this.slide.container.appendChild(this.element);
    }
  }
  /**
   * Activate the slide,
   * active slide is generally the current one,
   * meaning the user can see it.
   */


  activate() {
    if (this.instance.dispatch('contentActivate', {
      content: this
    }).defaultPrevented || !this.slide) {
      return;
    }

    if (this.isImageContent() && this.isDecoding && !isSafari()) {
      // add image to slide when it becomes active,
      // even if it's not finished decoding
      this.appendImage();
    } else if (this.isError()) {
      this.load(false, true); // try to reload
    }

    if (this.slide.holderElement) {
      this.slide.holderElement.setAttribute('aria-hidden', 'false');
    }
  }
  /**
   * Deactivate the content
   */


  deactivate() {
    this.instance.dispatch('contentDeactivate', {
      content: this
    });

    if (this.slide && this.slide.holderElement) {
      this.slide.holderElement.setAttribute('aria-hidden', 'true');
    }
  }
  /**
   * Remove the content from DOM
   */


  remove() {
    this.isAttached = false;

    if (this.instance.dispatch('contentRemove', {
      content: this
    }).defaultPrevented) {
      return;
    }

    if (this.element && this.element.parentNode) {
      this.element.remove();
    }

    if (this.placeholder && this.placeholder.element) {
      this.placeholder.element.remove();
    }
  }
  /**
   * Append the image content to slide container
   */


  appendImage() {
    if (!this.isAttached) {
      return;
    }

    if (this.instance.dispatch('contentAppendImage', {
      content: this
    }).defaultPrevented) {
      return;
    } // ensure that element exists and is not already appended


    if (this.slide && this.element && !this.element.parentNode) {
      this.slide.container.appendChild(this.element);
    }

    if (this.state === LOAD_STATE.LOADED || this.state === LOAD_STATE.ERROR) {
      this.removePlaceholder();
    }
  }

}

/** @typedef {import('../photoswipe.js').PhotoSwipeOptions} PhotoSwipeOptions */

/** @typedef {import('../core/base.js').default} PhotoSwipeBase */

/** @typedef {import('../photoswipe.js').Point} Point */

/** @typedef {import('../slide/slide.js').SlideData} SlideData */

/**
 * @param {PhotoSwipeOptions} options
 * @param {PhotoSwipeBase} pswp
 * @returns {Point}
 */
function getViewportSize(options, pswp) {
  if (options.getViewportSizeFn) {
    const newViewportSize = options.getViewportSizeFn(options, pswp);

    if (newViewportSize) {
      return newViewportSize;
    }
  }

  return {
    x: document.documentElement.clientWidth,
    // TODO: height on mobile is very incosistent due to toolbar
    // find a way to improve this
    //
    // document.documentElement.clientHeight - doesn't seem to work well
    y: window.innerHeight
  };
}
/**
 * Parses padding option.
 * Supported formats:
 *
 * // Object
 * padding: {
 *  top: 0,
 *  bottom: 0,
 *  left: 0,
 *  right: 0
 * }
 *
 * // A function that returns the object
 * paddingFn: (viewportSize, itemData, index) => {
 *  return {
 *    top: 0,
 *    bottom: 0,
 *    left: 0,
 *    right: 0
 *  };
 * }
 *
 * // Legacy variant
 * paddingLeft: 0,
 * paddingRight: 0,
 * paddingTop: 0,
 * paddingBottom: 0,
 *
 * @param {'left' | 'top' | 'bottom' | 'right'} prop
 * @param {PhotoSwipeOptions} options PhotoSwipe options
 * @param {Point} viewportSize PhotoSwipe viewport size, for example: { x:800, y:600 }
 * @param {SlideData} itemData Data about the slide
 * @param {number} index Slide index
 * @returns {number}
 */

function parsePaddingOption(prop, options, viewportSize, itemData, index) {
  let paddingValue = 0;

  if (options.paddingFn) {
    paddingValue = options.paddingFn(viewportSize, itemData, index)[prop];
  } else if (options.padding) {
    paddingValue = options.padding[prop];
  } else {
    const legacyPropName = 'padding' + prop[0].toUpperCase() + prop.slice(1); // @ts-expect-error

    if (options[legacyPropName]) {
      // @ts-expect-error
      paddingValue = options[legacyPropName];
    }
  }

  return Number(paddingValue) || 0;
}
/**
 * @param {PhotoSwipeOptions} options
 * @param {Point} viewportSize
 * @param {SlideData} itemData
 * @param {number} index
 * @returns {Point}
 */

function getPanAreaSize(options, viewportSize, itemData, index) {
  return {
    x: viewportSize.x - parsePaddingOption('left', options, viewportSize, itemData, index) - parsePaddingOption('right', options, viewportSize, itemData, index),
    y: viewportSize.y - parsePaddingOption('top', options, viewportSize, itemData, index) - parsePaddingOption('bottom', options, viewportSize, itemData, index)
  };
}

const MAX_IMAGE_WIDTH = 4000;
/** @typedef {import('../photoswipe.js').default} PhotoSwipe */

/** @typedef {import('../photoswipe.js').PhotoSwipeOptions} PhotoSwipeOptions */

/** @typedef {import('../photoswipe.js').Point} Point */

/** @typedef {import('../slide/slide.js').SlideData} SlideData */

/** @typedef {'fit' | 'fill' | number | ((zoomLevelObject: ZoomLevel) => number)} ZoomLevelOption */

/**
 * Calculates zoom levels for specific slide.
 * Depends on viewport size and image size.
 */

class ZoomLevel {
  /**
   * @param {PhotoSwipeOptions} options PhotoSwipe options
   * @param {SlideData} itemData Slide data
   * @param {number} index Slide index
   * @param {PhotoSwipe} [pswp] PhotoSwipe instance, can be undefined if not initialized yet
   */
  constructor(options, itemData, index, pswp) {
    this.pswp = pswp;
    this.options = options;
    this.itemData = itemData;
    this.index = index;
    /** @type { Point | null } */

    this.panAreaSize = null;
    /** @type { Point | null } */

    this.elementSize = null;
    this.fit = 1;
    this.fill = 1;
    this.vFill = 1;
    this.initial = 1;
    this.secondary = 1;
    this.max = 1;
    this.min = 1;
  }
  /**
   * Calculate initial, secondary and maximum zoom level for the specified slide.
   *
   * It should be called when either image or viewport size changes.
   *
   * @param {number} maxWidth
   * @param {number} maxHeight
   * @param {Point} panAreaSize
   */


  update(maxWidth, maxHeight, panAreaSize) {
    /** @type {Point} */
    const elementSize = {
      x: maxWidth,
      y: maxHeight
    };
    this.elementSize = elementSize;
    this.panAreaSize = panAreaSize;
    const hRatio = panAreaSize.x / elementSize.x;
    const vRatio = panAreaSize.y / elementSize.y;
    this.fit = Math.min(1, hRatio < vRatio ? hRatio : vRatio);
    this.fill = Math.min(1, hRatio > vRatio ? hRatio : vRatio); // zoom.vFill defines zoom level of the image
    // when it has 100% of viewport vertical space (height)

    this.vFill = Math.min(1, vRatio);
    this.initial = this._getInitial();
    this.secondary = this._getSecondary();
    this.max = Math.max(this.initial, this.secondary, this._getMax());
    this.min = Math.min(this.fit, this.initial, this.secondary);

    if (this.pswp) {
      this.pswp.dispatch('zoomLevelsUpdate', {
        zoomLevels: this,
        slideData: this.itemData
      });
    }
  }
  /**
   * Parses user-defined zoom option.
   *
   * @private
   * @param {'initial' | 'secondary' | 'max'} optionPrefix Zoom level option prefix (initial, secondary, max)
   * @returns { number | undefined }
   */


  _parseZoomLevelOption(optionPrefix) {
    const optionName =
    /** @type {'initialZoomLevel' | 'secondaryZoomLevel' | 'maxZoomLevel'} */
    optionPrefix + 'ZoomLevel';
    const optionValue = this.options[optionName];

    if (!optionValue) {
      return;
    }

    if (typeof optionValue === 'function') {
      return optionValue(this);
    }

    if (optionValue === 'fill') {
      return this.fill;
    }

    if (optionValue === 'fit') {
      return this.fit;
    }

    return Number(optionValue);
  }
  /**
   * Get zoom level to which image will be zoomed after double-tap gesture,
   * or when user clicks on zoom icon,
   * or mouse-click on image itself.
   * If you return 1 image will be zoomed to its original size.
   *
   * @private
   * @return {number}
   */


  _getSecondary() {
    let currZoomLevel = this._parseZoomLevelOption('secondary');

    if (currZoomLevel) {
      return currZoomLevel;
    } // 3x of "fit" state, but not larger than original


    currZoomLevel = Math.min(1, this.fit * 3);

    if (this.elementSize && currZoomLevel * this.elementSize.x > MAX_IMAGE_WIDTH) {
      currZoomLevel = MAX_IMAGE_WIDTH / this.elementSize.x;
    }

    return currZoomLevel;
  }
  /**
   * Get initial image zoom level.
   *
   * @private
   * @return {number}
   */


  _getInitial() {
    return this._parseZoomLevelOption('initial') || this.fit;
  }
  /**
   * Maximum zoom level when user zooms
   * via zoom/pinch gesture,
   * via cmd/ctrl-wheel or via trackpad.
   *
   * @private
   * @return {number}
   */


  _getMax() {
    // max zoom level is x4 from "fit state",
    // used for zoom gesture and ctrl/trackpad zoom
    return this._parseZoomLevelOption('max') || Math.max(1, this.fit * 4);
  }

}

/**
 * Lazy-load an image
 * This function is used both by Lightbox and PhotoSwipe core,
 * thus it can be called before dialog is opened.
 *
 * @param {SlideData} itemData Data about the slide
 * @param {PhotoSwipeBase} instance PhotoSwipe or PhotoSwipeLightbox instance
 * @param {number} index
 * @returns {Content} Image that is being decoded or false.
 */

function lazyLoadData(itemData, instance, index) {
  const content = instance.createContentFromData(itemData, index);
  /** @type {ZoomLevel | undefined} */

  let zoomLevel;
  const {
    options
  } = instance; // We need to know dimensions of the image to preload it,
  // as it might use srcset, and we need to define sizes

  if (options) {
    zoomLevel = new ZoomLevel(options, itemData, -1);
    let viewportSize;

    if (instance.pswp) {
      viewportSize = instance.pswp.viewportSize;
    } else {
      viewportSize = getViewportSize(options, instance);
    }

    const panAreaSize = getPanAreaSize(options, viewportSize, itemData, index);
    zoomLevel.update(content.width, content.height, panAreaSize);
  }

  content.lazyLoad();

  if (zoomLevel) {
    content.setDisplayedSize(Math.ceil(content.width * zoomLevel.initial), Math.ceil(content.height * zoomLevel.initial));
  }

  return content;
}
/**
 * Lazy-loads specific slide.
 * This function is used both by Lightbox and PhotoSwipe core,
 * thus it can be called before dialog is opened.
 *
 * By default, it loads image based on viewport size and initial zoom level.
 *
 * @param {number} index Slide index
 * @param {PhotoSwipeBase} instance PhotoSwipe or PhotoSwipeLightbox eventable instance
 * @returns {Content | undefined}
 */

function lazyLoadSlide(index, instance) {
  const itemData = instance.getItemData(index);

  if (instance.dispatch('lazyLoadSlide', {
    index,
    itemData
  }).defaultPrevented) {
    return;
  }

  return lazyLoadData(itemData, instance, index);
}

/** @typedef {import("../photoswipe.js").default} PhotoSwipe */

/** @typedef {import("../slide/slide.js").SlideData} SlideData */

/**
 * PhotoSwipe base class that can retrieve data about every slide.
 * Shared by PhotoSwipe Core and PhotoSwipe Lightbox
 */

class PhotoSwipeBase extends Eventable {
  /**
   * Get total number of slides
   *
   * @returns {number}
   */
  getNumItems() {
    var _this$options;

    let numItems = 0;
    const dataSource = (_this$options = this.options) === null || _this$options === void 0 ? void 0 : _this$options.dataSource;

    if (dataSource && 'length' in dataSource) {
      // may be an array or just object with length property
      numItems = dataSource.length;
    } else if (dataSource && 'gallery' in dataSource) {
      // query DOM elements
      if (!dataSource.items) {
        dataSource.items = this._getGalleryDOMElements(dataSource.gallery);
      }

      if (dataSource.items) {
        numItems = dataSource.items.length;
      }
    } // legacy event, before filters were introduced


    const event = this.dispatch('numItems', {
      dataSource,
      numItems
    });
    return this.applyFilters('numItems', event.numItems, dataSource);
  }
  /**
   * @param {SlideData} slideData
   * @param {number} index
   * @returns {Content}
   */


  createContentFromData(slideData, index) {
    return new Content(slideData, this, index);
  }
  /**
   * Get item data by index.
   *
   * "item data" should contain normalized information that PhotoSwipe needs to generate a slide.
   * For example, it may contain properties like
   * `src`, `srcset`, `w`, `h`, which will be used to generate a slide with image.
   *
   * @param {number} index
   * @returns {SlideData}
   */


  getItemData(index) {
    var _this$options2;

    const dataSource = (_this$options2 = this.options) === null || _this$options2 === void 0 ? void 0 : _this$options2.dataSource;
    /** @type {SlideData | HTMLElement} */

    let dataSourceItem = {};

    if (Array.isArray(dataSource)) {
      // Datasource is an array of elements
      dataSourceItem = dataSource[index];
    } else if (dataSource && 'gallery' in dataSource) {
      // dataSource has gallery property,
      // thus it was created by Lightbox, based on
      // gallery and children options
      // query DOM elements
      if (!dataSource.items) {
        dataSource.items = this._getGalleryDOMElements(dataSource.gallery);
      }

      dataSourceItem = dataSource.items[index];
    }

    let itemData = dataSourceItem;

    if (itemData instanceof Element) {
      itemData = this._domElementToItemData(itemData);
    } // Dispatching the itemData event,
    // it's a legacy verion before filters were introduced


    const event = this.dispatch('itemData', {
      itemData: itemData || {},
      index
    });
    return this.applyFilters('itemData', event.itemData, index);
  }
  /**
   * Get array of gallery DOM elements,
   * based on childSelector and gallery element.
   *
   * @param {HTMLElement} galleryElement
   * @returns {HTMLElement[]}
   */


  _getGalleryDOMElements(galleryElement) {
    var _this$options3, _this$options4;

    if ((_this$options3 = this.options) !== null && _this$options3 !== void 0 && _this$options3.children || (_this$options4 = this.options) !== null && _this$options4 !== void 0 && _this$options4.childSelector) {
      return getElementsFromOption(this.options.children, this.options.childSelector, galleryElement) || [];
    }

    return [galleryElement];
  }
  /**
   * Converts DOM element to item data object.
   *
   * @param {HTMLElement} element DOM element
   * @returns {SlideData}
   */


  _domElementToItemData(element) {
    /** @type {SlideData} */
    const itemData = {
      element
    };
    const linkEl =
    /** @type {HTMLAnchorElement} */
    element.tagName === 'A' ? element : element.querySelector('a');

    if (linkEl) {
      // src comes from data-pswp-src attribute,
      // if it's empty link href is used
      itemData.src = linkEl.dataset.pswpSrc || linkEl.href;

      if (linkEl.dataset.pswpSrcset) {
        itemData.srcset = linkEl.dataset.pswpSrcset;
      }

      itemData.width = linkEl.dataset.pswpWidth ? parseInt(linkEl.dataset.pswpWidth, 10) : 0;
      itemData.height = linkEl.dataset.pswpHeight ? parseInt(linkEl.dataset.pswpHeight, 10) : 0; // support legacy w & h properties

      itemData.w = itemData.width;
      itemData.h = itemData.height;

      if (linkEl.dataset.pswpType) {
        itemData.type = linkEl.dataset.pswpType;
      }

      const thumbnailEl = element.querySelector('img');

      if (thumbnailEl) {
        var _thumbnailEl$getAttri;

        // msrc is URL to placeholder image that's displayed before large image is loaded
        // by default it's displayed only for the first slide
        itemData.msrc = thumbnailEl.currentSrc || thumbnailEl.src;
        itemData.alt = (_thumbnailEl$getAttri = thumbnailEl.getAttribute('alt')) !== null && _thumbnailEl$getAttri !== void 0 ? _thumbnailEl$getAttri : '';
      }

      if (linkEl.dataset.pswpCropped || linkEl.dataset.cropped) {
        itemData.thumbCropped = true;
      }
    }

    return this.applyFilters('domItemData', itemData, element, linkEl);
  }
  /**
   * Lazy-load by slide data
   *
   * @param {SlideData} itemData Data about the slide
   * @param {number} index
   * @returns {Content} Image that is being decoded or false.
   */


  lazyLoadData(itemData, index) {
    return lazyLoadData(itemData, this, index);
  }

}

/**
 * @template T
 * @typedef {import('../types.js').Type<T>} Type<T>
 */

/** @typedef {import('../photoswipe.js').default} PhotoSwipe */

/** @typedef {import('../photoswipe.js').PhotoSwipeOptions} PhotoSwipeOptions */

/** @typedef {import('../photoswipe.js').DataSource} DataSource */

/** @typedef {import('../photoswipe.js').Point} Point */

/** @typedef {import('../slide/content.js').default} Content */

/** @typedef {import('../core/eventable.js').PhotoSwipeEventsMap} PhotoSwipeEventsMap */

/** @typedef {import('../core/eventable.js').PhotoSwipeFiltersMap} PhotoSwipeFiltersMap */

/**
 * @template {keyof PhotoSwipeEventsMap} T
 * @typedef {import('../core/eventable.js').EventCallback<T>} EventCallback<T>
 */

/**
 * PhotoSwipe Lightbox
 *
 * - If user has unsupported browser it falls back to default browser action (just opens URL)
 * - Binds click event to links that should open PhotoSwipe
 * - parses DOM strcture for PhotoSwipe (retrieves large image URLs and sizes)
 * - Initializes PhotoSwipe
 *
 *
 * Loader options use the same object as PhotoSwipe, and supports such options:
 *
 * gallery - Element | Element[] | NodeList | string selector for the gallery element
 * children - Element | Element[] | NodeList | string selector for the gallery children
 *
 */

class PhotoSwipeLightbox extends PhotoSwipeBase {
  /**
   * @param {PhotoSwipeOptions} [options]
   */
  constructor(options) {
    super();
    /** @type {PhotoSwipeOptions} */

    this.options = options || {};
    this._uid = 0;
    this.shouldOpen = false;
    /**
     * @private
     * @type {Content | undefined}
     */

    this._preloadedContent = undefined;
    this.onThumbnailsClick = this.onThumbnailsClick.bind(this);
  }
  /**
   * Initialize lightbox, should be called only once.
   * It's not included in the main constructor, so you may bind events before it.
   */


  init() {
    // Bind click events to each gallery
    getElementsFromOption(this.options.gallery, this.options.gallerySelector).forEach(galleryElement => {
      galleryElement.addEventListener('click', this.onThumbnailsClick, false);
    });
  }
  /**
   * @param {MouseEvent} e
   */


  onThumbnailsClick(e) {
    // Exit and allow default browser action if:
    if (specialKeyUsed(e) // ... if clicked with a special key (ctrl/cmd...)
    || window.pswp) {
      // ... if PhotoSwipe is already open
      return;
    } // If both clientX and clientY are 0 or not defined,
    // the event is likely triggered by keyboard,
    // so we do not pass the initialPoint
    //
    // Note that some screen readers emulate the mouse position,
    // so it's not the ideal way to detect them.
    //

    /** @type {Point | null} */


    let initialPoint = {
      x: e.clientX,
      y: e.clientY
    };

    if (!initialPoint.x && !initialPoint.y) {
      initialPoint = null;
    }

    let clickedIndex = this.getClickedIndex(e);
    clickedIndex = this.applyFilters('clickedIndex', clickedIndex, e, this);
    /** @type {DataSource} */

    const dataSource = {
      gallery:
      /** @type {HTMLElement} */
      e.currentTarget
    };

    if (clickedIndex >= 0) {
      e.preventDefault();
      this.loadAndOpen(clickedIndex, dataSource, initialPoint);
    }
  }
  /**
   * Get index of gallery item that was clicked.
   *
   * @param {MouseEvent} e click event
   * @returns {number}
   */


  getClickedIndex(e) {
    // legacy option
    if (this.options.getClickedIndexFn) {
      return this.options.getClickedIndexFn.call(this, e);
    }

    const clickedTarget =
    /** @type {HTMLElement} */
    e.target;
    const childElements = getElementsFromOption(this.options.children, this.options.childSelector,
    /** @type {HTMLElement} */
    e.currentTarget);
    const clickedChildIndex = childElements.findIndex(child => child === clickedTarget || child.contains(clickedTarget));

    if (clickedChildIndex !== -1) {
      return clickedChildIndex;
    } else if (this.options.children || this.options.childSelector) {
      // click wasn't on a child element
      return -1;
    } // There is only one item (which is the gallery)


    return 0;
  }
  /**
   * Load and open PhotoSwipe
   *
   * @param {number} index
   * @param {DataSource} [dataSource]
   * @param {Point | null} [initialPoint]
   * @returns {boolean}
   */


  loadAndOpen(index, dataSource, initialPoint) {
    // Check if the gallery is already open
    if (window.pswp || !this.options) {
      return false;
    } // Use the first gallery element if dataSource is not provided


    if (!dataSource && this.options.gallery && this.options.children) {
      const galleryElements = getElementsFromOption(this.options.gallery);

      if (galleryElements[0]) {
        dataSource = {
          gallery: galleryElements[0]
        };
      }
    } // set initial index


    this.options.index = index; // define options for PhotoSwipe constructor

    this.options.initialPointerPos = initialPoint;
    this.shouldOpen = true;
    this.preload(index, dataSource);
    return true;
  }
  /**
   * Load the main module and the slide content by index
   *
   * @param {number} index
   * @param {DataSource} [dataSource]
   */


  preload(index, dataSource) {
    const {
      options
    } = this;

    if (dataSource) {
      options.dataSource = dataSource;
    } // Add the main module

    /** @type {Promise<Type<PhotoSwipe>>[]} */


    const promiseArray = [];
    const pswpModuleType = typeof options.pswpModule;

    if (isPswpClass(options.pswpModule)) {
      promiseArray.push(Promise.resolve(
      /** @type {Type<PhotoSwipe>} */
      options.pswpModule));
    } else if (pswpModuleType === 'string') {
      throw new Error('pswpModule as string is no longer supported');
    } else if (pswpModuleType === 'function') {
      promiseArray.push(
      /** @type {() => Promise<Type<PhotoSwipe>>} */
      options.pswpModule());
    } else {
      throw new Error('pswpModule is not valid');
    } // Add custom-defined promise, if any


    if (typeof options.openPromise === 'function') {
      // allow developers to perform some task before opening
      promiseArray.push(options.openPromise());
    }

    if (options.preloadFirstSlide !== false && index >= 0) {
      this._preloadedContent = lazyLoadSlide(index, this);
    } // Wait till all promises resolve and open PhotoSwipe


    const uid = ++this._uid;
    Promise.all(promiseArray).then(iterableModules => {
      if (this.shouldOpen) {
        const mainModule = iterableModules[0];

        this._openPhotoswipe(mainModule, uid);
      }
    });
  }
  /**
   * @private
   * @param {Type<PhotoSwipe> | { default: Type<PhotoSwipe> }} module
   * @param {number} uid
   */


  _openPhotoswipe(module, uid) {
    // Cancel opening if UID doesn't match the current one
    // (if user clicked on another gallery item before current was loaded).
    //
    // Or if shouldOpen flag is set to false
    // (developer may modify it via public API)
    if (uid !== this._uid && this.shouldOpen) {
      return;
    }

    this.shouldOpen = false; // PhotoSwipe is already open

    if (window.pswp) {
      return;
    }
    /**
     * Pass data to PhotoSwipe and open init
     *
     * @type {PhotoSwipe}
     */


    const pswp = typeof module === 'object' ? new module.default(this.options) // eslint-disable-line
    : new module(this.options); // eslint-disable-line

    this.pswp = pswp;
    window.pswp = pswp; // map listeners from Lightbox to PhotoSwipe Core

    /** @type {(keyof PhotoSwipeEventsMap)[]} */

    Object.keys(this._listeners).forEach(name => {
      var _this$_listeners$name;

      (_this$_listeners$name = this._listeners[name]) === null || _this$_listeners$name === void 0 || _this$_listeners$name.forEach(fn => {
        pswp.on(name,
        /** @type {EventCallback<typeof name>} */
        fn);
      });
    }); // same with filters

    /** @type {(keyof PhotoSwipeFiltersMap)[]} */

    Object.keys(this._filters).forEach(name => {
      var _this$_filters$name;

      (_this$_filters$name = this._filters[name]) === null || _this$_filters$name === void 0 || _this$_filters$name.forEach(filter => {
        pswp.addFilter(name, filter.fn, filter.priority);
      });
    });

    if (this._preloadedContent) {
      pswp.contentLoader.addToCache(this._preloadedContent);
      this._preloadedContent = undefined;
    }

    pswp.on('destroy', () => {
      // clean up public variables
      this.pswp = undefined;
      delete window.pswp;
    });
    pswp.init();
  }
  /**
   * Unbinds all events, closes PhotoSwipe if it's open.
   */


  destroy() {
    var _this$pswp;

    (_this$pswp = this.pswp) === null || _this$pswp === void 0 || _this$pswp.destroy();
    this.shouldOpen = false;
    this._listeners = {};
    getElementsFromOption(this.options.gallery, this.options.gallerySelector).forEach(galleryElement => {
      galleryElement.removeEventListener('click', this.onThumbnailsClick, false);
    });
  }

}


//# sourceMappingURL=photoswipe-lightbox.esm.js.map


/***/ }),

/***/ "./node_modules/photoswipe/dist/photoswipe.css":
/*!*****************************************************!*\
  !*** ./node_modules/photoswipe/dist/photoswipe.css ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! !../../style-loader/dist/runtime/injectStylesIntoStyleTag.js */ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js");
/* harmony import */ var _style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _css_loader_dist_cjs_js_ruleSet_1_rules_10_oneOf_1_use_1_postcss_loader_dist_cjs_js_ruleSet_1_rules_10_oneOf_1_use_2_photoswipe_css__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! !!../../css-loader/dist/cjs.js??ruleSet[1].rules[10].oneOf[1].use[1]!../../postcss-loader/dist/cjs.js??ruleSet[1].rules[10].oneOf[1].use[2]!./photoswipe.css */ "./node_modules/css-loader/dist/cjs.js??ruleSet[1].rules[10].oneOf[1].use[1]!./node_modules/postcss-loader/dist/cjs.js??ruleSet[1].rules[10].oneOf[1].use[2]!./node_modules/photoswipe/dist/photoswipe.css");

            

var options = {};

options.insert = "head";
options.singleton = false;

var update = _style_loader_dist_runtime_injectStylesIntoStyleTag_js__WEBPACK_IMPORTED_MODULE_0___default()(_css_loader_dist_cjs_js_ruleSet_1_rules_10_oneOf_1_use_1_postcss_loader_dist_cjs_js_ruleSet_1_rules_10_oneOf_1_use_2_photoswipe_css__WEBPACK_IMPORTED_MODULE_1__["default"], options);



/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (_css_loader_dist_cjs_js_ruleSet_1_rules_10_oneOf_1_use_1_postcss_loader_dist_cjs_js_ruleSet_1_rules_10_oneOf_1_use_2_photoswipe_css__WEBPACK_IMPORTED_MODULE_1__["default"].locals || {});

/***/ }),

/***/ "./node_modules/photoswipe/dist/photoswipe.esm.js":
/*!********************************************************!*\
  !*** ./node_modules/photoswipe/dist/photoswipe.esm.js ***!
  \********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ PhotoSwipe)
/* harmony export */ });
/*!
  * PhotoSwipe 5.4.4 - https://photoswipe.com
  * (c) 2024 Dmytro Semenov
  */
/** @typedef {import('../photoswipe.js').Point} Point */

/**
 * @template {keyof HTMLElementTagNameMap} T
 * @param {string} className
 * @param {T} tagName
 * @param {Node} [appendToEl]
 * @returns {HTMLElementTagNameMap[T]}
 */
function createElement(className, tagName, appendToEl) {
  const el = document.createElement(tagName);

  if (className) {
    el.className = className;
  }

  if (appendToEl) {
    appendToEl.appendChild(el);
  }

  return el;
}
/**
 * @param {Point} p1
 * @param {Point} p2
 * @returns {Point}
 */

function equalizePoints(p1, p2) {
  p1.x = p2.x;
  p1.y = p2.y;

  if (p2.id !== undefined) {
    p1.id = p2.id;
  }

  return p1;
}
/**
 * @param {Point} p
 */

function roundPoint(p) {
  p.x = Math.round(p.x);
  p.y = Math.round(p.y);
}
/**
 * Returns distance between two points.
 *
 * @param {Point} p1
 * @param {Point} p2
 * @returns {number}
 */

function getDistanceBetween(p1, p2) {
  const x = Math.abs(p1.x - p2.x);
  const y = Math.abs(p1.y - p2.y);
  return Math.sqrt(x * x + y * y);
}
/**
 * Whether X and Y positions of points are equal
 *
 * @param {Point} p1
 * @param {Point} p2
 * @returns {boolean}
 */

function pointsEqual(p1, p2) {
  return p1.x === p2.x && p1.y === p2.y;
}
/**
 * The float result between the min and max values.
 *
 * @param {number} val
 * @param {number} min
 * @param {number} max
 * @returns {number}
 */

function clamp(val, min, max) {
  return Math.min(Math.max(val, min), max);
}
/**
 * Get transform string
 *
 * @param {number} x
 * @param {number} [y]
 * @param {number} [scale]
 * @returns {string}
 */

function toTransformString(x, y, scale) {
  let propValue = `translate3d(${x}px,${y || 0}px,0)`;

  if (scale !== undefined) {
    propValue += ` scale3d(${scale},${scale},1)`;
  }

  return propValue;
}
/**
 * Apply transform:translate(x, y) scale(scale) to element
 *
 * @param {HTMLElement} el
 * @param {number} x
 * @param {number} [y]
 * @param {number} [scale]
 */

function setTransform(el, x, y, scale) {
  el.style.transform = toTransformString(x, y, scale);
}
const defaultCSSEasing = 'cubic-bezier(.4,0,.22,1)';
/**
 * Apply CSS transition to element
 *
 * @param {HTMLElement} el
 * @param {string} [prop] CSS property to animate
 * @param {number} [duration] in ms
 * @param {string} [ease] CSS easing function
 */

function setTransitionStyle(el, prop, duration, ease) {
  // inOut: 'cubic-bezier(.4, 0, .22, 1)', // for "toggle state" transitions
  // out: 'cubic-bezier(0, 0, .22, 1)', // for "show" transitions
  // in: 'cubic-bezier(.4, 0, 1, 1)'// for "hide" transitions
  el.style.transition = prop ? `${prop} ${duration}ms ${ease || defaultCSSEasing}` : 'none';
}
/**
 * Apply width and height CSS properties to element
 *
 * @param {HTMLElement} el
 * @param {string | number} w
 * @param {string | number} h
 */

function setWidthHeight(el, w, h) {
  el.style.width = typeof w === 'number' ? `${w}px` : w;
  el.style.height = typeof h === 'number' ? `${h}px` : h;
}
/**
 * @param {HTMLElement} el
 */

function removeTransitionStyle(el) {
  setTransitionStyle(el);
}
/**
 * @param {HTMLImageElement} img
 * @returns {Promise<HTMLImageElement | void>}
 */

function decodeImage(img) {
  if ('decode' in img) {
    return img.decode().catch(() => {});
  }

  if (img.complete) {
    return Promise.resolve(img);
  }

  return new Promise((resolve, reject) => {
    img.onload = () => resolve(img);

    img.onerror = reject;
  });
}
/** @typedef {LOAD_STATE[keyof LOAD_STATE]} LoadState */

/** @type {{ IDLE: 'idle'; LOADING: 'loading'; LOADED: 'loaded'; ERROR: 'error' }} */

const LOAD_STATE = {
  IDLE: 'idle',
  LOADING: 'loading',
  LOADED: 'loaded',
  ERROR: 'error'
};
/**
 * Check if click or keydown event was dispatched
 * with a special key or via mouse wheel.
 *
 * @param {MouseEvent | KeyboardEvent} e
 * @returns {boolean}
 */

function specialKeyUsed(e) {
  return 'button' in e && e.button === 1 || e.ctrlKey || e.metaKey || e.altKey || e.shiftKey;
}
/**
 * Parse `gallery` or `children` options.
 *
 * @param {import('../photoswipe.js').ElementProvider} [option]
 * @param {string} [legacySelector]
 * @param {HTMLElement | Document} [parent]
 * @returns HTMLElement[]
 */

function getElementsFromOption(option, legacySelector, parent = document) {
  /** @type {HTMLElement[]} */
  let elements = [];

  if (option instanceof Element) {
    elements = [option];
  } else if (option instanceof NodeList || Array.isArray(option)) {
    elements = Array.from(option);
  } else {
    const selector = typeof option === 'string' ? option : legacySelector;

    if (selector) {
      elements = Array.from(parent.querySelectorAll(selector));
    }
  }

  return elements;
}
/**
 * Check if browser is Safari
 *
 * @returns {boolean}
 */

function isSafari() {
  return !!(navigator.vendor && navigator.vendor.match(/apple/i));
}

// Detect passive event listener support
let supportsPassive = false;
/* eslint-disable */

try {
  /* @ts-ignore */
  window.addEventListener('test', null, Object.defineProperty({}, 'passive', {
    get: () => {
      supportsPassive = true;
    }
  }));
} catch (e) {}
/* eslint-enable */

/**
 * @typedef {Object} PoolItem
 * @prop {HTMLElement | Window | Document | undefined | null} target
 * @prop {string} type
 * @prop {EventListenerOrEventListenerObject} listener
 * @prop {boolean} [passive]
 */


class DOMEvents {
  constructor() {
    /**
     * @type {PoolItem[]}
     * @private
     */
    this._pool = [];
  }
  /**
   * Adds event listeners
   *
   * @param {PoolItem['target']} target
   * @param {PoolItem['type']} type Can be multiple, separated by space.
   * @param {PoolItem['listener']} listener
   * @param {PoolItem['passive']} [passive]
   */


  add(target, type, listener, passive) {
    this._toggleListener(target, type, listener, passive);
  }
  /**
   * Removes event listeners
   *
   * @param {PoolItem['target']} target
   * @param {PoolItem['type']} type
   * @param {PoolItem['listener']} listener
   * @param {PoolItem['passive']} [passive]
   */


  remove(target, type, listener, passive) {
    this._toggleListener(target, type, listener, passive, true);
  }
  /**
   * Removes all bound events
   */


  removeAll() {
    this._pool.forEach(poolItem => {
      this._toggleListener(poolItem.target, poolItem.type, poolItem.listener, poolItem.passive, true, true);
    });

    this._pool = [];
  }
  /**
   * Adds or removes event
   *
   * @private
   * @param {PoolItem['target']} target
   * @param {PoolItem['type']} type
   * @param {PoolItem['listener']} listener
   * @param {PoolItem['passive']} [passive]
   * @param {boolean} [unbind] Whether the event should be added or removed
   * @param {boolean} [skipPool] Whether events pool should be skipped
   */


  _toggleListener(target, type, listener, passive, unbind, skipPool) {
    if (!target) {
      return;
    }

    const methodName = unbind ? 'removeEventListener' : 'addEventListener';
    const types = type.split(' ');
    types.forEach(eType => {
      if (eType) {
        // Events pool is used to easily unbind all events when PhotoSwipe is closed,
        // so developer doesn't need to do this manually
        if (!skipPool) {
          if (unbind) {
            // Remove from the events pool
            this._pool = this._pool.filter(poolItem => {
              return poolItem.type !== eType || poolItem.listener !== listener || poolItem.target !== target;
            });
          } else {
            // Add to the events pool
            this._pool.push({
              target,
              type: eType,
              listener,
              passive
            });
          }
        } // most PhotoSwipe events call preventDefault,
        // and we do not need browser to scroll the page


        const eventOptions = supportsPassive ? {
          passive: passive || false
        } : false;
        target[methodName](eType, listener, eventOptions);
      }
    });
  }

}

/** @typedef {import('../photoswipe.js').PhotoSwipeOptions} PhotoSwipeOptions */

/** @typedef {import('../core/base.js').default} PhotoSwipeBase */

/** @typedef {import('../photoswipe.js').Point} Point */

/** @typedef {import('../slide/slide.js').SlideData} SlideData */

/**
 * @param {PhotoSwipeOptions} options
 * @param {PhotoSwipeBase} pswp
 * @returns {Point}
 */
function getViewportSize(options, pswp) {
  if (options.getViewportSizeFn) {
    const newViewportSize = options.getViewportSizeFn(options, pswp);

    if (newViewportSize) {
      return newViewportSize;
    }
  }

  return {
    x: document.documentElement.clientWidth,
    // TODO: height on mobile is very incosistent due to toolbar
    // find a way to improve this
    //
    // document.documentElement.clientHeight - doesn't seem to work well
    y: window.innerHeight
  };
}
/**
 * Parses padding option.
 * Supported formats:
 *
 * // Object
 * padding: {
 *  top: 0,
 *  bottom: 0,
 *  left: 0,
 *  right: 0
 * }
 *
 * // A function that returns the object
 * paddingFn: (viewportSize, itemData, index) => {
 *  return {
 *    top: 0,
 *    bottom: 0,
 *    left: 0,
 *    right: 0
 *  };
 * }
 *
 * // Legacy variant
 * paddingLeft: 0,
 * paddingRight: 0,
 * paddingTop: 0,
 * paddingBottom: 0,
 *
 * @param {'left' | 'top' | 'bottom' | 'right'} prop
 * @param {PhotoSwipeOptions} options PhotoSwipe options
 * @param {Point} viewportSize PhotoSwipe viewport size, for example: { x:800, y:600 }
 * @param {SlideData} itemData Data about the slide
 * @param {number} index Slide index
 * @returns {number}
 */

function parsePaddingOption(prop, options, viewportSize, itemData, index) {
  let paddingValue = 0;

  if (options.paddingFn) {
    paddingValue = options.paddingFn(viewportSize, itemData, index)[prop];
  } else if (options.padding) {
    paddingValue = options.padding[prop];
  } else {
    const legacyPropName = 'padding' + prop[0].toUpperCase() + prop.slice(1); // @ts-expect-error

    if (options[legacyPropName]) {
      // @ts-expect-error
      paddingValue = options[legacyPropName];
    }
  }

  return Number(paddingValue) || 0;
}
/**
 * @param {PhotoSwipeOptions} options
 * @param {Point} viewportSize
 * @param {SlideData} itemData
 * @param {number} index
 * @returns {Point}
 */

function getPanAreaSize(options, viewportSize, itemData, index) {
  return {
    x: viewportSize.x - parsePaddingOption('left', options, viewportSize, itemData, index) - parsePaddingOption('right', options, viewportSize, itemData, index),
    y: viewportSize.y - parsePaddingOption('top', options, viewportSize, itemData, index) - parsePaddingOption('bottom', options, viewportSize, itemData, index)
  };
}

/** @typedef {import('./slide.js').default} Slide */

/** @typedef {Record<Axis, number>} Point */

/** @typedef {'x' | 'y'} Axis */

/**
 * Calculates minimum, maximum and initial (center) bounds of a slide
 */

class PanBounds {
  /**
   * @param {Slide} slide
   */
  constructor(slide) {
    this.slide = slide;
    this.currZoomLevel = 1;
    this.center =
    /** @type {Point} */
    {
      x: 0,
      y: 0
    };
    this.max =
    /** @type {Point} */
    {
      x: 0,
      y: 0
    };
    this.min =
    /** @type {Point} */
    {
      x: 0,
      y: 0
    };
  }
  /**
   * _getItemBounds
   *
   * @param {number} currZoomLevel
   */


  update(currZoomLevel) {
    this.currZoomLevel = currZoomLevel;

    if (!this.slide.width) {
      this.reset();
    } else {
      this._updateAxis('x');

      this._updateAxis('y');

      this.slide.pswp.dispatch('calcBounds', {
        slide: this.slide
      });
    }
  }
  /**
   * _calculateItemBoundsForAxis
   *
   * @param {Axis} axis
   */


  _updateAxis(axis) {
    const {
      pswp
    } = this.slide;
    const elSize = this.slide[axis === 'x' ? 'width' : 'height'] * this.currZoomLevel;
    const paddingProp = axis === 'x' ? 'left' : 'top';
    const padding = parsePaddingOption(paddingProp, pswp.options, pswp.viewportSize, this.slide.data, this.slide.index);
    const panAreaSize = this.slide.panAreaSize[axis]; // Default position of element.
    // By default, it is center of viewport:

    this.center[axis] = Math.round((panAreaSize - elSize) / 2) + padding; // maximum pan position

    this.max[axis] = elSize > panAreaSize ? Math.round(panAreaSize - elSize) + padding : this.center[axis]; // minimum pan position

    this.min[axis] = elSize > panAreaSize ? padding : this.center[axis];
  } // _getZeroBounds


  reset() {
    this.center.x = 0;
    this.center.y = 0;
    this.max.x = 0;
    this.max.y = 0;
    this.min.x = 0;
    this.min.y = 0;
  }
  /**
   * Correct pan position if it's beyond the bounds
   *
   * @param {Axis} axis x or y
   * @param {number} panOffset
   * @returns {number}
   */


  correctPan(axis, panOffset) {
    // checkPanBounds
    return clamp(panOffset, this.max[axis], this.min[axis]);
  }

}

const MAX_IMAGE_WIDTH = 4000;
/** @typedef {import('../photoswipe.js').default} PhotoSwipe */

/** @typedef {import('../photoswipe.js').PhotoSwipeOptions} PhotoSwipeOptions */

/** @typedef {import('../photoswipe.js').Point} Point */

/** @typedef {import('../slide/slide.js').SlideData} SlideData */

/** @typedef {'fit' | 'fill' | number | ((zoomLevelObject: ZoomLevel) => number)} ZoomLevelOption */

/**
 * Calculates zoom levels for specific slide.
 * Depends on viewport size and image size.
 */

class ZoomLevel {
  /**
   * @param {PhotoSwipeOptions} options PhotoSwipe options
   * @param {SlideData} itemData Slide data
   * @param {number} index Slide index
   * @param {PhotoSwipe} [pswp] PhotoSwipe instance, can be undefined if not initialized yet
   */
  constructor(options, itemData, index, pswp) {
    this.pswp = pswp;
    this.options = options;
    this.itemData = itemData;
    this.index = index;
    /** @type { Point | null } */

    this.panAreaSize = null;
    /** @type { Point | null } */

    this.elementSize = null;
    this.fit = 1;
    this.fill = 1;
    this.vFill = 1;
    this.initial = 1;
    this.secondary = 1;
    this.max = 1;
    this.min = 1;
  }
  /**
   * Calculate initial, secondary and maximum zoom level for the specified slide.
   *
   * It should be called when either image or viewport size changes.
   *
   * @param {number} maxWidth
   * @param {number} maxHeight
   * @param {Point} panAreaSize
   */


  update(maxWidth, maxHeight, panAreaSize) {
    /** @type {Point} */
    const elementSize = {
      x: maxWidth,
      y: maxHeight
    };
    this.elementSize = elementSize;
    this.panAreaSize = panAreaSize;
    const hRatio = panAreaSize.x / elementSize.x;
    const vRatio = panAreaSize.y / elementSize.y;
    this.fit = Math.min(1, hRatio < vRatio ? hRatio : vRatio);
    this.fill = Math.min(1, hRatio > vRatio ? hRatio : vRatio); // zoom.vFill defines zoom level of the image
    // when it has 100% of viewport vertical space (height)

    this.vFill = Math.min(1, vRatio);
    this.initial = this._getInitial();
    this.secondary = this._getSecondary();
    this.max = Math.max(this.initial, this.secondary, this._getMax());
    this.min = Math.min(this.fit, this.initial, this.secondary);

    if (this.pswp) {
      this.pswp.dispatch('zoomLevelsUpdate', {
        zoomLevels: this,
        slideData: this.itemData
      });
    }
  }
  /**
   * Parses user-defined zoom option.
   *
   * @private
   * @param {'initial' | 'secondary' | 'max'} optionPrefix Zoom level option prefix (initial, secondary, max)
   * @returns { number | undefined }
   */


  _parseZoomLevelOption(optionPrefix) {
    const optionName =
    /** @type {'initialZoomLevel' | 'secondaryZoomLevel' | 'maxZoomLevel'} */
    optionPrefix + 'ZoomLevel';
    const optionValue = this.options[optionName];

    if (!optionValue) {
      return;
    }

    if (typeof optionValue === 'function') {
      return optionValue(this);
    }

    if (optionValue === 'fill') {
      return this.fill;
    }

    if (optionValue === 'fit') {
      return this.fit;
    }

    return Number(optionValue);
  }
  /**
   * Get zoom level to which image will be zoomed after double-tap gesture,
   * or when user clicks on zoom icon,
   * or mouse-click on image itself.
   * If you return 1 image will be zoomed to its original size.
   *
   * @private
   * @return {number}
   */


  _getSecondary() {
    let currZoomLevel = this._parseZoomLevelOption('secondary');

    if (currZoomLevel) {
      return currZoomLevel;
    } // 3x of "fit" state, but not larger than original


    currZoomLevel = Math.min(1, this.fit * 3);

    if (this.elementSize && currZoomLevel * this.elementSize.x > MAX_IMAGE_WIDTH) {
      currZoomLevel = MAX_IMAGE_WIDTH / this.elementSize.x;
    }

    return currZoomLevel;
  }
  /**
   * Get initial image zoom level.
   *
   * @private
   * @return {number}
   */


  _getInitial() {
    return this._parseZoomLevelOption('initial') || this.fit;
  }
  /**
   * Maximum zoom level when user zooms
   * via zoom/pinch gesture,
   * via cmd/ctrl-wheel or via trackpad.
   *
   * @private
   * @return {number}
   */


  _getMax() {
    // max zoom level is x4 from "fit state",
    // used for zoom gesture and ctrl/trackpad zoom
    return this._parseZoomLevelOption('max') || Math.max(1, this.fit * 4);
  }

}

/** @typedef {import('../photoswipe.js').default} PhotoSwipe */
/**
 * Renders and allows to control a single slide
 */

class Slide {
  /**
   * @param {SlideData} data
   * @param {number} index
   * @param {PhotoSwipe} pswp
   */
  constructor(data, index, pswp) {
    this.data = data;
    this.index = index;
    this.pswp = pswp;
    this.isActive = index === pswp.currIndex;
    this.currentResolution = 0;
    /** @type {Point} */

    this.panAreaSize = {
      x: 0,
      y: 0
    };
    /** @type {Point} */

    this.pan = {
      x: 0,
      y: 0
    };
    this.isFirstSlide = this.isActive && !pswp.opener.isOpen;
    this.zoomLevels = new ZoomLevel(pswp.options, data, index, pswp);
    this.pswp.dispatch('gettingData', {
      slide: this,
      data: this.data,
      index
    });
    this.content = this.pswp.contentLoader.getContentBySlide(this);
    this.container = createElement('pswp__zoom-wrap', 'div');
    /** @type {HTMLElement | null} */

    this.holderElement = null;
    this.currZoomLevel = 1;
    /** @type {number} */

    this.width = this.content.width;
    /** @type {number} */

    this.height = this.content.height;
    this.heavyAppended = false;
    this.bounds = new PanBounds(this);
    this.prevDisplayedWidth = -1;
    this.prevDisplayedHeight = -1;
    this.pswp.dispatch('slideInit', {
      slide: this
    });
  }
  /**
   * If this slide is active/current/visible
   *
   * @param {boolean} isActive
   */


  setIsActive(isActive) {
    if (isActive && !this.isActive) {
      // slide just became active
      this.activate();
    } else if (!isActive && this.isActive) {
      // slide just became non-active
      this.deactivate();
    }
  }
  /**
   * Appends slide content to DOM
   *
   * @param {HTMLElement} holderElement
   */


  append(holderElement) {
    this.holderElement = holderElement;
    this.container.style.transformOrigin = '0 0'; // Slide appended to DOM

    if (!this.data) {
      return;
    }

    this.calculateSize();
    this.load();
    this.updateContentSize();
    this.appendHeavy();
    this.holderElement.appendChild(this.container);
    this.zoomAndPanToInitial();
    this.pswp.dispatch('firstZoomPan', {
      slide: this
    });
    this.applyCurrentZoomPan();
    this.pswp.dispatch('afterSetContent', {
      slide: this
    });

    if (this.isActive) {
      this.activate();
    }
  }

  load() {
    this.content.load(false);
    this.pswp.dispatch('slideLoad', {
      slide: this
    });
  }
  /**
   * Append "heavy" DOM elements
   *
   * This may depend on a type of slide,
   * but generally these are large images.
   */


  appendHeavy() {
    const {
      pswp
    } = this;
    const appendHeavyNearby = true; // todo
    // Avoid appending heavy elements during animations

    if (this.heavyAppended || !pswp.opener.isOpen || pswp.mainScroll.isShifted() || !this.isActive && !appendHeavyNearby) {
      return;
    }

    if (this.pswp.dispatch('appendHeavy', {
      slide: this
    }).defaultPrevented) {
      return;
    }

    this.heavyAppended = true;
    this.content.append();
    this.pswp.dispatch('appendHeavyContent', {
      slide: this
    });
  }
  /**
   * Triggered when this slide is active (selected).
   *
   * If it's part of opening/closing transition -
   * activate() will trigger after the transition is ended.
   */


  activate() {
    this.isActive = true;
    this.appendHeavy();
    this.content.activate();
    this.pswp.dispatch('slideActivate', {
      slide: this
    });
  }
  /**
   * Triggered when this slide becomes inactive.
   *
   * Slide can become inactive only after it was active.
   */


  deactivate() {
    this.isActive = false;
    this.content.deactivate();

    if (this.currZoomLevel !== this.zoomLevels.initial) {
      // allow filtering
      this.calculateSize();
    } // reset zoom level


    this.currentResolution = 0;
    this.zoomAndPanToInitial();
    this.applyCurrentZoomPan();
    this.updateContentSize();
    this.pswp.dispatch('slideDeactivate', {
      slide: this
    });
  }
  /**
   * The slide should destroy itself, it will never be used again.
   * (unbind all events and destroy internal components)
   */


  destroy() {
    this.content.hasSlide = false;
    this.content.remove();
    this.container.remove();
    this.pswp.dispatch('slideDestroy', {
      slide: this
    });
  }

  resize() {
    if (this.currZoomLevel === this.zoomLevels.initial || !this.isActive) {
      // Keep initial zoom level if it was before the resize,
      // as well as when this slide is not active
      // Reset position and scale to original state
      this.calculateSize();
      this.currentResolution = 0;
      this.zoomAndPanToInitial();
      this.applyCurrentZoomPan();
      this.updateContentSize();
    } else {
      // readjust pan position if it's beyond the bounds
      this.calculateSize();
      this.bounds.update(this.currZoomLevel);
      this.panTo(this.pan.x, this.pan.y);
    }
  }
  /**
   * Apply size to current slide content,
   * based on the current resolution and scale.
   *
   * @param {boolean} [force] if size should be updated even if dimensions weren't changed
   */


  updateContentSize(force) {
    // Use initial zoom level
    // if resolution is not defined (user didn't zoom yet)
    const scaleMultiplier = this.currentResolution || this.zoomLevels.initial;

    if (!scaleMultiplier) {
      return;
    }

    const width = Math.round(this.width * scaleMultiplier) || this.pswp.viewportSize.x;
    const height = Math.round(this.height * scaleMultiplier) || this.pswp.viewportSize.y;

    if (!this.sizeChanged(width, height) && !force) {
      return;
    }

    this.content.setDisplayedSize(width, height);
  }
  /**
   * @param {number} width
   * @param {number} height
   */


  sizeChanged(width, height) {
    if (width !== this.prevDisplayedWidth || height !== this.prevDisplayedHeight) {
      this.prevDisplayedWidth = width;
      this.prevDisplayedHeight = height;
      return true;
    }

    return false;
  }
  /** @returns {HTMLImageElement | HTMLDivElement | null | undefined} */


  getPlaceholderElement() {
    var _this$content$placeho;

    return (_this$content$placeho = this.content.placeholder) === null || _this$content$placeho === void 0 ? void 0 : _this$content$placeho.element;
  }
  /**
   * Zoom current slide image to...
   *
   * @param {number} destZoomLevel Destination zoom level.
   * @param {Point} [centerPoint]
   * Transform origin center point, or false if viewport center should be used.
   * @param {number | false} [transitionDuration] Transition duration, may be set to 0.
   * @param {boolean} [ignoreBounds] Minimum and maximum zoom levels will be ignored.
   */


  zoomTo(destZoomLevel, centerPoint, transitionDuration, ignoreBounds) {
    const {
      pswp
    } = this;

    if (!this.isZoomable() || pswp.mainScroll.isShifted()) {
      return;
    }

    pswp.dispatch('beforeZoomTo', {
      destZoomLevel,
      centerPoint,
      transitionDuration
    }); // stop all pan and zoom transitions

    pswp.animations.stopAllPan(); // if (!centerPoint) {
    //   centerPoint = pswp.getViewportCenterPoint();
    // }

    const prevZoomLevel = this.currZoomLevel;

    if (!ignoreBounds) {
      destZoomLevel = clamp(destZoomLevel, this.zoomLevels.min, this.zoomLevels.max);
    } // if (transitionDuration === undefined) {
    //   transitionDuration = this.pswp.options.zoomAnimationDuration;
    // }


    this.setZoomLevel(destZoomLevel);
    this.pan.x = this.calculateZoomToPanOffset('x', centerPoint, prevZoomLevel);
    this.pan.y = this.calculateZoomToPanOffset('y', centerPoint, prevZoomLevel);
    roundPoint(this.pan);

    const finishTransition = () => {
      this._setResolution(destZoomLevel);

      this.applyCurrentZoomPan();
    };

    if (!transitionDuration) {
      finishTransition();
    } else {
      pswp.animations.startTransition({
        isPan: true,
        name: 'zoomTo',
        target: this.container,
        transform: this.getCurrentTransform(),
        onComplete: finishTransition,
        duration: transitionDuration,
        easing: pswp.options.easing
      });
    }
  }
  /**
   * @param {Point} [centerPoint]
   */


  toggleZoom(centerPoint) {
    this.zoomTo(this.currZoomLevel === this.zoomLevels.initial ? this.zoomLevels.secondary : this.zoomLevels.initial, centerPoint, this.pswp.options.zoomAnimationDuration);
  }
  /**
   * Updates zoom level property and recalculates new pan bounds,
   * unlike zoomTo it does not apply transform (use applyCurrentZoomPan)
   *
   * @param {number} currZoomLevel
   */


  setZoomLevel(currZoomLevel) {
    this.currZoomLevel = currZoomLevel;
    this.bounds.update(this.currZoomLevel);
  }
  /**
   * Get pan position after zoom at a given `point`.
   *
   * Always call setZoomLevel(newZoomLevel) beforehand to recalculate
   * pan bounds according to the new zoom level.
   *
   * @param {'x' | 'y'} axis
   * @param {Point} [point]
   * point based on which zoom is performed, usually refers to the current mouse position,
   * if false - viewport center will be used.
   * @param {number} [prevZoomLevel] Zoom level before new zoom was applied.
   * @returns {number}
   */


  calculateZoomToPanOffset(axis, point, prevZoomLevel) {
    const totalPanDistance = this.bounds.max[axis] - this.bounds.min[axis];

    if (totalPanDistance === 0) {
      return this.bounds.center[axis];
    }

    if (!point) {
      point = this.pswp.getViewportCenterPoint();
    }

    if (!prevZoomLevel) {
      prevZoomLevel = this.zoomLevels.initial;
    }

    const zoomFactor = this.currZoomLevel / prevZoomLevel;
    return this.bounds.correctPan(axis, (this.pan[axis] - point[axis]) * zoomFactor + point[axis]);
  }
  /**
   * Apply pan and keep it within bounds.
   *
   * @param {number} panX
   * @param {number} panY
   */


  panTo(panX, panY) {
    this.pan.x = this.bounds.correctPan('x', panX);
    this.pan.y = this.bounds.correctPan('y', panY);
    this.applyCurrentZoomPan();
  }
  /**
   * If the slide in the current state can be panned by the user
   * @returns {boolean}
   */


  isPannable() {
    return Boolean(this.width) && this.currZoomLevel > this.zoomLevels.fit;
  }
  /**
   * If the slide can be zoomed
   * @returns {boolean}
   */


  isZoomable() {
    return Boolean(this.width) && this.content.isZoomable();
  }
  /**
   * Apply transform and scale based on
   * the current pan position (this.pan) and zoom level (this.currZoomLevel)
   */


  applyCurrentZoomPan() {
    this._applyZoomTransform(this.pan.x, this.pan.y, this.currZoomLevel);

    if (this === this.pswp.currSlide) {
      this.pswp.dispatch('zoomPanUpdate', {
        slide: this
      });
    }
  }

  zoomAndPanToInitial() {
    this.currZoomLevel = this.zoomLevels.initial; // pan according to the zoom level

    this.bounds.update(this.currZoomLevel);
    equalizePoints(this.pan, this.bounds.center);
    this.pswp.dispatch('initialZoomPan', {
      slide: this
    });
  }
  /**
   * Set translate and scale based on current resolution
   *
   * @param {number} x
   * @param {number} y
   * @param {number} zoom
   * @private
   */


  _applyZoomTransform(x, y, zoom) {
    zoom /= this.currentResolution || this.zoomLevels.initial;
    setTransform(this.container, x, y, zoom);
  }

  calculateSize() {
    const {
      pswp
    } = this;
    equalizePoints(this.panAreaSize, getPanAreaSize(pswp.options, pswp.viewportSize, this.data, this.index));
    this.zoomLevels.update(this.width, this.height, this.panAreaSize);
    pswp.dispatch('calcSlideSize', {
      slide: this
    });
  }
  /** @returns {string} */


  getCurrentTransform() {
    const scale = this.currZoomLevel / (this.currentResolution || this.zoomLevels.initial);
    return toTransformString(this.pan.x, this.pan.y, scale);
  }
  /**
   * Set resolution and re-render the image.
   *
   * For example, if the real image size is 2000x1500,
   * and resolution is 0.5 - it will be rendered as 1000x750.
   *
   * Image with zoom level 2 and resolution 0.5 is
   * the same as image with zoom level 1 and resolution 1.
   *
   * Used to optimize animations and make
   * sure that browser renders image in the highest quality.
   * Also used by responsive images to load the correct one.
   *
   * @param {number} newResolution
   */


  _setResolution(newResolution) {
    if (newResolution === this.currentResolution) {
      return;
    }

    this.currentResolution = newResolution;
    this.updateContentSize();
    this.pswp.dispatch('resolutionChanged');
  }

}

/** @typedef {import('../photoswipe.js').Point} Point */

/** @typedef {import('./gestures.js').default} Gestures */

const PAN_END_FRICTION = 0.35;
const VERTICAL_DRAG_FRICTION = 0.6; // 1 corresponds to the third of viewport height

const MIN_RATIO_TO_CLOSE = 0.4; // Minimum speed required to navigate
// to next or previous slide

const MIN_NEXT_SLIDE_SPEED = 0.5;
/**
 * @param {number} initialVelocity
 * @param {number} decelerationRate
 * @returns {number}
 */

function project(initialVelocity, decelerationRate) {
  return initialVelocity * decelerationRate / (1 - decelerationRate);
}
/**
 * Handles single pointer dragging
 */


class DragHandler {
  /**
   * @param {Gestures} gestures
   */
  constructor(gestures) {
    this.gestures = gestures;
    this.pswp = gestures.pswp;
    /** @type {Point} */

    this.startPan = {
      x: 0,
      y: 0
    };
  }

  start() {
    if (this.pswp.currSlide) {
      equalizePoints(this.startPan, this.pswp.currSlide.pan);
    }

    this.pswp.animations.stopAll();
  }

  change() {
    const {
      p1,
      prevP1,
      dragAxis
    } = this.gestures;
    const {
      currSlide
    } = this.pswp;

    if (dragAxis === 'y' && this.pswp.options.closeOnVerticalDrag && currSlide && currSlide.currZoomLevel <= currSlide.zoomLevels.fit && !this.gestures.isMultitouch) {
      // Handle vertical drag to close
      const panY = currSlide.pan.y + (p1.y - prevP1.y);

      if (!this.pswp.dispatch('verticalDrag', {
        panY
      }).defaultPrevented) {
        this._setPanWithFriction('y', panY, VERTICAL_DRAG_FRICTION);

        const bgOpacity = 1 - Math.abs(this._getVerticalDragRatio(currSlide.pan.y));
        this.pswp.applyBgOpacity(bgOpacity);
        currSlide.applyCurrentZoomPan();
      }
    } else {
      const mainScrollChanged = this._panOrMoveMainScroll('x');

      if (!mainScrollChanged) {
        this._panOrMoveMainScroll('y');

        if (currSlide) {
          roundPoint(currSlide.pan);
          currSlide.applyCurrentZoomPan();
        }
      }
    }
  }

  end() {
    const {
      velocity
    } = this.gestures;
    const {
      mainScroll,
      currSlide
    } = this.pswp;
    let indexDiff = 0;
    this.pswp.animations.stopAll(); // Handle main scroll if it's shifted

    if (mainScroll.isShifted()) {
      // Position of the main scroll relative to the viewport
      const mainScrollShiftDiff = mainScroll.x - mainScroll.getCurrSlideX(); // Ratio between 0 and 1:
      // 0 - slide is not visible at all,
      // 0.5 - half of the slide is visible
      // 1 - slide is fully visible

      const currentSlideVisibilityRatio = mainScrollShiftDiff / this.pswp.viewportSize.x; // Go next slide.
      //
      // - if velocity and its direction is matched,
      //   and we see at least tiny part of the next slide
      //
      // - or if we see less than 50% of the current slide
      //   and velocity is close to 0
      //

      if (velocity.x < -MIN_NEXT_SLIDE_SPEED && currentSlideVisibilityRatio < 0 || velocity.x < 0.1 && currentSlideVisibilityRatio < -0.5) {
        // Go to next slide
        indexDiff = 1;
        velocity.x = Math.min(velocity.x, 0);
      } else if (velocity.x > MIN_NEXT_SLIDE_SPEED && currentSlideVisibilityRatio > 0 || velocity.x > -0.1 && currentSlideVisibilityRatio > 0.5) {
        // Go to prev slide
        indexDiff = -1;
        velocity.x = Math.max(velocity.x, 0);
      }

      mainScroll.moveIndexBy(indexDiff, true, velocity.x);
    } // Restore zoom level


    if (currSlide && currSlide.currZoomLevel > currSlide.zoomLevels.max || this.gestures.isMultitouch) {
      this.gestures.zoomLevels.correctZoomPan(true);
    } else {
      // we run two animations instead of one,
      // as each axis has own pan boundaries and thus different spring function
      // (correctZoomPan does not have this functionality,
      //  it animates all properties with single timing function)
      this._finishPanGestureForAxis('x');

      this._finishPanGestureForAxis('y');
    }
  }
  /**
   * @private
   * @param {'x' | 'y'} axis
   */


  _finishPanGestureForAxis(axis) {
    const {
      velocity
    } = this.gestures;
    const {
      currSlide
    } = this.pswp;

    if (!currSlide) {
      return;
    }

    const {
      pan,
      bounds
    } = currSlide;
    const panPos = pan[axis];
    const restoreBgOpacity = this.pswp.bgOpacity < 1 && axis === 'y'; // 0.995 means - scroll view loses 0.5% of its velocity per millisecond
    // Increasing this number will reduce travel distance

    const decelerationRate = 0.995; // 0.99
    // Pan position if there is no bounds

    const projectedPosition = panPos + project(velocity[axis], decelerationRate);

    if (restoreBgOpacity) {
      const vDragRatio = this._getVerticalDragRatio(panPos);

      const projectedVDragRatio = this._getVerticalDragRatio(projectedPosition); // If we are above and moving upwards,
      // or if we are below and moving downwards


      if (vDragRatio < 0 && projectedVDragRatio < -MIN_RATIO_TO_CLOSE || vDragRatio > 0 && projectedVDragRatio > MIN_RATIO_TO_CLOSE) {
        this.pswp.close();
        return;
      }
    } // Pan position with corrected bounds


    const correctedPanPosition = bounds.correctPan(axis, projectedPosition); // Exit if pan position should not be changed
    // or if speed it too low

    if (panPos === correctedPanPosition) {
      return;
    } // Overshoot if the final position is out of pan bounds


    const dampingRatio = correctedPanPosition === projectedPosition ? 1 : 0.82;
    const initialBgOpacity = this.pswp.bgOpacity;
    const totalPanDist = correctedPanPosition - panPos;
    this.pswp.animations.startSpring({
      name: 'panGesture' + axis,
      isPan: true,
      start: panPos,
      end: correctedPanPosition,
      velocity: velocity[axis],
      dampingRatio,
      onUpdate: pos => {
        // Animate opacity of background relative to Y pan position of an image
        if (restoreBgOpacity && this.pswp.bgOpacity < 1) {
          // 0 - start of animation, 1 - end of animation
          const animationProgressRatio = 1 - (correctedPanPosition - pos) / totalPanDist; // We clamp opacity to keep it between 0 and 1.
          // As progress ratio can be larger than 1 due to overshoot,
          // and we do not want to bounce opacity.

          this.pswp.applyBgOpacity(clamp(initialBgOpacity + (1 - initialBgOpacity) * animationProgressRatio, 0, 1));
        }

        pan[axis] = Math.floor(pos);
        currSlide.applyCurrentZoomPan();
      }
    });
  }
  /**
   * Update position of the main scroll,
   * or/and update pan position of the current slide.
   *
   * Should return true if it changes (or can change) main scroll.
   *
   * @private
   * @param {'x' | 'y'} axis
   * @returns {boolean}
   */


  _panOrMoveMainScroll(axis) {
    const {
      p1,
      dragAxis,
      prevP1,
      isMultitouch
    } = this.gestures;
    const {
      currSlide,
      mainScroll
    } = this.pswp;
    const delta = p1[axis] - prevP1[axis];
    const newMainScrollX = mainScroll.x + delta;

    if (!delta || !currSlide) {
      return false;
    } // Always move main scroll if image can not be panned


    if (axis === 'x' && !currSlide.isPannable() && !isMultitouch) {
      mainScroll.moveTo(newMainScrollX, true);
      return true; // changed main scroll
    }

    const {
      bounds
    } = currSlide;
    const newPan = currSlide.pan[axis] + delta;

    if (this.pswp.options.allowPanToNext && dragAxis === 'x' && axis === 'x' && !isMultitouch) {
      const currSlideMainScrollX = mainScroll.getCurrSlideX(); // Position of the main scroll relative to the viewport

      const mainScrollShiftDiff = mainScroll.x - currSlideMainScrollX;
      const isLeftToRight = delta > 0;
      const isRightToLeft = !isLeftToRight;

      if (newPan > bounds.min[axis] && isLeftToRight) {
        // Panning from left to right, beyond the left edge
        // Wether the image was at minimum pan position (or less)
        // when this drag gesture started.
        // Minimum pan position refers to the left edge of the image.
        const wasAtMinPanPosition = bounds.min[axis] <= this.startPan[axis];

        if (wasAtMinPanPosition) {
          mainScroll.moveTo(newMainScrollX, true);
          return true;
        } else {
          this._setPanWithFriction(axis, newPan); //currSlide.pan[axis] = newPan;

        }
      } else if (newPan < bounds.max[axis] && isRightToLeft) {
        // Paning from right to left, beyond the right edge
        // Maximum pan position refers to the right edge of the image.
        const wasAtMaxPanPosition = this.startPan[axis] <= bounds.max[axis];

        if (wasAtMaxPanPosition) {
          mainScroll.moveTo(newMainScrollX, true);
          return true;
        } else {
          this._setPanWithFriction(axis, newPan); //currSlide.pan[axis] = newPan;

        }
      } else {
        // If main scroll is shifted
        if (mainScrollShiftDiff !== 0) {
          // If main scroll is shifted right
          if (mainScrollShiftDiff > 0
          /*&& isRightToLeft*/
          ) {
            mainScroll.moveTo(Math.max(newMainScrollX, currSlideMainScrollX), true);
            return true;
          } else if (mainScrollShiftDiff < 0
          /*&& isLeftToRight*/
          ) {
            // Main scroll is shifted left (Position is less than 0 comparing to the viewport 0)
            mainScroll.moveTo(Math.min(newMainScrollX, currSlideMainScrollX), true);
            return true;
          }
        } else {
          // We are within pan bounds, so just pan
          this._setPanWithFriction(axis, newPan);
        }
      }
    } else {
      if (axis === 'y') {
        // Do not pan vertically if main scroll is shifted o
        if (!mainScroll.isShifted() && bounds.min.y !== bounds.max.y) {
          this._setPanWithFriction(axis, newPan);
        }
      } else {
        this._setPanWithFriction(axis, newPan);
      }
    }

    return false;
  } // If we move above - the ratio is negative
  // If we move below the ratio is positive

  /**
   * Relation between pan Y position and third of viewport height.
   *
   * When we are at initial position (center bounds) - the ratio is 0,
   * if position is shifted upwards - the ratio is negative,
   * if position is shifted downwards - the ratio is positive.
   *
   * @private
   * @param {number} panY The current pan Y position.
   * @returns {number}
   */


  _getVerticalDragRatio(panY) {
    var _this$pswp$currSlide$, _this$pswp$currSlide;

    return (panY - ((_this$pswp$currSlide$ = (_this$pswp$currSlide = this.pswp.currSlide) === null || _this$pswp$currSlide === void 0 ? void 0 : _this$pswp$currSlide.bounds.center.y) !== null && _this$pswp$currSlide$ !== void 0 ? _this$pswp$currSlide$ : 0)) / (this.pswp.viewportSize.y / 3);
  }
  /**
   * Set pan position of the current slide.
   * Apply friction if the position is beyond the pan bounds,
   * or if custom friction is defined.
   *
   * @private
   * @param {'x' | 'y'} axis
   * @param {number} potentialPan
   * @param {number} [customFriction] (0.1 - 1)
   */


  _setPanWithFriction(axis, potentialPan, customFriction) {
    const {
      currSlide
    } = this.pswp;

    if (!currSlide) {
      return;
    }

    const {
      pan,
      bounds
    } = currSlide;
    const correctedPan = bounds.correctPan(axis, potentialPan); // If we are out of pan bounds

    if (correctedPan !== potentialPan || customFriction) {
      const delta = Math.round(potentialPan - pan[axis]);
      pan[axis] += delta * (customFriction || PAN_END_FRICTION);
    } else {
      pan[axis] = potentialPan;
    }
  }

}

/** @typedef {import('../photoswipe.js').Point} Point */

/** @typedef {import('./gestures.js').default} Gestures */

const UPPER_ZOOM_FRICTION = 0.05;
const LOWER_ZOOM_FRICTION = 0.15;
/**
 * Get center point between two points
 *
 * @param {Point} p
 * @param {Point} p1
 * @param {Point} p2
 * @returns {Point}
 */

function getZoomPointsCenter(p, p1, p2) {
  p.x = (p1.x + p2.x) / 2;
  p.y = (p1.y + p2.y) / 2;
  return p;
}

class ZoomHandler {
  /**
   * @param {Gestures} gestures
   */
  constructor(gestures) {
    this.gestures = gestures;
    /**
     * @private
     * @type {Point}
     */

    this._startPan = {
      x: 0,
      y: 0
    };
    /**
     * @private
     * @type {Point}
     */

    this._startZoomPoint = {
      x: 0,
      y: 0
    };
    /**
     * @private
     * @type {Point}
     */

    this._zoomPoint = {
      x: 0,
      y: 0
    };
    /** @private */

    this._wasOverFitZoomLevel = false;
    /** @private */

    this._startZoomLevel = 1;
  }

  start() {
    const {
      currSlide
    } = this.gestures.pswp;

    if (currSlide) {
      this._startZoomLevel = currSlide.currZoomLevel;
      equalizePoints(this._startPan, currSlide.pan);
    }

    this.gestures.pswp.animations.stopAllPan();
    this._wasOverFitZoomLevel = false;
  }

  change() {
    const {
      p1,
      startP1,
      p2,
      startP2,
      pswp
    } = this.gestures;
    const {
      currSlide
    } = pswp;

    if (!currSlide) {
      return;
    }

    const minZoomLevel = currSlide.zoomLevels.min;
    const maxZoomLevel = currSlide.zoomLevels.max;

    if (!currSlide.isZoomable() || pswp.mainScroll.isShifted()) {
      return;
    }

    getZoomPointsCenter(this._startZoomPoint, startP1, startP2);
    getZoomPointsCenter(this._zoomPoint, p1, p2);

    let currZoomLevel = 1 / getDistanceBetween(startP1, startP2) * getDistanceBetween(p1, p2) * this._startZoomLevel; // slightly over the zoom.fit


    if (currZoomLevel > currSlide.zoomLevels.initial + currSlide.zoomLevels.initial / 15) {
      this._wasOverFitZoomLevel = true;
    }

    if (currZoomLevel < minZoomLevel) {
      if (pswp.options.pinchToClose && !this._wasOverFitZoomLevel && this._startZoomLevel <= currSlide.zoomLevels.initial) {
        // fade out background if zooming out
        const bgOpacity = 1 - (minZoomLevel - currZoomLevel) / (minZoomLevel / 1.2);

        if (!pswp.dispatch('pinchClose', {
          bgOpacity
        }).defaultPrevented) {
          pswp.applyBgOpacity(bgOpacity);
        }
      } else {
        // Apply the friction if zoom level is below the min
        currZoomLevel = minZoomLevel - (minZoomLevel - currZoomLevel) * LOWER_ZOOM_FRICTION;
      }
    } else if (currZoomLevel > maxZoomLevel) {
      // Apply the friction if zoom level is above the max
      currZoomLevel = maxZoomLevel + (currZoomLevel - maxZoomLevel) * UPPER_ZOOM_FRICTION;
    }

    currSlide.pan.x = this._calculatePanForZoomLevel('x', currZoomLevel);
    currSlide.pan.y = this._calculatePanForZoomLevel('y', currZoomLevel);
    currSlide.setZoomLevel(currZoomLevel);
    currSlide.applyCurrentZoomPan();
  }

  end() {
    const {
      pswp
    } = this.gestures;
    const {
      currSlide
    } = pswp;

    if ((!currSlide || currSlide.currZoomLevel < currSlide.zoomLevels.initial) && !this._wasOverFitZoomLevel && pswp.options.pinchToClose) {
      pswp.close();
    } else {
      this.correctZoomPan();
    }
  }
  /**
   * @private
   * @param {'x' | 'y'} axis
   * @param {number} currZoomLevel
   * @returns {number}
   */


  _calculatePanForZoomLevel(axis, currZoomLevel) {
    const zoomFactor = currZoomLevel / this._startZoomLevel;
    return this._zoomPoint[axis] - (this._startZoomPoint[axis] - this._startPan[axis]) * zoomFactor;
  }
  /**
   * Correct currZoomLevel and pan if they are
   * beyond minimum or maximum values.
   * With animation.
   *
   * @param {boolean} [ignoreGesture]
   * Wether gesture coordinates should be ignored when calculating destination pan position.
   */


  correctZoomPan(ignoreGesture) {
    const {
      pswp
    } = this.gestures;
    const {
      currSlide
    } = pswp;

    if (!(currSlide !== null && currSlide !== void 0 && currSlide.isZoomable())) {
      return;
    }

    if (this._zoomPoint.x === 0) {
      ignoreGesture = true;
    }

    const prevZoomLevel = currSlide.currZoomLevel;
    /** @type {number} */

    let destinationZoomLevel;
    let currZoomLevelNeedsChange = true;

    if (prevZoomLevel < currSlide.zoomLevels.initial) {
      destinationZoomLevel = currSlide.zoomLevels.initial; // zoom to min
    } else if (prevZoomLevel > currSlide.zoomLevels.max) {
      destinationZoomLevel = currSlide.zoomLevels.max; // zoom to max
    } else {
      currZoomLevelNeedsChange = false;
      destinationZoomLevel = prevZoomLevel;
    }

    const initialBgOpacity = pswp.bgOpacity;
    const restoreBgOpacity = pswp.bgOpacity < 1;
    const initialPan = equalizePoints({
      x: 0,
      y: 0
    }, currSlide.pan);
    let destinationPan = equalizePoints({
      x: 0,
      y: 0
    }, initialPan);

    if (ignoreGesture) {
      this._zoomPoint.x = 0;
      this._zoomPoint.y = 0;
      this._startZoomPoint.x = 0;
      this._startZoomPoint.y = 0;
      this._startZoomLevel = prevZoomLevel;
      equalizePoints(this._startPan, initialPan);
    }

    if (currZoomLevelNeedsChange) {
      destinationPan = {
        x: this._calculatePanForZoomLevel('x', destinationZoomLevel),
        y: this._calculatePanForZoomLevel('y', destinationZoomLevel)
      };
    } // set zoom level, so pan bounds are updated according to it


    currSlide.setZoomLevel(destinationZoomLevel);
    destinationPan = {
      x: currSlide.bounds.correctPan('x', destinationPan.x),
      y: currSlide.bounds.correctPan('y', destinationPan.y)
    }; // return zoom level and its bounds to initial

    currSlide.setZoomLevel(prevZoomLevel);
    const panNeedsChange = !pointsEqual(destinationPan, initialPan);

    if (!panNeedsChange && !currZoomLevelNeedsChange && !restoreBgOpacity) {
      // update resolution after gesture
      currSlide._setResolution(destinationZoomLevel);

      currSlide.applyCurrentZoomPan(); // nothing to animate

      return;
    }

    pswp.animations.stopAllPan();
    pswp.animations.startSpring({
      isPan: true,
      start: 0,
      end: 1000,
      velocity: 0,
      dampingRatio: 1,
      naturalFrequency: 40,
      onUpdate: now => {
        now /= 1000; // 0 - start, 1 - end

        if (panNeedsChange || currZoomLevelNeedsChange) {
          if (panNeedsChange) {
            currSlide.pan.x = initialPan.x + (destinationPan.x - initialPan.x) * now;
            currSlide.pan.y = initialPan.y + (destinationPan.y - initialPan.y) * now;
          }

          if (currZoomLevelNeedsChange) {
            const newZoomLevel = prevZoomLevel + (destinationZoomLevel - prevZoomLevel) * now;
            currSlide.setZoomLevel(newZoomLevel);
          }

          currSlide.applyCurrentZoomPan();
        } // Restore background opacity


        if (restoreBgOpacity && pswp.bgOpacity < 1) {
          // We clamp opacity to keep it between 0 and 1.
          // As progress ratio can be larger than 1 due to overshoot,
          // and we do not want to bounce opacity.
          pswp.applyBgOpacity(clamp(initialBgOpacity + (1 - initialBgOpacity) * now, 0, 1));
        }
      },
      onComplete: () => {
        // update resolution after transition ends
        currSlide._setResolution(destinationZoomLevel);

        currSlide.applyCurrentZoomPan();
      }
    });
  }

}

/**
 * @template {string} T
 * @template {string} P
 * @typedef {import('../types.js').AddPostfix<T, P>} AddPostfix<T, P>
 */

/** @typedef {import('./gestures.js').default} Gestures */

/** @typedef {import('../photoswipe.js').Point} Point */

/** @typedef {'imageClick' | 'bgClick' | 'tap' | 'doubleTap'} Actions */

/**
 * Whether the tap was performed on the main slide
 * (rather than controls or caption).
 *
 * @param {PointerEvent} event
 * @returns {boolean}
 */
function didTapOnMainContent(event) {
  return !!
  /** @type {HTMLElement} */
  event.target.closest('.pswp__container');
}
/**
 * Tap, double-tap handler.
 */


class TapHandler {
  /**
   * @param {Gestures} gestures
   */
  constructor(gestures) {
    this.gestures = gestures;
  }
  /**
   * @param {Point} point
   * @param {PointerEvent} originalEvent
   */


  click(point, originalEvent) {
    const targetClassList =
    /** @type {HTMLElement} */
    originalEvent.target.classList;
    const isImageClick = targetClassList.contains('pswp__img');
    const isBackgroundClick = targetClassList.contains('pswp__item') || targetClassList.contains('pswp__zoom-wrap');

    if (isImageClick) {
      this._doClickOrTapAction('imageClick', point, originalEvent);
    } else if (isBackgroundClick) {
      this._doClickOrTapAction('bgClick', point, originalEvent);
    }
  }
  /**
   * @param {Point} point
   * @param {PointerEvent} originalEvent
   */


  tap(point, originalEvent) {
    if (didTapOnMainContent(originalEvent)) {
      this._doClickOrTapAction('tap', point, originalEvent);
    }
  }
  /**
   * @param {Point} point
   * @param {PointerEvent} originalEvent
   */


  doubleTap(point, originalEvent) {
    if (didTapOnMainContent(originalEvent)) {
      this._doClickOrTapAction('doubleTap', point, originalEvent);
    }
  }
  /**
   * @private
   * @param {Actions} actionName
   * @param {Point} point
   * @param {PointerEvent} originalEvent
   */


  _doClickOrTapAction(actionName, point, originalEvent) {
    var _this$gestures$pswp$e;

    const {
      pswp
    } = this.gestures;
    const {
      currSlide
    } = pswp;
    const actionFullName =
    /** @type {AddPostfix<Actions, 'Action'>} */
    actionName + 'Action';
    const optionValue = pswp.options[actionFullName];

    if (pswp.dispatch(actionFullName, {
      point,
      originalEvent
    }).defaultPrevented) {
      return;
    }

    if (typeof optionValue === 'function') {
      optionValue.call(pswp, point, originalEvent);
      return;
    }

    switch (optionValue) {
      case 'close':
      case 'next':
        pswp[optionValue]();
        break;

      case 'zoom':
        currSlide === null || currSlide === void 0 || currSlide.toggleZoom(point);
        break;

      case 'zoom-or-close':
        // by default click zooms current image,
        // if it can not be zoomed - gallery will be closed
        if (currSlide !== null && currSlide !== void 0 && currSlide.isZoomable() && currSlide.zoomLevels.secondary !== currSlide.zoomLevels.initial) {
          currSlide.toggleZoom(point);
        } else if (pswp.options.clickToCloseNonZoomable) {
          pswp.close();
        }

        break;

      case 'toggle-controls':
        (_this$gestures$pswp$e = this.gestures.pswp.element) === null || _this$gestures$pswp$e === void 0 || _this$gestures$pswp$e.classList.toggle('pswp--ui-visible'); // if (_controlsVisible) {
        //   _ui.hideControls();
        // } else {
        //   _ui.showControls();
        // }

        break;
    }
  }

}

/** @typedef {import('../photoswipe.js').default} PhotoSwipe */

/** @typedef {import('../photoswipe.js').Point} Point */
// How far should user should drag
// until we can determine that the gesture is swipe and its direction

const AXIS_SWIPE_HYSTERISIS = 10; //const PAN_END_FRICTION = 0.35;

const DOUBLE_TAP_DELAY = 300; // ms

const MIN_TAP_DISTANCE = 25; // px

/**
 * Gestures class bind touch, pointer or mouse events
 * and emits drag to drag-handler and zoom events zoom-handler.
 *
 * Drag and zoom events are emited in requestAnimationFrame,
 * and only when one of pointers was actually changed.
 */

class Gestures {
  /**
   * @param {PhotoSwipe} pswp
   */
  constructor(pswp) {
    this.pswp = pswp;
    /** @type {'x' | 'y' | null} */

    this.dragAxis = null; // point objects are defined once and reused
    // PhotoSwipe keeps track only of two pointers, others are ignored

    /** @type {Point} */

    this.p1 = {
      x: 0,
      y: 0
    }; // the first pressed pointer

    /** @type {Point} */

    this.p2 = {
      x: 0,
      y: 0
    }; // the second pressed pointer

    /** @type {Point} */

    this.prevP1 = {
      x: 0,
      y: 0
    };
    /** @type {Point} */

    this.prevP2 = {
      x: 0,
      y: 0
    };
    /** @type {Point} */

    this.startP1 = {
      x: 0,
      y: 0
    };
    /** @type {Point} */

    this.startP2 = {
      x: 0,
      y: 0
    };
    /** @type {Point} */

    this.velocity = {
      x: 0,
      y: 0
    };
    /** @type {Point}
     * @private
     */

    this._lastStartP1 = {
      x: 0,
      y: 0
    };
    /** @type {Point}
     * @private
     */

    this._intervalP1 = {
      x: 0,
      y: 0
    };
    /** @private */

    this._numActivePoints = 0;
    /** @type {Point[]}
     * @private
     */

    this._ongoingPointers = [];
    /** @private */

    this._touchEventEnabled = 'ontouchstart' in window;
    /** @private */

    this._pointerEventEnabled = !!window.PointerEvent;
    this.supportsTouch = this._touchEventEnabled || this._pointerEventEnabled && navigator.maxTouchPoints > 1;
    /** @private */

    this._numActivePoints = 0;
    /** @private */

    this._intervalTime = 0;
    /** @private */

    this._velocityCalculated = false;
    this.isMultitouch = false;
    this.isDragging = false;
    this.isZooming = false;
    /** @type {number | null} */

    this.raf = null;
    /** @type {NodeJS.Timeout | null}
     * @private
     */

    this._tapTimer = null;

    if (!this.supportsTouch) {
      // disable pan to next slide for non-touch devices
      pswp.options.allowPanToNext = false;
    }

    this.drag = new DragHandler(this);
    this.zoomLevels = new ZoomHandler(this);
    this.tapHandler = new TapHandler(this);
    pswp.on('bindEvents', () => {
      pswp.events.add(pswp.scrollWrap, 'click',
      /** @type EventListener */
      this._onClick.bind(this));

      if (this._pointerEventEnabled) {
        this._bindEvents('pointer', 'down', 'up', 'cancel');
      } else if (this._touchEventEnabled) {
        this._bindEvents('touch', 'start', 'end', 'cancel'); // In previous versions we also bound mouse event here,
        // in case device supports both touch and mouse events,
        // but newer versions of browsers now support PointerEvent.
        // on iOS10 if you bind touchmove/end after touchstart,
        // and you don't preventDefault touchstart (which PhotoSwipe does),
        // preventDefault will have no effect on touchmove and touchend.
        // Unless you bind it previously.


        if (pswp.scrollWrap) {
          pswp.scrollWrap.ontouchmove = () => {};

          pswp.scrollWrap.ontouchend = () => {};
        }
      } else {
        this._bindEvents('mouse', 'down', 'up');
      }
    });
  }
  /**
   * @private
   * @param {'mouse' | 'touch' | 'pointer'} pref
   * @param {'down' | 'start'} down
   * @param {'up' | 'end'} up
   * @param {'cancel'} [cancel]
   */


  _bindEvents(pref, down, up, cancel) {
    const {
      pswp
    } = this;
    const {
      events
    } = pswp;
    const cancelEvent = cancel ? pref + cancel : '';
    events.add(pswp.scrollWrap, pref + down,
    /** @type EventListener */
    this.onPointerDown.bind(this));
    events.add(window, pref + 'move',
    /** @type EventListener */
    this.onPointerMove.bind(this));
    events.add(window, pref + up,
    /** @type EventListener */
    this.onPointerUp.bind(this));

    if (cancelEvent) {
      events.add(pswp.scrollWrap, cancelEvent,
      /** @type EventListener */
      this.onPointerUp.bind(this));
    }
  }
  /**
   * @param {PointerEvent} e
   */


  onPointerDown(e) {
    // We do not call preventDefault for touch events
    // to allow browser to show native dialog on longpress
    // (the one that allows to save image or open it in new tab).
    //
    // Desktop Safari allows to drag images when preventDefault isn't called on mousedown,
    // even though preventDefault IS called on mousemove. That's why we preventDefault mousedown.
    const isMousePointer = e.type === 'mousedown' || e.pointerType === 'mouse'; // Allow dragging only via left mouse button.
    // http://www.quirksmode.org/js/events_properties.html
    // https://developer.mozilla.org/en-US/docs/Web/API/event.button

    if (isMousePointer && e.button > 0) {
      return;
    }

    const {
      pswp
    } = this; // if PhotoSwipe is opening or closing

    if (!pswp.opener.isOpen) {
      e.preventDefault();
      return;
    }

    if (pswp.dispatch('pointerDown', {
      originalEvent: e
    }).defaultPrevented) {
      return;
    }

    if (isMousePointer) {
      pswp.mouseDetected(); // preventDefault mouse event to prevent
      // browser image drag feature

      this._preventPointerEventBehaviour(e, 'down');
    }

    pswp.animations.stopAll();

    this._updatePoints(e, 'down');

    if (this._numActivePoints === 1) {
      this.dragAxis = null; // we need to store initial point to determine the main axis,
      // drag is activated only after the axis is determined

      equalizePoints(this.startP1, this.p1);
    }

    if (this._numActivePoints > 1) {
      // Tap or double tap should not trigger if more than one pointer
      this._clearTapTimer();

      this.isMultitouch = true;
    } else {
      this.isMultitouch = false;
    }
  }
  /**
   * @param {PointerEvent} e
   */


  onPointerMove(e) {
    this._preventPointerEventBehaviour(e, 'move');

    if (!this._numActivePoints) {
      return;
    }

    this._updatePoints(e, 'move');

    if (this.pswp.dispatch('pointerMove', {
      originalEvent: e
    }).defaultPrevented) {
      return;
    }

    if (this._numActivePoints === 1 && !this.isDragging) {
      if (!this.dragAxis) {
        this._calculateDragDirection();
      } // Drag axis was detected, emit drag.start


      if (this.dragAxis && !this.isDragging) {
        if (this.isZooming) {
          this.isZooming = false;
          this.zoomLevels.end();
        }

        this.isDragging = true;

        this._clearTapTimer(); // Tap can not trigger after drag
        // Adjust starting point


        this._updateStartPoints();

        this._intervalTime = Date.now(); //this._startTime = this._intervalTime;

        this._velocityCalculated = false;
        equalizePoints(this._intervalP1, this.p1);
        this.velocity.x = 0;
        this.velocity.y = 0;
        this.drag.start();

        this._rafStopLoop();

        this._rafRenderLoop();
      }
    } else if (this._numActivePoints > 1 && !this.isZooming) {
      this._finishDrag();

      this.isZooming = true; // Adjust starting points

      this._updateStartPoints();

      this.zoomLevels.start();

      this._rafStopLoop();

      this._rafRenderLoop();
    }
  }
  /**
   * @private
   */


  _finishDrag() {
    if (this.isDragging) {
      this.isDragging = false; // Try to calculate velocity,
      // if it wasn't calculated yet in drag.change

      if (!this._velocityCalculated) {
        this._updateVelocity(true);
      }

      this.drag.end();
      this.dragAxis = null;
    }
  }
  /**
   * @param {PointerEvent} e
   */


  onPointerUp(e) {
    if (!this._numActivePoints) {
      return;
    }

    this._updatePoints(e, 'up');

    if (this.pswp.dispatch('pointerUp', {
      originalEvent: e
    }).defaultPrevented) {
      return;
    }

    if (this._numActivePoints === 0) {
      this._rafStopLoop();

      if (this.isDragging) {
        this._finishDrag();
      } else if (!this.isZooming && !this.isMultitouch) {
        //this.zoomLevels.correctZoomPan();
        this._finishTap(e);
      }
    }

    if (this._numActivePoints < 2 && this.isZooming) {
      this.isZooming = false;
      this.zoomLevels.end();

      if (this._numActivePoints === 1) {
        // Since we have 1 point left, we need to reinitiate drag
        this.dragAxis = null;

        this._updateStartPoints();
      }
    }
  }
  /**
   * @private
   */


  _rafRenderLoop() {
    if (this.isDragging || this.isZooming) {
      this._updateVelocity();

      if (this.isDragging) {
        // make sure that pointer moved since the last update
        if (!pointsEqual(this.p1, this.prevP1)) {
          this.drag.change();
        }
      } else
        /* if (this.isZooming) */
        {
          if (!pointsEqual(this.p1, this.prevP1) || !pointsEqual(this.p2, this.prevP2)) {
            this.zoomLevels.change();
          }
        }

      this._updatePrevPoints();

      this.raf = requestAnimationFrame(this._rafRenderLoop.bind(this));
    }
  }
  /**
   * Update velocity at 50ms interval
   *
   * @private
   * @param {boolean} [force]
   */


  _updateVelocity(force) {
    const time = Date.now();
    const duration = time - this._intervalTime;

    if (duration < 50 && !force) {
      return;
    }

    this.velocity.x = this._getVelocity('x', duration);
    this.velocity.y = this._getVelocity('y', duration);
    this._intervalTime = time;
    equalizePoints(this._intervalP1, this.p1);
    this._velocityCalculated = true;
  }
  /**
   * @private
   * @param {PointerEvent} e
   */


  _finishTap(e) {
    const {
      mainScroll
    } = this.pswp; // Do not trigger tap events if main scroll is shifted

    if (mainScroll.isShifted()) {
      // restore main scroll position
      // (usually happens if stopped in the middle of animation)
      mainScroll.moveIndexBy(0, true);
      return;
    } // Do not trigger tap for touchcancel or pointercancel


    if (e.type.indexOf('cancel') > 0) {
      return;
    } // Trigger click instead of tap for mouse events


    if (e.type === 'mouseup' || e.pointerType === 'mouse') {
      this.tapHandler.click(this.startP1, e);
      return;
    } // Disable delay if there is no doubleTapAction


    const tapDelay = this.pswp.options.doubleTapAction ? DOUBLE_TAP_DELAY : 0; // If tapTimer is defined - we tapped recently,
    // check if the current tap is close to the previous one,
    // if yes - trigger double tap

    if (this._tapTimer) {
      this._clearTapTimer(); // Check if two taps were more or less on the same place


      if (getDistanceBetween(this._lastStartP1, this.startP1) < MIN_TAP_DISTANCE) {
        this.tapHandler.doubleTap(this.startP1, e);
      }
    } else {
      equalizePoints(this._lastStartP1, this.startP1);
      this._tapTimer = setTimeout(() => {
        this.tapHandler.tap(this.startP1, e);

        this._clearTapTimer();
      }, tapDelay);
    }
  }
  /**
   * @private
   */


  _clearTapTimer() {
    if (this._tapTimer) {
      clearTimeout(this._tapTimer);
      this._tapTimer = null;
    }
  }
  /**
   * Get velocity for axis
   *
   * @private
   * @param {'x' | 'y'} axis
   * @param {number} duration
   * @returns {number}
   */


  _getVelocity(axis, duration) {
    // displacement is like distance, but can be negative.
    const displacement = this.p1[axis] - this._intervalP1[axis];

    if (Math.abs(displacement) > 1 && duration > 5) {
      return displacement / duration;
    }

    return 0;
  }
  /**
   * @private
   */


  _rafStopLoop() {
    if (this.raf) {
      cancelAnimationFrame(this.raf);
      this.raf = null;
    }
  }
  /**
   * @private
   * @param {PointerEvent} e
   * @param {'up' | 'down' | 'move'} pointerType Normalized pointer type
   */


  _preventPointerEventBehaviour(e, pointerType) {
    const preventPointerEvent = this.pswp.applyFilters('preventPointerEvent', true, e, pointerType);

    if (preventPointerEvent) {
      e.preventDefault();
    }
  }
  /**
   * Parses and normalizes points from the touch, mouse or pointer event.
   * Updates p1 and p2.
   *
   * @private
   * @param {PointerEvent | TouchEvent} e
   * @param {'up' | 'down' | 'move'} pointerType Normalized pointer type
   */


  _updatePoints(e, pointerType) {
    if (this._pointerEventEnabled) {
      const pointerEvent =
      /** @type {PointerEvent} */
      e; // Try to find the current pointer in ongoing pointers by its ID

      const pointerIndex = this._ongoingPointers.findIndex(ongoingPointer => {
        return ongoingPointer.id === pointerEvent.pointerId;
      });

      if (pointerType === 'up' && pointerIndex > -1) {
        // release the pointer - remove it from ongoing
        this._ongoingPointers.splice(pointerIndex, 1);
      } else if (pointerType === 'down' && pointerIndex === -1) {
        // add new pointer
        this._ongoingPointers.push(this._convertEventPosToPoint(pointerEvent, {
          x: 0,
          y: 0
        }));
      } else if (pointerIndex > -1) {
        // update existing pointer
        this._convertEventPosToPoint(pointerEvent, this._ongoingPointers[pointerIndex]);
      }

      this._numActivePoints = this._ongoingPointers.length; // update points that PhotoSwipe uses
      // to calculate position and scale

      if (this._numActivePoints > 0) {
        equalizePoints(this.p1, this._ongoingPointers[0]);
      }

      if (this._numActivePoints > 1) {
        equalizePoints(this.p2, this._ongoingPointers[1]);
      }
    } else {
      const touchEvent =
      /** @type {TouchEvent} */
      e;
      this._numActivePoints = 0;

      if (touchEvent.type.indexOf('touch') > -1) {
        // Touch Event
        // https://developer.mozilla.org/en-US/docs/Web/API/TouchEvent
        if (touchEvent.touches && touchEvent.touches.length > 0) {
          this._convertEventPosToPoint(touchEvent.touches[0], this.p1);

          this._numActivePoints++;

          if (touchEvent.touches.length > 1) {
            this._convertEventPosToPoint(touchEvent.touches[1], this.p2);

            this._numActivePoints++;
          }
        }
      } else {
        // Mouse Event
        this._convertEventPosToPoint(
        /** @type {PointerEvent} */
        e, this.p1);

        if (pointerType === 'up') {
          // clear all points on mouseup
          this._numActivePoints = 0;
        } else {
          this._numActivePoints++;
        }
      }
    }
  }
  /** update points that were used during previous rAF tick
   * @private
   */


  _updatePrevPoints() {
    equalizePoints(this.prevP1, this.p1);
    equalizePoints(this.prevP2, this.p2);
  }
  /** update points at the start of gesture
   * @private
   */


  _updateStartPoints() {
    equalizePoints(this.startP1, this.p1);
    equalizePoints(this.startP2, this.p2);

    this._updatePrevPoints();
  }
  /** @private */


  _calculateDragDirection() {
    if (this.pswp.mainScroll.isShifted()) {
      // if main scroll position is shifted – direction is always horizontal
      this.dragAxis = 'x';
    } else {
      // calculate delta of the last touchmove tick
      const diff = Math.abs(this.p1.x - this.startP1.x) - Math.abs(this.p1.y - this.startP1.y);

      if (diff !== 0) {
        // check if pointer was shifted horizontally or vertically
        const axisToCheck = diff > 0 ? 'x' : 'y';

        if (Math.abs(this.p1[axisToCheck] - this.startP1[axisToCheck]) >= AXIS_SWIPE_HYSTERISIS) {
          this.dragAxis = axisToCheck;
        }
      }
    }
  }
  /**
   * Converts touch, pointer or mouse event
   * to PhotoSwipe point.
   *
   * @private
   * @param {Touch | PointerEvent} e
   * @param {Point} p
   * @returns {Point}
   */


  _convertEventPosToPoint(e, p) {
    p.x = e.pageX - this.pswp.offset.x;
    p.y = e.pageY - this.pswp.offset.y;

    if ('pointerId' in e) {
      p.id = e.pointerId;
    } else if (e.identifier !== undefined) {
      p.id = e.identifier;
    }

    return p;
  }
  /**
   * @private
   * @param {PointerEvent} e
   */


  _onClick(e) {
    // Do not allow click event to pass through after drag
    if (this.pswp.mainScroll.isShifted()) {
      e.preventDefault();
      e.stopPropagation();
    }
  }

}

/** @typedef {import('./photoswipe.js').default} PhotoSwipe */

/** @typedef {import('./slide/slide.js').default} Slide */

/** @typedef {{ el: HTMLDivElement; slide?: Slide }} ItemHolder */

const MAIN_SCROLL_END_FRICTION = 0.35; // const MIN_SWIPE_TRANSITION_DURATION = 250;
// const MAX_SWIPE_TRABSITION_DURATION = 500;
// const DEFAULT_SWIPE_TRANSITION_DURATION = 333;

/**
 * Handles movement of the main scrolling container
 * (for example, it repositions when user swipes left or right).
 *
 * Also stores its state.
 */

class MainScroll {
  /**
   * @param {PhotoSwipe} pswp
   */
  constructor(pswp) {
    this.pswp = pswp;
    this.x = 0;
    this.slideWidth = 0;
    /** @private */

    this._currPositionIndex = 0;
    /** @private */

    this._prevPositionIndex = 0;
    /** @private */

    this._containerShiftIndex = -1;
    /** @type {ItemHolder[]} */

    this.itemHolders = [];
  }
  /**
   * Position the scroller and slide containers
   * according to viewport size.
   *
   * @param {boolean} [resizeSlides] Whether slides content should resized
   */


  resize(resizeSlides) {
    const {
      pswp
    } = this;
    const newSlideWidth = Math.round(pswp.viewportSize.x + pswp.viewportSize.x * pswp.options.spacing); // Mobile browsers might trigger a resize event during a gesture.
    // (due to toolbar appearing or hiding).
    // Avoid re-adjusting main scroll position if width wasn't changed

    const slideWidthChanged = newSlideWidth !== this.slideWidth;

    if (slideWidthChanged) {
      this.slideWidth = newSlideWidth;
      this.moveTo(this.getCurrSlideX());
    }

    this.itemHolders.forEach((itemHolder, index) => {
      if (slideWidthChanged) {
        setTransform(itemHolder.el, (index + this._containerShiftIndex) * this.slideWidth);
      }

      if (resizeSlides && itemHolder.slide) {
        itemHolder.slide.resize();
      }
    });
  }
  /**
   * Reset X position of the main scroller to zero
   */


  resetPosition() {
    // Position on the main scroller (offset)
    // it is independent from slide index
    this._currPositionIndex = 0;
    this._prevPositionIndex = 0; // This will force recalculation of size on next resize()

    this.slideWidth = 0; // _containerShiftIndex*viewportSize will give you amount of transform of the current slide

    this._containerShiftIndex = -1;
  }
  /**
   * Create and append array of three items
   * that hold data about slides in DOM
   */


  appendHolders() {
    this.itemHolders = []; // append our three slide holders -
    // previous, current, and next

    for (let i = 0; i < 3; i++) {
      const el = createElement('pswp__item', 'div', this.pswp.container);
      el.setAttribute('role', 'group');
      el.setAttribute('aria-roledescription', 'slide');
      el.setAttribute('aria-hidden', 'true'); // hide nearby item holders until initial zoom animation finishes (to avoid extra Paints)

      el.style.display = i === 1 ? 'block' : 'none';
      this.itemHolders.push({
        el //index: -1

      });
    }
  }
  /**
   * Whether the main scroll can be horizontally swiped to the next or previous slide.
   * @returns {boolean}
   */


  canBeSwiped() {
    return this.pswp.getNumItems() > 1;
  }
  /**
   * Move main scroll by X amount of slides.
   * For example:
   *   `-1` will move to the previous slide,
   *    `0` will reset the scroll position of the current slide,
   *    `3` will move three slides forward
   *
   * If loop option is enabled - index will be automatically looped too,
   * (for example `-1` will move to the last slide of the gallery).
   *
   * @param {number} diff
   * @param {boolean} [animate]
   * @param {number} [velocityX]
   * @returns {boolean} whether index was changed or not
   */


  moveIndexBy(diff, animate, velocityX) {
    const {
      pswp
    } = this;
    let newIndex = pswp.potentialIndex + diff;
    const numSlides = pswp.getNumItems();

    if (pswp.canLoop()) {
      newIndex = pswp.getLoopedIndex(newIndex);
      const distance = (diff + numSlides) % numSlides;

      if (distance <= numSlides / 2) {
        // go forward
        diff = distance;
      } else {
        // go backwards
        diff = distance - numSlides;
      }
    } else {
      if (newIndex < 0) {
        newIndex = 0;
      } else if (newIndex >= numSlides) {
        newIndex = numSlides - 1;
      }

      diff = newIndex - pswp.potentialIndex;
    }

    pswp.potentialIndex = newIndex;
    this._currPositionIndex -= diff;
    pswp.animations.stopMainScroll();
    const destinationX = this.getCurrSlideX();

    if (!animate) {
      this.moveTo(destinationX);
      this.updateCurrItem();
    } else {
      pswp.animations.startSpring({
        isMainScroll: true,
        start: this.x,
        end: destinationX,
        velocity: velocityX || 0,
        naturalFrequency: 30,
        dampingRatio: 1,
        //0.7,
        onUpdate: x => {
          this.moveTo(x);
        },
        onComplete: () => {
          this.updateCurrItem();
          pswp.appendHeavy();
        }
      });
      let currDiff = pswp.potentialIndex - pswp.currIndex;

      if (pswp.canLoop()) {
        const currDistance = (currDiff + numSlides) % numSlides;

        if (currDistance <= numSlides / 2) {
          // go forward
          currDiff = currDistance;
        } else {
          // go backwards
          currDiff = currDistance - numSlides;
        }
      } // Force-append new slides during transition
      // if difference between slides is more than 1


      if (Math.abs(currDiff) > 1) {
        this.updateCurrItem();
      }
    }

    return Boolean(diff);
  }
  /**
   * X position of the main scroll for the current slide
   * (ignores position during dragging)
   * @returns {number}
   */


  getCurrSlideX() {
    return this.slideWidth * this._currPositionIndex;
  }
  /**
   * Whether scroll position is shifted.
   * For example, it will return true if the scroll is being dragged or animated.
   * @returns {boolean}
   */


  isShifted() {
    return this.x !== this.getCurrSlideX();
  }
  /**
   * Update slides X positions and set their content
   */


  updateCurrItem() {
    var _this$itemHolders$;

    const {
      pswp
    } = this;
    const positionDifference = this._prevPositionIndex - this._currPositionIndex;

    if (!positionDifference) {
      return;
    }

    this._prevPositionIndex = this._currPositionIndex;
    pswp.currIndex = pswp.potentialIndex;
    let diffAbs = Math.abs(positionDifference);
    /** @type {ItemHolder | undefined} */

    let tempHolder;

    if (diffAbs >= 3) {
      this._containerShiftIndex += positionDifference + (positionDifference > 0 ? -3 : 3);
      diffAbs = 3; // If slides are changed by 3 screens or more - clean up previous slides

      this.itemHolders.forEach(itemHolder => {
        var _itemHolder$slide;

        (_itemHolder$slide = itemHolder.slide) === null || _itemHolder$slide === void 0 || _itemHolder$slide.destroy();
        itemHolder.slide = undefined;
      });
    }

    for (let i = 0; i < diffAbs; i++) {
      if (positionDifference > 0) {
        tempHolder = this.itemHolders.shift();

        if (tempHolder) {
          this.itemHolders[2] = tempHolder; // move first to last

          this._containerShiftIndex++;
          setTransform(tempHolder.el, (this._containerShiftIndex + 2) * this.slideWidth);
          pswp.setContent(tempHolder, pswp.currIndex - diffAbs + i + 2);
        }
      } else {
        tempHolder = this.itemHolders.pop();

        if (tempHolder) {
          this.itemHolders.unshift(tempHolder); // move last to first

          this._containerShiftIndex--;
          setTransform(tempHolder.el, this._containerShiftIndex * this.slideWidth);
          pswp.setContent(tempHolder, pswp.currIndex + diffAbs - i - 2);
        }
      }
    } // Reset transfrom every 50ish navigations in one direction.
    //
    // Otherwise transform will keep growing indefinitely,
    // which might cause issues as browsers have a maximum transform limit.
    // I wasn't able to reach it, but just to be safe.
    // This should not cause noticable lag.


    if (Math.abs(this._containerShiftIndex) > 50 && !this.isShifted()) {
      this.resetPosition();
      this.resize();
    } // Pan transition might be running (and consntantly updating pan position)


    pswp.animations.stopAllPan();
    this.itemHolders.forEach((itemHolder, i) => {
      if (itemHolder.slide) {
        // Slide in the 2nd holder is always active
        itemHolder.slide.setIsActive(i === 1);
      }
    });
    pswp.currSlide = (_this$itemHolders$ = this.itemHolders[1]) === null || _this$itemHolders$ === void 0 ? void 0 : _this$itemHolders$.slide;
    pswp.contentLoader.updateLazy(positionDifference);

    if (pswp.currSlide) {
      pswp.currSlide.applyCurrentZoomPan();
    }

    pswp.dispatch('change');
  }
  /**
   * Move the X position of the main scroll container
   *
   * @param {number} x
   * @param {boolean} [dragging]
   */


  moveTo(x, dragging) {
    if (!this.pswp.canLoop() && dragging) {
      // Apply friction
      let newSlideIndexOffset = (this.slideWidth * this._currPositionIndex - x) / this.slideWidth;
      newSlideIndexOffset += this.pswp.currIndex;
      const delta = Math.round(x - this.x);

      if (newSlideIndexOffset < 0 && delta > 0 || newSlideIndexOffset >= this.pswp.getNumItems() - 1 && delta < 0) {
        x = this.x + delta * MAIN_SCROLL_END_FRICTION;
      }
    }

    this.x = x;

    if (this.pswp.container) {
      setTransform(this.pswp.container, x);
    }

    this.pswp.dispatch('moveMainScroll', {
      x,
      dragging: dragging !== null && dragging !== void 0 ? dragging : false
    });
  }

}

/** @typedef {import('./photoswipe.js').default} PhotoSwipe */

/**
 * @template T
 * @typedef {import('./types.js').Methods<T>} Methods<T>
 */

const KeyboardKeyCodesMap = {
  Escape: 27,
  z: 90,
  ArrowLeft: 37,
  ArrowUp: 38,
  ArrowRight: 39,
  ArrowDown: 40,
  Tab: 9
};
/**
 * @template {keyof KeyboardKeyCodesMap} T
 * @param {T} key
 * @param {boolean} isKeySupported
 * @returns {T | number | undefined}
 */

const getKeyboardEventKey = (key, isKeySupported) => {
  return isKeySupported ? key : KeyboardKeyCodesMap[key];
};
/**
 * - Manages keyboard shortcuts.
 * - Helps trap focus within photoswipe.
 */


class Keyboard {
  /**
   * @param {PhotoSwipe} pswp
   */
  constructor(pswp) {
    this.pswp = pswp;
    /** @private */

    this._wasFocused = false;
    pswp.on('bindEvents', () => {
      if (pswp.options.trapFocus) {
        // Dialog was likely opened by keyboard if initial point is not defined
        if (!pswp.options.initialPointerPos) {
          // focus causes layout,
          // which causes lag during the animation,
          // that's why we delay it until the opener transition ends
          this._focusRoot();
        }

        pswp.events.add(document, 'focusin',
        /** @type EventListener */
        this._onFocusIn.bind(this));
      }

      pswp.events.add(document, 'keydown',
      /** @type EventListener */
      this._onKeyDown.bind(this));
    });
    const lastActiveElement =
    /** @type {HTMLElement} */
    document.activeElement;
    pswp.on('destroy', () => {
      if (pswp.options.returnFocus && lastActiveElement && this._wasFocused) {
        lastActiveElement.focus();
      }
    });
  }
  /** @private */


  _focusRoot() {
    if (!this._wasFocused && this.pswp.element) {
      this.pswp.element.focus();
      this._wasFocused = true;
    }
  }
  /**
   * @private
   * @param {KeyboardEvent} e
   */


  _onKeyDown(e) {
    const {
      pswp
    } = this;

    if (pswp.dispatch('keydown', {
      originalEvent: e
    }).defaultPrevented) {
      return;
    }

    if (specialKeyUsed(e)) {
      // don't do anything if special key pressed
      // to prevent from overriding default browser actions
      // for example, in Chrome on Mac cmd+arrow-left returns to previous page
      return;
    }
    /** @type {Methods<PhotoSwipe> | undefined} */


    let keydownAction;
    /** @type {'x' | 'y' | undefined} */

    let axis;
    let isForward = false;
    const isKeySupported = ('key' in e);

    switch (isKeySupported ? e.key : e.keyCode) {
      case getKeyboardEventKey('Escape', isKeySupported):
        if (pswp.options.escKey) {
          keydownAction = 'close';
        }

        break;

      case getKeyboardEventKey('z', isKeySupported):
        keydownAction = 'toggleZoom';
        break;

      case getKeyboardEventKey('ArrowLeft', isKeySupported):
        axis = 'x';
        break;

      case getKeyboardEventKey('ArrowUp', isKeySupported):
        axis = 'y';
        break;

      case getKeyboardEventKey('ArrowRight', isKeySupported):
        axis = 'x';
        isForward = true;
        break;

      case getKeyboardEventKey('ArrowDown', isKeySupported):
        isForward = true;
        axis = 'y';
        break;

      case getKeyboardEventKey('Tab', isKeySupported):
        this._focusRoot();

        break;
    } // if left/right/top/bottom key


    if (axis) {
      // prevent page scroll
      e.preventDefault();
      const {
        currSlide
      } = pswp;

      if (pswp.options.arrowKeys && axis === 'x' && pswp.getNumItems() > 1) {
        keydownAction = isForward ? 'next' : 'prev';
      } else if (currSlide && currSlide.currZoomLevel > currSlide.zoomLevels.fit) {
        // up/down arrow keys pan the image vertically
        // left/right arrow keys pan horizontally.
        // Unless there is only one image,
        // or arrowKeys option is disabled
        currSlide.pan[axis] += isForward ? -80 : 80;
        currSlide.panTo(currSlide.pan.x, currSlide.pan.y);
      }
    }

    if (keydownAction) {
      e.preventDefault(); // @ts-ignore

      pswp[keydownAction]();
    }
  }
  /**
   * Trap focus inside photoswipe
   *
   * @private
   * @param {FocusEvent} e
   */


  _onFocusIn(e) {
    const {
      template
    } = this.pswp;

    if (template && document !== e.target && template !== e.target && !template.contains(
    /** @type {Node} */
    e.target)) {
      // focus root element
      template.focus();
    }
  }

}

const DEFAULT_EASING = 'cubic-bezier(.4,0,.22,1)';
/** @typedef {import('./animations.js').SharedAnimationProps} SharedAnimationProps */

/** @typedef {Object} DefaultCssAnimationProps
 *
 * @prop {HTMLElement} target
 * @prop {number} [duration]
 * @prop {string} [easing]
 * @prop {string} [transform]
 * @prop {string} [opacity]
 * */

/** @typedef {SharedAnimationProps & DefaultCssAnimationProps} CssAnimationProps */

/**
 * Runs CSS transition.
 */

class CSSAnimation {
  /**
   * onComplete can be unpredictable, be careful about current state
   *
   * @param {CssAnimationProps} props
   */
  constructor(props) {
    var _props$prop;

    this.props = props;
    const {
      target,
      onComplete,
      transform,
      onFinish = () => {},
      duration = 333,
      easing = DEFAULT_EASING
    } = props;
    this.onFinish = onFinish; // support only transform and opacity

    const prop = transform ? 'transform' : 'opacity';
    const propValue = (_props$prop = props[prop]) !== null && _props$prop !== void 0 ? _props$prop : '';
    /** @private */

    this._target = target;
    /** @private */

    this._onComplete = onComplete;
    /** @private */

    this._finished = false;
    /** @private */

    this._onTransitionEnd = this._onTransitionEnd.bind(this); // Using timeout hack to make sure that animation
    // starts even if the animated property was changed recently,
    // otherwise transitionend might not fire or transition won't start.
    // https://drafts.csswg.org/css-transitions/#starting
    //
    // ¯\_(ツ)_/¯

    /** @private */

    this._helperTimeout = setTimeout(() => {
      setTransitionStyle(target, prop, duration, easing);
      this._helperTimeout = setTimeout(() => {
        target.addEventListener('transitionend', this._onTransitionEnd, false);
        target.addEventListener('transitioncancel', this._onTransitionEnd, false); // Safari occasionally does not emit transitionend event
        // if element property was modified during the transition,
        // which may be caused by resize or third party component,
        // using timeout as a safety fallback

        this._helperTimeout = setTimeout(() => {
          this._finalizeAnimation();
        }, duration + 500);
        target.style[prop] = propValue;
      }, 30); // Do not reduce this number
    }, 0);
  }
  /**
   * @private
   * @param {TransitionEvent} e
   */


  _onTransitionEnd(e) {
    if (e.target === this._target) {
      this._finalizeAnimation();
    }
  }
  /**
   * @private
   */


  _finalizeAnimation() {
    if (!this._finished) {
      this._finished = true;
      this.onFinish();

      if (this._onComplete) {
        this._onComplete();
      }
    }
  } // Destroy is called automatically onFinish


  destroy() {
    if (this._helperTimeout) {
      clearTimeout(this._helperTimeout);
    }

    removeTransitionStyle(this._target);

    this._target.removeEventListener('transitionend', this._onTransitionEnd, false);

    this._target.removeEventListener('transitioncancel', this._onTransitionEnd, false);

    if (!this._finished) {
      this._finalizeAnimation();
    }
  }

}

const DEFAULT_NATURAL_FREQUENCY = 12;
const DEFAULT_DAMPING_RATIO = 0.75;
/**
 * Spring easing helper
 */

class SpringEaser {
  /**
   * @param {number} initialVelocity Initial velocity, px per ms.
   *
   * @param {number} [dampingRatio]
   * Determines how bouncy animation will be.
   * From 0 to 1, 0 - always overshoot, 1 - do not overshoot.
   * "overshoot" refers to part of animation that
   * goes beyond the final value.
   *
   * @param {number} [naturalFrequency]
   * Determines how fast animation will slow down.
   * The higher value - the stiffer the transition will be,
   * and the faster it will slow down.
   * Recommended value from 10 to 50
   */
  constructor(initialVelocity, dampingRatio, naturalFrequency) {
    this.velocity = initialVelocity * 1000; // convert to "pixels per second"
    // https://en.wikipedia.org/wiki/Damping_ratio

    this._dampingRatio = dampingRatio || DEFAULT_DAMPING_RATIO; // https://en.wikipedia.org/wiki/Natural_frequency

    this._naturalFrequency = naturalFrequency || DEFAULT_NATURAL_FREQUENCY;
    this._dampedFrequency = this._naturalFrequency;

    if (this._dampingRatio < 1) {
      this._dampedFrequency *= Math.sqrt(1 - this._dampingRatio * this._dampingRatio);
    }
  }
  /**
   * @param {number} deltaPosition Difference between current and end position of the animation
   * @param {number} deltaTime Frame duration in milliseconds
   *
   * @returns {number} Displacement, relative to the end position.
   */


  easeFrame(deltaPosition, deltaTime) {
    // Inspired by Apple Webkit and Android spring function implementation
    // https://en.wikipedia.org/wiki/Oscillation
    // https://en.wikipedia.org/wiki/Damping_ratio
    // we ignore mass (assume that it's 1kg)
    let displacement = 0;
    let coeff;
    deltaTime /= 1000;
    const naturalDumpingPow = Math.E ** (-this._dampingRatio * this._naturalFrequency * deltaTime);

    if (this._dampingRatio === 1) {
      coeff = this.velocity + this._naturalFrequency * deltaPosition;
      displacement = (deltaPosition + coeff * deltaTime) * naturalDumpingPow;
      this.velocity = displacement * -this._naturalFrequency + coeff * naturalDumpingPow;
    } else if (this._dampingRatio < 1) {
      coeff = 1 / this._dampedFrequency * (this._dampingRatio * this._naturalFrequency * deltaPosition + this.velocity);
      const dumpedFCos = Math.cos(this._dampedFrequency * deltaTime);
      const dumpedFSin = Math.sin(this._dampedFrequency * deltaTime);
      displacement = naturalDumpingPow * (deltaPosition * dumpedFCos + coeff * dumpedFSin);
      this.velocity = displacement * -this._naturalFrequency * this._dampingRatio + naturalDumpingPow * (-this._dampedFrequency * deltaPosition * dumpedFSin + this._dampedFrequency * coeff * dumpedFCos);
    } // Overdamped (>1) damping ratio is not supported


    return displacement;
  }

}

/** @typedef {import('./animations.js').SharedAnimationProps} SharedAnimationProps */

/**
 * @typedef {Object} DefaultSpringAnimationProps
 *
 * @prop {number} start
 * @prop {number} end
 * @prop {number} velocity
 * @prop {number} [dampingRatio]
 * @prop {number} [naturalFrequency]
 * @prop {(end: number) => void} onUpdate
 */

/** @typedef {SharedAnimationProps & DefaultSpringAnimationProps} SpringAnimationProps */

class SpringAnimation {
  /**
   * @param {SpringAnimationProps} props
   */
  constructor(props) {
    this.props = props;
    this._raf = 0;
    const {
      start,
      end,
      velocity,
      onUpdate,
      onComplete,
      onFinish = () => {},
      dampingRatio,
      naturalFrequency
    } = props;
    this.onFinish = onFinish;
    const easer = new SpringEaser(velocity, dampingRatio, naturalFrequency);
    let prevTime = Date.now();
    let deltaPosition = start - end;

    const animationLoop = () => {
      if (this._raf) {
        deltaPosition = easer.easeFrame(deltaPosition, Date.now() - prevTime); // Stop the animation if velocity is low and position is close to end

        if (Math.abs(deltaPosition) < 1 && Math.abs(easer.velocity) < 50) {
          // Finalize the animation
          onUpdate(end);

          if (onComplete) {
            onComplete();
          }

          this.onFinish();
        } else {
          prevTime = Date.now();
          onUpdate(deltaPosition + end);
          this._raf = requestAnimationFrame(animationLoop);
        }
      }
    };

    this._raf = requestAnimationFrame(animationLoop);
  } // Destroy is called automatically onFinish


  destroy() {
    if (this._raf >= 0) {
      cancelAnimationFrame(this._raf);
    }

    this._raf = 0;
  }

}

/** @typedef {import('./css-animation.js').CssAnimationProps} CssAnimationProps */

/** @typedef {import('./spring-animation.js').SpringAnimationProps} SpringAnimationProps */

/** @typedef {Object} SharedAnimationProps
 * @prop {string} [name]
 * @prop {boolean} [isPan]
 * @prop {boolean} [isMainScroll]
 * @prop {VoidFunction} [onComplete]
 * @prop {VoidFunction} [onFinish]
 */

/** @typedef {SpringAnimation | CSSAnimation} Animation */

/** @typedef {SpringAnimationProps | CssAnimationProps} AnimationProps */

/**
 * Manages animations
 */

class Animations {
  constructor() {
    /** @type {Animation[]} */
    this.activeAnimations = [];
  }
  /**
   * @param {SpringAnimationProps} props
   */


  startSpring(props) {
    this._start(props, true);
  }
  /**
   * @param {CssAnimationProps} props
   */


  startTransition(props) {
    this._start(props);
  }
  /**
   * @private
   * @param {AnimationProps} props
   * @param {boolean} [isSpring]
   * @returns {Animation}
   */


  _start(props, isSpring) {
    const animation = isSpring ? new SpringAnimation(
    /** @type SpringAnimationProps */
    props) : new CSSAnimation(
    /** @type CssAnimationProps */
    props);
    this.activeAnimations.push(animation);

    animation.onFinish = () => this.stop(animation);

    return animation;
  }
  /**
   * @param {Animation} animation
   */


  stop(animation) {
    animation.destroy();
    const index = this.activeAnimations.indexOf(animation);

    if (index > -1) {
      this.activeAnimations.splice(index, 1);
    }
  }

  stopAll() {
    // _stopAllAnimations
    this.activeAnimations.forEach(animation => {
      animation.destroy();
    });
    this.activeAnimations = [];
  }
  /**
   * Stop all pan or zoom transitions
   */


  stopAllPan() {
    this.activeAnimations = this.activeAnimations.filter(animation => {
      if (animation.props.isPan) {
        animation.destroy();
        return false;
      }

      return true;
    });
  }

  stopMainScroll() {
    this.activeAnimations = this.activeAnimations.filter(animation => {
      if (animation.props.isMainScroll) {
        animation.destroy();
        return false;
      }

      return true;
    });
  }
  /**
   * Returns true if main scroll transition is running
   */
  // isMainScrollRunning() {
  //   return this.activeAnimations.some((animation) => {
  //     return animation.props.isMainScroll;
  //   });
  // }

  /**
   * Returns true if any pan or zoom transition is running
   */


  isPanRunning() {
    return this.activeAnimations.some(animation => {
      return animation.props.isPan;
    });
  }

}

/** @typedef {import('./photoswipe.js').default} PhotoSwipe */

/**
 * Handles scroll wheel.
 * Can pan and zoom current slide image.
 */
class ScrollWheel {
  /**
   * @param {PhotoSwipe} pswp
   */
  constructor(pswp) {
    this.pswp = pswp;
    pswp.events.add(pswp.element, 'wheel',
    /** @type EventListener */
    this._onWheel.bind(this));
  }
  /**
   * @private
   * @param {WheelEvent} e
   */


  _onWheel(e) {
    e.preventDefault();
    const {
      currSlide
    } = this.pswp;
    let {
      deltaX,
      deltaY
    } = e;

    if (!currSlide) {
      return;
    }

    if (this.pswp.dispatch('wheel', {
      originalEvent: e
    }).defaultPrevented) {
      return;
    }

    if (e.ctrlKey || this.pswp.options.wheelToZoom) {
      // zoom
      if (currSlide.isZoomable()) {
        let zoomFactor = -deltaY;

        if (e.deltaMode === 1
        /* DOM_DELTA_LINE */
        ) {
          zoomFactor *= 0.05;
        } else {
          zoomFactor *= e.deltaMode ? 1 : 0.002;
        }

        zoomFactor = 2 ** zoomFactor;
        const destZoomLevel = currSlide.currZoomLevel * zoomFactor;
        currSlide.zoomTo(destZoomLevel, {
          x: e.clientX,
          y: e.clientY
        });
      }
    } else {
      // pan
      if (currSlide.isPannable()) {
        if (e.deltaMode === 1
        /* DOM_DELTA_LINE */
        ) {
          // 18 - average line height
          deltaX *= 18;
          deltaY *= 18;
        }

        currSlide.panTo(currSlide.pan.x - deltaX, currSlide.pan.y - deltaY);
      }
    }
  }

}

/** @typedef {import('../photoswipe.js').default} PhotoSwipe */

/**
 * @template T
 * @typedef {import('../types.js').Methods<T>} Methods<T>
 */

/**
 * @typedef {Object} UIElementMarkupProps
 * @prop {boolean} [isCustomSVG]
 * @prop {string} inner
 * @prop {string} [outlineID]
 * @prop {number | string} [size]
 */

/**
 * @typedef {Object} UIElementData
 * @prop {DefaultUIElements | string} [name]
 * @prop {string} [className]
 * @prop {UIElementMarkup} [html]
 * @prop {boolean} [isButton]
 * @prop {keyof HTMLElementTagNameMap} [tagName]
 * @prop {string} [title]
 * @prop {string} [ariaLabel]
 * @prop {(element: HTMLElement, pswp: PhotoSwipe) => void} [onInit]
 * @prop {Methods<PhotoSwipe> | ((e: MouseEvent, element: HTMLElement, pswp: PhotoSwipe) => void)} [onClick]
 * @prop {'bar' | 'wrapper' | 'root'} [appendTo]
 * @prop {number} [order]
 */

/** @typedef {'arrowPrev' | 'arrowNext' | 'close' | 'zoom' | 'counter'} DefaultUIElements */

/** @typedef {string | UIElementMarkupProps} UIElementMarkup */

/**
 * @param {UIElementMarkup} [htmlData]
 * @returns {string}
 */

function addElementHTML(htmlData) {
  if (typeof htmlData === 'string') {
    // Allow developers to provide full svg,
    // For example:
    // <svg viewBox="0 0 32 32" width="32" height="32" aria-hidden="true" class="pswp__icn">
    //   <path d="..." />
    //   <circle ... />
    // </svg>
    // Can also be any HTML string.
    return htmlData;
  }

  if (!htmlData || !htmlData.isCustomSVG) {
    return '';
  }

  const svgData = htmlData;
  let out = '<svg aria-hidden="true" class="pswp__icn" viewBox="0 0 %d %d" width="%d" height="%d">'; // replace all %d with size

  out = out.split('%d').join(
  /** @type {string} */
  svgData.size || 32); // Icons may contain outline/shadow,
  // to make it we "clone" base icon shape and add border to it.
  // Icon itself and border are styled via CSS.
  //
  // Property shadowID defines ID of element that should be cloned.

  if (svgData.outlineID) {
    out += '<use class="pswp__icn-shadow" xlink:href="#' + svgData.outlineID + '"/>';
  }

  out += svgData.inner;
  out += '</svg>';
  return out;
}

class UIElement {
  /**
   * @param {PhotoSwipe} pswp
   * @param {UIElementData} data
   */
  constructor(pswp, data) {
    var _container;

    const name = data.name || data.className;
    let elementHTML = data.html; // @ts-expect-error lookup only by `data.name` maybe?

    if (pswp.options[name] === false) {
      // exit if element is disabled from options
      return;
    } // Allow to override SVG icons from options
    // @ts-expect-error lookup only by `data.name` maybe?


    if (typeof pswp.options[name + 'SVG'] === 'string') {
      // arrowPrevSVG
      // arrowNextSVG
      // closeSVG
      // zoomSVG
      // @ts-expect-error lookup only by `data.name` maybe?
      elementHTML = pswp.options[name + 'SVG'];
    }

    pswp.dispatch('uiElementCreate', {
      data
    });
    let className = '';

    if (data.isButton) {
      className += 'pswp__button ';
      className += data.className || `pswp__button--${data.name}`;
    } else {
      className += data.className || `pswp__${data.name}`;
    }

    let tagName = data.isButton ? data.tagName || 'button' : data.tagName || 'div';
    tagName =
    /** @type {keyof HTMLElementTagNameMap} */
    tagName.toLowerCase();
    /** @type {HTMLElement} */

    const element = createElement(className, tagName);

    if (data.isButton) {
      if (tagName === 'button') {
        /** @type {HTMLButtonElement} */
        element.type = 'button';
      }

      let {
        title
      } = data;
      const {
        ariaLabel
      } = data; // @ts-expect-error lookup only by `data.name` maybe?

      if (typeof pswp.options[name + 'Title'] === 'string') {
        // @ts-expect-error lookup only by `data.name` maybe?
        title = pswp.options[name + 'Title'];
      }

      if (title) {
        element.title = title;
      }

      const ariaText = ariaLabel || title;

      if (ariaText) {
        element.setAttribute('aria-label', ariaText);
      }
    }

    element.innerHTML = addElementHTML(elementHTML);

    if (data.onInit) {
      data.onInit(element, pswp);
    }

    if (data.onClick) {
      element.onclick = e => {
        if (typeof data.onClick === 'string') {
          // @ts-ignore
          pswp[data.onClick]();
        } else if (typeof data.onClick === 'function') {
          data.onClick(e, element, pswp);
        }
      };
    } // Top bar is default position


    const appendTo = data.appendTo || 'bar';
    /** @type {HTMLElement | undefined} root element by default */

    let container = pswp.element;

    if (appendTo === 'bar') {
      if (!pswp.topBar) {
        pswp.topBar = createElement('pswp__top-bar pswp__hide-on-close', 'div', pswp.scrollWrap);
      }

      container = pswp.topBar;
    } else {
      // element outside of top bar gets a secondary class
      // that makes element fade out on close
      element.classList.add('pswp__hide-on-close');

      if (appendTo === 'wrapper') {
        container = pswp.scrollWrap;
      }
    }

    (_container = container) === null || _container === void 0 || _container.appendChild(pswp.applyFilters('uiElement', element, data));
  }

}

/*
  Backward and forward arrow buttons
 */

/** @typedef {import('./ui-element.js').UIElementData} UIElementData */

/** @typedef {import('../photoswipe.js').default} PhotoSwipe */

/**
 *
 * @param {HTMLElement} element
 * @param {PhotoSwipe} pswp
 * @param {boolean} [isNextButton]
 */
function initArrowButton(element, pswp, isNextButton) {
  element.classList.add('pswp__button--arrow'); // TODO: this should point to a unique id for this instance

  element.setAttribute('aria-controls', 'pswp__items');
  pswp.on('change', () => {
    if (!pswp.options.loop) {
      if (isNextButton) {
        /** @type {HTMLButtonElement} */
        element.disabled = !(pswp.currIndex < pswp.getNumItems() - 1);
      } else {
        /** @type {HTMLButtonElement} */
        element.disabled = !(pswp.currIndex > 0);
      }
    }
  });
}
/** @type {UIElementData} */


const arrowPrev = {
  name: 'arrowPrev',
  className: 'pswp__button--arrow--prev',
  title: 'Previous',
  order: 10,
  isButton: true,
  appendTo: 'wrapper',
  html: {
    isCustomSVG: true,
    size: 60,
    inner: '<path d="M29 43l-3 3-16-16 16-16 3 3-13 13 13 13z" id="pswp__icn-arrow"/>',
    outlineID: 'pswp__icn-arrow'
  },
  onClick: 'prev',
  onInit: initArrowButton
};
/** @type {UIElementData} */

const arrowNext = {
  name: 'arrowNext',
  className: 'pswp__button--arrow--next',
  title: 'Next',
  order: 11,
  isButton: true,
  appendTo: 'wrapper',
  html: {
    isCustomSVG: true,
    size: 60,
    inner: '<use xlink:href="#pswp__icn-arrow"/>',
    outlineID: 'pswp__icn-arrow'
  },
  onClick: 'next',
  onInit: (el, pswp) => {
    initArrowButton(el, pswp, true);
  }
};

/** @type {import('./ui-element.js').UIElementData} UIElementData */
const closeButton = {
  name: 'close',
  title: 'Close',
  order: 20,
  isButton: true,
  html: {
    isCustomSVG: true,
    inner: '<path d="M24 10l-2-2-6 6-6-6-2 2 6 6-6 6 2 2 6-6 6 6 2-2-6-6z" id="pswp__icn-close"/>',
    outlineID: 'pswp__icn-close'
  },
  onClick: 'close'
};

/** @type {import('./ui-element.js').UIElementData} UIElementData */
const zoomButton = {
  name: 'zoom',
  title: 'Zoom',
  order: 10,
  isButton: true,
  html: {
    isCustomSVG: true,
    // eslint-disable-next-line max-len
    inner: '<path d="M17.426 19.926a6 6 0 1 1 1.5-1.5L23 22.5 21.5 24l-4.074-4.074z" id="pswp__icn-zoom"/>' + '<path fill="currentColor" class="pswp__zoom-icn-bar-h" d="M11 16v-2h6v2z"/>' + '<path fill="currentColor" class="pswp__zoom-icn-bar-v" d="M13 12h2v6h-2z"/>',
    outlineID: 'pswp__icn-zoom'
  },
  onClick: 'toggleZoom'
};

/** @type {import('./ui-element.js').UIElementData} UIElementData */
const loadingIndicator = {
  name: 'preloader',
  appendTo: 'bar',
  order: 7,
  html: {
    isCustomSVG: true,
    // eslint-disable-next-line max-len
    inner: '<path fill-rule="evenodd" clip-rule="evenodd" d="M21.2 16a5.2 5.2 0 1 1-5.2-5.2V8a8 8 0 1 0 8 8h-2.8Z" id="pswp__icn-loading"/>',
    outlineID: 'pswp__icn-loading'
  },
  onInit: (indicatorElement, pswp) => {
    /** @type {boolean | undefined} */
    let isVisible;
    /** @type {NodeJS.Timeout | null} */

    let delayTimeout = null;
    /**
     * @param {string} className
     * @param {boolean} add
     */

    const toggleIndicatorClass = (className, add) => {
      indicatorElement.classList.toggle('pswp__preloader--' + className, add);
    };
    /**
     * @param {boolean} visible
     */


    const setIndicatorVisibility = visible => {
      if (isVisible !== visible) {
        isVisible = visible;
        toggleIndicatorClass('active', visible);
      }
    };

    const updatePreloaderVisibility = () => {
      var _pswp$currSlide;

      if (!((_pswp$currSlide = pswp.currSlide) !== null && _pswp$currSlide !== void 0 && _pswp$currSlide.content.isLoading())) {
        setIndicatorVisibility(false);

        if (delayTimeout) {
          clearTimeout(delayTimeout);
          delayTimeout = null;
        }

        return;
      }

      if (!delayTimeout) {
        // display loading indicator with delay
        delayTimeout = setTimeout(() => {
          var _pswp$currSlide2;

          setIndicatorVisibility(Boolean((_pswp$currSlide2 = pswp.currSlide) === null || _pswp$currSlide2 === void 0 ? void 0 : _pswp$currSlide2.content.isLoading()));
          delayTimeout = null;
        }, pswp.options.preloaderDelay);
      }
    };

    pswp.on('change', updatePreloaderVisibility);
    pswp.on('loadComplete', e => {
      if (pswp.currSlide === e.slide) {
        updatePreloaderVisibility();
      }
    }); // expose the method

    if (pswp.ui) {
      pswp.ui.updatePreloaderVisibility = updatePreloaderVisibility;
    }
  }
};

/** @type {import('./ui-element.js').UIElementData} UIElementData */
const counterIndicator = {
  name: 'counter',
  order: 5,
  onInit: (counterElement, pswp) => {
    pswp.on('change', () => {
      counterElement.innerText = pswp.currIndex + 1 + pswp.options.indexIndicatorSep + pswp.getNumItems();
    });
  }
};

/** @typedef {import('../photoswipe.js').default} PhotoSwipe */

/** @typedef {import('./ui-element.js').UIElementData} UIElementData */

/**
 * Set special class on element when image is zoomed.
 *
 * By default, it is used to adjust
 * zoom icon and zoom cursor via CSS.
 *
 * @param {HTMLElement} el
 * @param {boolean} isZoomedIn
 */

function setZoomedIn(el, isZoomedIn) {
  el.classList.toggle('pswp--zoomed-in', isZoomedIn);
}

class UI {
  /**
   * @param {PhotoSwipe} pswp
   */
  constructor(pswp) {
    this.pswp = pswp;
    this.isRegistered = false;
    /** @type {UIElementData[]} */

    this.uiElementsData = [];
    /** @type {(UIElement | UIElementData)[]} */

    this.items = [];
    /** @type {() => void} */

    this.updatePreloaderVisibility = () => {};
    /**
     * @private
     * @type {number | undefined}
     */


    this._lastUpdatedZoomLevel = undefined;
  }

  init() {
    const {
      pswp
    } = this;
    this.isRegistered = false;
    this.uiElementsData = [closeButton, arrowPrev, arrowNext, zoomButton, loadingIndicator, counterIndicator];
    pswp.dispatch('uiRegister'); // sort by order

    this.uiElementsData.sort((a, b) => {
      // default order is 0
      return (a.order || 0) - (b.order || 0);
    });
    this.items = [];
    this.isRegistered = true;
    this.uiElementsData.forEach(uiElementData => {
      this.registerElement(uiElementData);
    });
    pswp.on('change', () => {
      var _pswp$element;

      (_pswp$element = pswp.element) === null || _pswp$element === void 0 || _pswp$element.classList.toggle('pswp--one-slide', pswp.getNumItems() === 1);
    });
    pswp.on('zoomPanUpdate', () => this._onZoomPanUpdate());
  }
  /**
   * @param {UIElementData} elementData
   */


  registerElement(elementData) {
    if (this.isRegistered) {
      this.items.push(new UIElement(this.pswp, elementData));
    } else {
      this.uiElementsData.push(elementData);
    }
  }
  /**
   * Fired each time zoom or pan position is changed.
   * Update classes that control visibility of zoom button and cursor icon.
   *
   * @private
   */


  _onZoomPanUpdate() {
    const {
      template,
      currSlide,
      options
    } = this.pswp;

    if (this.pswp.opener.isClosing || !template || !currSlide) {
      return;
    }

    let {
      currZoomLevel
    } = currSlide; // if not open yet - check against initial zoom level

    if (!this.pswp.opener.isOpen) {
      currZoomLevel = currSlide.zoomLevels.initial;
    }

    if (currZoomLevel === this._lastUpdatedZoomLevel) {
      return;
    }

    this._lastUpdatedZoomLevel = currZoomLevel;
    const currZoomLevelDiff = currSlide.zoomLevels.initial - currSlide.zoomLevels.secondary; // Initial and secondary zoom levels are almost equal

    if (Math.abs(currZoomLevelDiff) < 0.01 || !currSlide.isZoomable()) {
      // disable zoom
      setZoomedIn(template, false);
      template.classList.remove('pswp--zoom-allowed');
      return;
    }

    template.classList.add('pswp--zoom-allowed');
    const potentialZoomLevel = currZoomLevel === currSlide.zoomLevels.initial ? currSlide.zoomLevels.secondary : currSlide.zoomLevels.initial;
    setZoomedIn(template, potentialZoomLevel <= currZoomLevel);

    if (options.imageClickAction === 'zoom' || options.imageClickAction === 'zoom-or-close') {
      template.classList.add('pswp--click-to-zoom');
    }
  }

}

/** @typedef {import('./slide.js').SlideData} SlideData */

/** @typedef {import('../photoswipe.js').default} PhotoSwipe */

/** @typedef {{ x: number; y: number; w: number; innerRect?: { w: number; h: number; x: number; y: number } }} Bounds */

/**
 * @param {HTMLElement} el
 * @returns Bounds
 */
function getBoundsByElement(el) {
  const thumbAreaRect = el.getBoundingClientRect();
  return {
    x: thumbAreaRect.left,
    y: thumbAreaRect.top,
    w: thumbAreaRect.width
  };
}
/**
 * @param {HTMLElement} el
 * @param {number} imageWidth
 * @param {number} imageHeight
 * @returns Bounds
 */


function getCroppedBoundsByElement(el, imageWidth, imageHeight) {
  const thumbAreaRect = el.getBoundingClientRect(); // fill image into the area
  // (do they same as object-fit:cover does to retrieve coordinates)

  const hRatio = thumbAreaRect.width / imageWidth;
  const vRatio = thumbAreaRect.height / imageHeight;
  const fillZoomLevel = hRatio > vRatio ? hRatio : vRatio;
  const offsetX = (thumbAreaRect.width - imageWidth * fillZoomLevel) / 2;
  const offsetY = (thumbAreaRect.height - imageHeight * fillZoomLevel) / 2;
  /**
   * Coordinates of the image,
   * as if it was not cropped,
   * height is calculated automatically
   *
   * @type {Bounds}
   */

  const bounds = {
    x: thumbAreaRect.left + offsetX,
    y: thumbAreaRect.top + offsetY,
    w: imageWidth * fillZoomLevel
  }; // Coordinates of inner crop area
  // relative to the image

  bounds.innerRect = {
    w: thumbAreaRect.width,
    h: thumbAreaRect.height,
    x: offsetX,
    y: offsetY
  };
  return bounds;
}
/**
 * Get dimensions of thumbnail image
 * (click on which opens photoswipe or closes photoswipe to)
 *
 * @param {number} index
 * @param {SlideData} itemData
 * @param {PhotoSwipe} instance PhotoSwipe instance
 * @returns {Bounds | undefined}
 */


function getThumbBounds(index, itemData, instance) {
  // legacy event, before filters were introduced
  const event = instance.dispatch('thumbBounds', {
    index,
    itemData,
    instance
  }); // @ts-expect-error

  if (event.thumbBounds) {
    // @ts-expect-error
    return event.thumbBounds;
  }

  const {
    element
  } = itemData;
  /** @type {Bounds | undefined} */

  let thumbBounds;
  /** @type {HTMLElement | null | undefined} */

  let thumbnail;

  if (element && instance.options.thumbSelector !== false) {
    const thumbSelector = instance.options.thumbSelector || 'img';
    thumbnail = element.matches(thumbSelector) ? element :
    /** @type {HTMLElement | null} */
    element.querySelector(thumbSelector);
  }

  thumbnail = instance.applyFilters('thumbEl', thumbnail, itemData, index);

  if (thumbnail) {
    if (!itemData.thumbCropped) {
      thumbBounds = getBoundsByElement(thumbnail);
    } else {
      thumbBounds = getCroppedBoundsByElement(thumbnail, itemData.width || itemData.w || 0, itemData.height || itemData.h || 0);
    }
  }

  return instance.applyFilters('thumbBounds', thumbBounds, itemData, index);
}

/** @typedef {import('../lightbox/lightbox.js').default} PhotoSwipeLightbox */

/** @typedef {import('../photoswipe.js').default} PhotoSwipe */

/** @typedef {import('../photoswipe.js').PhotoSwipeOptions} PhotoSwipeOptions */

/** @typedef {import('../photoswipe.js').DataSource} DataSource */

/** @typedef {import('../ui/ui-element.js').UIElementData} UIElementData */

/** @typedef {import('../slide/content.js').default} ContentDefault */

/** @typedef {import('../slide/slide.js').default} Slide */

/** @typedef {import('../slide/slide.js').SlideData} SlideData */

/** @typedef {import('../slide/zoom-level.js').default} ZoomLevel */

/** @typedef {import('../slide/get-thumb-bounds.js').Bounds} Bounds */

/**
 * Allow adding an arbitrary props to the Content
 * https://photoswipe.com/custom-content/#using-webp-image-format
 * @typedef {ContentDefault & Record<string, any>} Content
 */

/** @typedef {{ x?: number; y?: number }} Point */

/**
 * @typedef {Object} PhotoSwipeEventsMap https://photoswipe.com/events/
 *
 *
 * https://photoswipe.com/adding-ui-elements/
 *
 * @prop {undefined} uiRegister
 * @prop {{ data: UIElementData }} uiElementCreate
 *
 *
 * https://photoswipe.com/events/#initialization-events
 *
 * @prop {undefined} beforeOpen
 * @prop {undefined} firstUpdate
 * @prop {undefined} initialLayout
 * @prop {undefined} change
 * @prop {undefined} afterInit
 * @prop {undefined} bindEvents
 *
 *
 * https://photoswipe.com/events/#opening-or-closing-transition-events
 *
 * @prop {undefined} openingAnimationStart
 * @prop {undefined} openingAnimationEnd
 * @prop {undefined} closingAnimationStart
 * @prop {undefined} closingAnimationEnd
 *
 *
 * https://photoswipe.com/events/#closing-events
 *
 * @prop {undefined} close
 * @prop {undefined} destroy
 *
 *
 * https://photoswipe.com/events/#pointer-and-gesture-events
 *
 * @prop {{ originalEvent: PointerEvent }} pointerDown
 * @prop {{ originalEvent: PointerEvent }} pointerMove
 * @prop {{ originalEvent: PointerEvent }} pointerUp
 * @prop {{ bgOpacity: number }} pinchClose can be default prevented
 * @prop {{ panY: number }} verticalDrag can be default prevented
 *
 *
 * https://photoswipe.com/events/#slide-content-events
 *
 * @prop {{ content: Content }} contentInit
 * @prop {{ content: Content; isLazy: boolean }} contentLoad can be default prevented
 * @prop {{ content: Content; isLazy: boolean }} contentLoadImage can be default prevented
 * @prop {{ content: Content; slide: Slide; isError?: boolean }} loadComplete
 * @prop {{ content: Content; slide: Slide }} loadError
 * @prop {{ content: Content; width: number; height: number }} contentResize can be default prevented
 * @prop {{ content: Content; width: number; height: number; slide: Slide }} imageSizeChange
 * @prop {{ content: Content }} contentLazyLoad can be default prevented
 * @prop {{ content: Content }} contentAppend can be default prevented
 * @prop {{ content: Content }} contentActivate can be default prevented
 * @prop {{ content: Content }} contentDeactivate can be default prevented
 * @prop {{ content: Content }} contentRemove can be default prevented
 * @prop {{ content: Content }} contentDestroy can be default prevented
 *
 *
 * undocumented
 *
 * @prop {{ point: Point; originalEvent: PointerEvent }} imageClickAction can be default prevented
 * @prop {{ point: Point; originalEvent: PointerEvent }} bgClickAction can be default prevented
 * @prop {{ point: Point; originalEvent: PointerEvent }} tapAction can be default prevented
 * @prop {{ point: Point; originalEvent: PointerEvent }} doubleTapAction can be default prevented
 *
 * @prop {{ originalEvent: KeyboardEvent }} keydown can be default prevented
 * @prop {{ x: number; dragging: boolean }} moveMainScroll
 * @prop {{ slide: Slide }} firstZoomPan
 * @prop {{ slide: Slide | undefined, data: SlideData, index: number }} gettingData
 * @prop {undefined} beforeResize
 * @prop {undefined} resize
 * @prop {undefined} viewportSize
 * @prop {undefined} updateScrollOffset
 * @prop {{ slide: Slide }} slideInit
 * @prop {{ slide: Slide }} afterSetContent
 * @prop {{ slide: Slide }} slideLoad
 * @prop {{ slide: Slide }} appendHeavy can be default prevented
 * @prop {{ slide: Slide }} appendHeavyContent
 * @prop {{ slide: Slide }} slideActivate
 * @prop {{ slide: Slide }} slideDeactivate
 * @prop {{ slide: Slide }} slideDestroy
 * @prop {{ destZoomLevel: number, centerPoint: Point | undefined, transitionDuration: number | false | undefined }} beforeZoomTo
 * @prop {{ slide: Slide }} zoomPanUpdate
 * @prop {{ slide: Slide }} initialZoomPan
 * @prop {{ slide: Slide }} calcSlideSize
 * @prop {undefined} resolutionChanged
 * @prop {{ originalEvent: WheelEvent }} wheel can be default prevented
 * @prop {{ content: Content }} contentAppendImage can be default prevented
 * @prop {{ index: number; itemData: SlideData }} lazyLoadSlide can be default prevented
 * @prop {undefined} lazyLoad
 * @prop {{ slide: Slide }} calcBounds
 * @prop {{ zoomLevels: ZoomLevel, slideData: SlideData }} zoomLevelsUpdate
 *
 *
 * legacy
 *
 * @prop {undefined} init
 * @prop {undefined} initialZoomIn
 * @prop {undefined} initialZoomOut
 * @prop {undefined} initialZoomInEnd
 * @prop {undefined} initialZoomOutEnd
 * @prop {{ dataSource: DataSource | undefined, numItems: number }} numItems
 * @prop {{ itemData: SlideData; index: number }} itemData
 * @prop {{ index: number, itemData: SlideData, instance: PhotoSwipe }} thumbBounds
 */

/**
 * @typedef {Object} PhotoSwipeFiltersMap https://photoswipe.com/filters/
 *
 * @prop {(numItems: number, dataSource: DataSource | undefined) => number} numItems
 * Modify the total amount of slides. Example on Data sources page.
 * https://photoswipe.com/filters/#numitems
 *
 * @prop {(itemData: SlideData, index: number) => SlideData} itemData
 * Modify slide item data. Example on Data sources page.
 * https://photoswipe.com/filters/#itemdata
 *
 * @prop {(itemData: SlideData, element: HTMLElement, linkEl: HTMLAnchorElement) => SlideData} domItemData
 * Modify item data when it's parsed from DOM element. Example on Data sources page.
 * https://photoswipe.com/filters/#domitemdata
 *
 * @prop {(clickedIndex: number, e: MouseEvent, instance: PhotoSwipeLightbox) => number} clickedIndex
 * Modify clicked gallery item index.
 * https://photoswipe.com/filters/#clickedindex
 *
 * @prop {(placeholderSrc: string | false, content: Content) => string | false} placeholderSrc
 * Modify placeholder image source.
 * https://photoswipe.com/filters/#placeholdersrc
 *
 * @prop {(isContentLoading: boolean, content: Content) => boolean} isContentLoading
 * Modify if the content is currently loading.
 * https://photoswipe.com/filters/#iscontentloading
 *
 * @prop {(isContentZoomable: boolean, content: Content) => boolean} isContentZoomable
 * Modify if the content can be zoomed.
 * https://photoswipe.com/filters/#iscontentzoomable
 *
 * @prop {(useContentPlaceholder: boolean, content: Content) => boolean} useContentPlaceholder
 * Modify if the placeholder should be used for the content.
 * https://photoswipe.com/filters/#usecontentplaceholder
 *
 * @prop {(isKeepingPlaceholder: boolean, content: Content) => boolean} isKeepingPlaceholder
 * Modify if the placeholder should be kept after the content is loaded.
 * https://photoswipe.com/filters/#iskeepingplaceholder
 *
 *
 * @prop {(contentErrorElement: HTMLElement, content: Content) => HTMLElement} contentErrorElement
 * Modify an element when the content has error state (for example, if image cannot be loaded).
 * https://photoswipe.com/filters/#contenterrorelement
 *
 * @prop {(element: HTMLElement, data: UIElementData) => HTMLElement} uiElement
 * Modify a UI element that's being created.
 * https://photoswipe.com/filters/#uielement
 *
 * @prop {(thumbnail: HTMLElement | null | undefined, itemData: SlideData, index: number) => HTMLElement} thumbEl
 * Modify the thumbnail element from which opening zoom animation starts or ends.
 * https://photoswipe.com/filters/#thumbel
 *
 * @prop {(thumbBounds: Bounds | undefined, itemData: SlideData, index: number) => Bounds} thumbBounds
 * Modify the thumbnail bounds from which opening zoom animation starts or ends.
 * https://photoswipe.com/filters/#thumbbounds
 *
 * @prop {(srcsetSizesWidth: number, content: Content) => number} srcsetSizesWidth
 *
 * @prop {(preventPointerEvent: boolean, event: PointerEvent, pointerType: string) => boolean} preventPointerEvent
 *
 */

/**
 * @template {keyof PhotoSwipeFiltersMap} T
 * @typedef {{ fn: PhotoSwipeFiltersMap[T], priority: number }} Filter
 */

/**
 * @template {keyof PhotoSwipeEventsMap} T
 * @typedef {PhotoSwipeEventsMap[T] extends undefined ? PhotoSwipeEvent<T> : PhotoSwipeEvent<T> & PhotoSwipeEventsMap[T]} AugmentedEvent
 */

/**
 * @template {keyof PhotoSwipeEventsMap} T
 * @typedef {(event: AugmentedEvent<T>) => void} EventCallback
 */

/**
 * Base PhotoSwipe event object
 *
 * @template {keyof PhotoSwipeEventsMap} T
 */
class PhotoSwipeEvent {
  /**
   * @param {T} type
   * @param {PhotoSwipeEventsMap[T]} [details]
   */
  constructor(type, details) {
    this.type = type;
    this.defaultPrevented = false;

    if (details) {
      Object.assign(this, details);
    }
  }

  preventDefault() {
    this.defaultPrevented = true;
  }

}
/**
 * PhotoSwipe base class that can listen and dispatch for events.
 * Shared by PhotoSwipe Core and PhotoSwipe Lightbox, extended by base.js
 */


class Eventable {
  constructor() {
    /**
     * @type {{ [T in keyof PhotoSwipeEventsMap]?: ((event: AugmentedEvent<T>) => void)[] }}
     */
    this._listeners = {};
    /**
     * @type {{ [T in keyof PhotoSwipeFiltersMap]?: Filter<T>[] }}
     */

    this._filters = {};
    /** @type {PhotoSwipe | undefined} */

    this.pswp = undefined;
    /** @type {PhotoSwipeOptions | undefined} */

    this.options = undefined;
  }
  /**
   * @template {keyof PhotoSwipeFiltersMap} T
   * @param {T} name
   * @param {PhotoSwipeFiltersMap[T]} fn
   * @param {number} priority
   */


  addFilter(name, fn, priority = 100) {
    var _this$_filters$name, _this$_filters$name2, _this$pswp;

    if (!this._filters[name]) {
      this._filters[name] = [];
    }

    (_this$_filters$name = this._filters[name]) === null || _this$_filters$name === void 0 || _this$_filters$name.push({
      fn,
      priority
    });
    (_this$_filters$name2 = this._filters[name]) === null || _this$_filters$name2 === void 0 || _this$_filters$name2.sort((f1, f2) => f1.priority - f2.priority);
    (_this$pswp = this.pswp) === null || _this$pswp === void 0 || _this$pswp.addFilter(name, fn, priority);
  }
  /**
   * @template {keyof PhotoSwipeFiltersMap} T
   * @param {T} name
   * @param {PhotoSwipeFiltersMap[T]} fn
   */


  removeFilter(name, fn) {
    if (this._filters[name]) {
      // @ts-expect-error
      this._filters[name] = this._filters[name].filter(filter => filter.fn !== fn);
    }

    if (this.pswp) {
      this.pswp.removeFilter(name, fn);
    }
  }
  /**
   * @template {keyof PhotoSwipeFiltersMap} T
   * @param {T} name
   * @param {Parameters<PhotoSwipeFiltersMap[T]>} args
   * @returns {Parameters<PhotoSwipeFiltersMap[T]>[0]}
   */


  applyFilters(name, ...args) {
    var _this$_filters$name3;

    (_this$_filters$name3 = this._filters[name]) === null || _this$_filters$name3 === void 0 || _this$_filters$name3.forEach(filter => {
      // @ts-expect-error
      args[0] = filter.fn.apply(this, args);
    });
    return args[0];
  }
  /**
   * @template {keyof PhotoSwipeEventsMap} T
   * @param {T} name
   * @param {EventCallback<T>} fn
   */


  on(name, fn) {
    var _this$_listeners$name, _this$pswp2;

    if (!this._listeners[name]) {
      this._listeners[name] = [];
    }

    (_this$_listeners$name = this._listeners[name]) === null || _this$_listeners$name === void 0 || _this$_listeners$name.push(fn); // When binding events to lightbox,
    // also bind events to PhotoSwipe Core,
    // if it's open.

    (_this$pswp2 = this.pswp) === null || _this$pswp2 === void 0 || _this$pswp2.on(name, fn);
  }
  /**
   * @template {keyof PhotoSwipeEventsMap} T
   * @param {T} name
   * @param {EventCallback<T>} fn
   */


  off(name, fn) {
    var _this$pswp3;

    if (this._listeners[name]) {
      // @ts-expect-error
      this._listeners[name] = this._listeners[name].filter(listener => fn !== listener);
    }

    (_this$pswp3 = this.pswp) === null || _this$pswp3 === void 0 || _this$pswp3.off(name, fn);
  }
  /**
   * @template {keyof PhotoSwipeEventsMap} T
   * @param {T} name
   * @param {PhotoSwipeEventsMap[T]} [details]
   * @returns {AugmentedEvent<T>}
   */


  dispatch(name, details) {
    var _this$_listeners$name2;

    if (this.pswp) {
      return this.pswp.dispatch(name, details);
    }

    const event =
    /** @type {AugmentedEvent<T>} */
    new PhotoSwipeEvent(name, details);
    (_this$_listeners$name2 = this._listeners[name]) === null || _this$_listeners$name2 === void 0 || _this$_listeners$name2.forEach(listener => {
      listener.call(this, event);
    });
    return event;
  }

}

class Placeholder {
  /**
   * @param {string | false} imageSrc
   * @param {HTMLElement} container
   */
  constructor(imageSrc, container) {
    // Create placeholder
    // (stretched thumbnail or simple div behind the main image)

    /** @type {HTMLImageElement | HTMLDivElement | null} */
    this.element = createElement('pswp__img pswp__img--placeholder', imageSrc ? 'img' : 'div', container);

    if (imageSrc) {
      const imgEl =
      /** @type {HTMLImageElement} */
      this.element;
      imgEl.decoding = 'async';
      imgEl.alt = '';
      imgEl.src = imageSrc;
      imgEl.setAttribute('role', 'presentation');
    }

    this.element.setAttribute('aria-hidden', 'true');
  }
  /**
   * @param {number} width
   * @param {number} height
   */


  setDisplayedSize(width, height) {
    if (!this.element) {
      return;
    }

    if (this.element.tagName === 'IMG') {
      // Use transform scale() to modify img placeholder size
      // (instead of changing width/height directly).
      // This helps with performance, specifically in iOS15 Safari.
      setWidthHeight(this.element, 250, 'auto');
      this.element.style.transformOrigin = '0 0';
      this.element.style.transform = toTransformString(0, 0, width / 250);
    } else {
      setWidthHeight(this.element, width, height);
    }
  }

  destroy() {
    var _this$element;

    if ((_this$element = this.element) !== null && _this$element !== void 0 && _this$element.parentNode) {
      this.element.remove();
    }

    this.element = null;
  }

}

/** @typedef {import('./slide.js').default} Slide */

/** @typedef {import('./slide.js').SlideData} SlideData */

/** @typedef {import('../core/base.js').default} PhotoSwipeBase */

/** @typedef {import('../util/util.js').LoadState} LoadState */

class Content {
  /**
   * @param {SlideData} itemData Slide data
   * @param {PhotoSwipeBase} instance PhotoSwipe or PhotoSwipeLightbox instance
   * @param {number} index
   */
  constructor(itemData, instance, index) {
    this.instance = instance;
    this.data = itemData;
    this.index = index;
    /** @type {HTMLImageElement | HTMLDivElement | undefined} */

    this.element = undefined;
    /** @type {Placeholder | undefined} */

    this.placeholder = undefined;
    /** @type {Slide | undefined} */

    this.slide = undefined;
    this.displayedImageWidth = 0;
    this.displayedImageHeight = 0;
    this.width = Number(this.data.w) || Number(this.data.width) || 0;
    this.height = Number(this.data.h) || Number(this.data.height) || 0;
    this.isAttached = false;
    this.hasSlide = false;
    this.isDecoding = false;
    /** @type {LoadState} */

    this.state = LOAD_STATE.IDLE;

    if (this.data.type) {
      this.type = this.data.type;
    } else if (this.data.src) {
      this.type = 'image';
    } else {
      this.type = 'html';
    }

    this.instance.dispatch('contentInit', {
      content: this
    });
  }

  removePlaceholder() {
    if (this.placeholder && !this.keepPlaceholder()) {
      // With delay, as image might be loaded, but not rendered
      setTimeout(() => {
        if (this.placeholder) {
          this.placeholder.destroy();
          this.placeholder = undefined;
        }
      }, 1000);
    }
  }
  /**
   * Preload content
   *
   * @param {boolean} isLazy
   * @param {boolean} [reload]
   */


  load(isLazy, reload) {
    if (this.slide && this.usePlaceholder()) {
      if (!this.placeholder) {
        const placeholderSrc = this.instance.applyFilters('placeholderSrc', // use  image-based placeholder only for the first slide,
        // as rendering (even small stretched thumbnail) is an expensive operation
        this.data.msrc && this.slide.isFirstSlide ? this.data.msrc : false, this);
        this.placeholder = new Placeholder(placeholderSrc, this.slide.container);
      } else {
        const placeholderEl = this.placeholder.element; // Add placeholder to DOM if it was already created

        if (placeholderEl && !placeholderEl.parentElement) {
          this.slide.container.prepend(placeholderEl);
        }
      }
    }

    if (this.element && !reload) {
      return;
    }

    if (this.instance.dispatch('contentLoad', {
      content: this,
      isLazy
    }).defaultPrevented) {
      return;
    }

    if (this.isImageContent()) {
      this.element = createElement('pswp__img', 'img'); // Start loading only after width is defined, as sizes might depend on it.
      // Due to Safari feature, we must define sizes before srcset.

      if (this.displayedImageWidth) {
        this.loadImage(isLazy);
      }
    } else {
      this.element = createElement('pswp__content', 'div');
      this.element.innerHTML = this.data.html || '';
    }

    if (reload && this.slide) {
      this.slide.updateContentSize(true);
    }
  }
  /**
   * Preload image
   *
   * @param {boolean} isLazy
   */


  loadImage(isLazy) {
    var _this$data$src, _this$data$alt;

    if (!this.isImageContent() || !this.element || this.instance.dispatch('contentLoadImage', {
      content: this,
      isLazy
    }).defaultPrevented) {
      return;
    }

    const imageElement =
    /** @type HTMLImageElement */
    this.element;
    this.updateSrcsetSizes();

    if (this.data.srcset) {
      imageElement.srcset = this.data.srcset;
    }

    imageElement.src = (_this$data$src = this.data.src) !== null && _this$data$src !== void 0 ? _this$data$src : '';
    imageElement.alt = (_this$data$alt = this.data.alt) !== null && _this$data$alt !== void 0 ? _this$data$alt : '';
    this.state = LOAD_STATE.LOADING;

    if (imageElement.complete) {
      this.onLoaded();
    } else {
      imageElement.onload = () => {
        this.onLoaded();
      };

      imageElement.onerror = () => {
        this.onError();
      };
    }
  }
  /**
   * Assign slide to content
   *
   * @param {Slide} slide
   */


  setSlide(slide) {
    this.slide = slide;
    this.hasSlide = true;
    this.instance = slide.pswp; // todo: do we need to unset slide?
  }
  /**
   * Content load success handler
   */


  onLoaded() {
    this.state = LOAD_STATE.LOADED;

    if (this.slide && this.element) {
      this.instance.dispatch('loadComplete', {
        slide: this.slide,
        content: this
      }); // if content is reloaded

      if (this.slide.isActive && this.slide.heavyAppended && !this.element.parentNode) {
        this.append();
        this.slide.updateContentSize(true);
      }

      if (this.state === LOAD_STATE.LOADED || this.state === LOAD_STATE.ERROR) {
        this.removePlaceholder();
      }
    }
  }
  /**
   * Content load error handler
   */


  onError() {
    this.state = LOAD_STATE.ERROR;

    if (this.slide) {
      this.displayError();
      this.instance.dispatch('loadComplete', {
        slide: this.slide,
        isError: true,
        content: this
      });
      this.instance.dispatch('loadError', {
        slide: this.slide,
        content: this
      });
    }
  }
  /**
   * @returns {Boolean} If the content is currently loading
   */


  isLoading() {
    return this.instance.applyFilters('isContentLoading', this.state === LOAD_STATE.LOADING, this);
  }
  /**
   * @returns {Boolean} If the content is in error state
   */


  isError() {
    return this.state === LOAD_STATE.ERROR;
  }
  /**
   * @returns {boolean} If the content is image
   */


  isImageContent() {
    return this.type === 'image';
  }
  /**
   * Update content size
   *
   * @param {Number} width
   * @param {Number} height
   */


  setDisplayedSize(width, height) {
    if (!this.element) {
      return;
    }

    if (this.placeholder) {
      this.placeholder.setDisplayedSize(width, height);
    }

    if (this.instance.dispatch('contentResize', {
      content: this,
      width,
      height
    }).defaultPrevented) {
      return;
    }

    setWidthHeight(this.element, width, height);

    if (this.isImageContent() && !this.isError()) {
      const isInitialSizeUpdate = !this.displayedImageWidth && width;
      this.displayedImageWidth = width;
      this.displayedImageHeight = height;

      if (isInitialSizeUpdate) {
        this.loadImage(false);
      } else {
        this.updateSrcsetSizes();
      }

      if (this.slide) {
        this.instance.dispatch('imageSizeChange', {
          slide: this.slide,
          width,
          height,
          content: this
        });
      }
    }
  }
  /**
   * @returns {boolean} If the content can be zoomed
   */


  isZoomable() {
    return this.instance.applyFilters('isContentZoomable', this.isImageContent() && this.state !== LOAD_STATE.ERROR, this);
  }
  /**
   * Update image srcset sizes attribute based on width and height
   */


  updateSrcsetSizes() {
    // Handle srcset sizes attribute.
    //
    // Never lower quality, if it was increased previously.
    // Chrome does this automatically, Firefox and Safari do not,
    // so we store largest used size in dataset.
    if (!this.isImageContent() || !this.element || !this.data.srcset) {
      return;
    }

    const image =
    /** @type HTMLImageElement */
    this.element;
    const sizesWidth = this.instance.applyFilters('srcsetSizesWidth', this.displayedImageWidth, this);

    if (!image.dataset.largestUsedSize || sizesWidth > parseInt(image.dataset.largestUsedSize, 10)) {
      image.sizes = sizesWidth + 'px';
      image.dataset.largestUsedSize = String(sizesWidth);
    }
  }
  /**
   * @returns {boolean} If content should use a placeholder (from msrc by default)
   */


  usePlaceholder() {
    return this.instance.applyFilters('useContentPlaceholder', this.isImageContent(), this);
  }
  /**
   * Preload content with lazy-loading param
   */


  lazyLoad() {
    if (this.instance.dispatch('contentLazyLoad', {
      content: this
    }).defaultPrevented) {
      return;
    }

    this.load(true);
  }
  /**
   * @returns {boolean} If placeholder should be kept after content is loaded
   */


  keepPlaceholder() {
    return this.instance.applyFilters('isKeepingPlaceholder', this.isLoading(), this);
  }
  /**
   * Destroy the content
   */


  destroy() {
    this.hasSlide = false;
    this.slide = undefined;

    if (this.instance.dispatch('contentDestroy', {
      content: this
    }).defaultPrevented) {
      return;
    }

    this.remove();

    if (this.placeholder) {
      this.placeholder.destroy();
      this.placeholder = undefined;
    }

    if (this.isImageContent() && this.element) {
      this.element.onload = null;
      this.element.onerror = null;
      this.element = undefined;
    }
  }
  /**
   * Display error message
   */


  displayError() {
    if (this.slide) {
      var _this$instance$option, _this$instance$option2;

      let errorMsgEl = createElement('pswp__error-msg', 'div');
      errorMsgEl.innerText = (_this$instance$option = (_this$instance$option2 = this.instance.options) === null || _this$instance$option2 === void 0 ? void 0 : _this$instance$option2.errorMsg) !== null && _this$instance$option !== void 0 ? _this$instance$option : '';
      errorMsgEl =
      /** @type {HTMLDivElement} */
      this.instance.applyFilters('contentErrorElement', errorMsgEl, this);
      this.element = createElement('pswp__content pswp__error-msg-container', 'div');
      this.element.appendChild(errorMsgEl);
      this.slide.container.innerText = '';
      this.slide.container.appendChild(this.element);
      this.slide.updateContentSize(true);
      this.removePlaceholder();
    }
  }
  /**
   * Append the content
   */


  append() {
    if (this.isAttached || !this.element) {
      return;
    }

    this.isAttached = true;

    if (this.state === LOAD_STATE.ERROR) {
      this.displayError();
      return;
    }

    if (this.instance.dispatch('contentAppend', {
      content: this
    }).defaultPrevented) {
      return;
    }

    const supportsDecode = ('decode' in this.element);

    if (this.isImageContent()) {
      // Use decode() on nearby slides
      //
      // Nearby slide images are in DOM and not hidden via display:none.
      // However, they are placed offscreen (to the left and right side).
      //
      // Some browsers do not composite the image until it's actually visible,
      // using decode() helps.
      //
      // You might ask "why dont you just decode() and then append all images",
      // that's because I want to show image before it's fully loaded,
      // as browser can render parts of image while it is loading.
      // We do not do this in Safari due to partial loading bug.
      if (supportsDecode && this.slide && (!this.slide.isActive || isSafari())) {
        this.isDecoding = true; // purposefully using finally instead of then,
        // as if srcset sizes changes dynamically - it may cause decode error

        /** @type {HTMLImageElement} */

        this.element.decode().catch(() => {}).finally(() => {
          this.isDecoding = false;
          this.appendImage();
        });
      } else {
        this.appendImage();
      }
    } else if (this.slide && !this.element.parentNode) {
      this.slide.container.appendChild(this.element);
    }
  }
  /**
   * Activate the slide,
   * active slide is generally the current one,
   * meaning the user can see it.
   */


  activate() {
    if (this.instance.dispatch('contentActivate', {
      content: this
    }).defaultPrevented || !this.slide) {
      return;
    }

    if (this.isImageContent() && this.isDecoding && !isSafari()) {
      // add image to slide when it becomes active,
      // even if it's not finished decoding
      this.appendImage();
    } else if (this.isError()) {
      this.load(false, true); // try to reload
    }

    if (this.slide.holderElement) {
      this.slide.holderElement.setAttribute('aria-hidden', 'false');
    }
  }
  /**
   * Deactivate the content
   */


  deactivate() {
    this.instance.dispatch('contentDeactivate', {
      content: this
    });

    if (this.slide && this.slide.holderElement) {
      this.slide.holderElement.setAttribute('aria-hidden', 'true');
    }
  }
  /**
   * Remove the content from DOM
   */


  remove() {
    this.isAttached = false;

    if (this.instance.dispatch('contentRemove', {
      content: this
    }).defaultPrevented) {
      return;
    }

    if (this.element && this.element.parentNode) {
      this.element.remove();
    }

    if (this.placeholder && this.placeholder.element) {
      this.placeholder.element.remove();
    }
  }
  /**
   * Append the image content to slide container
   */


  appendImage() {
    if (!this.isAttached) {
      return;
    }

    if (this.instance.dispatch('contentAppendImage', {
      content: this
    }).defaultPrevented) {
      return;
    } // ensure that element exists and is not already appended


    if (this.slide && this.element && !this.element.parentNode) {
      this.slide.container.appendChild(this.element);
    }

    if (this.state === LOAD_STATE.LOADED || this.state === LOAD_STATE.ERROR) {
      this.removePlaceholder();
    }
  }

}

/** @typedef {import('./content.js').default} Content */

/** @typedef {import('./slide.js').default} Slide */

/** @typedef {import('./slide.js').SlideData} SlideData */

/** @typedef {import('../core/base.js').default} PhotoSwipeBase */

/** @typedef {import('../photoswipe.js').default} PhotoSwipe */

const MIN_SLIDES_TO_CACHE = 5;
/**
 * Lazy-load an image
 * This function is used both by Lightbox and PhotoSwipe core,
 * thus it can be called before dialog is opened.
 *
 * @param {SlideData} itemData Data about the slide
 * @param {PhotoSwipeBase} instance PhotoSwipe or PhotoSwipeLightbox instance
 * @param {number} index
 * @returns {Content} Image that is being decoded or false.
 */

function lazyLoadData(itemData, instance, index) {
  const content = instance.createContentFromData(itemData, index);
  /** @type {ZoomLevel | undefined} */

  let zoomLevel;
  const {
    options
  } = instance; // We need to know dimensions of the image to preload it,
  // as it might use srcset, and we need to define sizes

  if (options) {
    zoomLevel = new ZoomLevel(options, itemData, -1);
    let viewportSize;

    if (instance.pswp) {
      viewportSize = instance.pswp.viewportSize;
    } else {
      viewportSize = getViewportSize(options, instance);
    }

    const panAreaSize = getPanAreaSize(options, viewportSize, itemData, index);
    zoomLevel.update(content.width, content.height, panAreaSize);
  }

  content.lazyLoad();

  if (zoomLevel) {
    content.setDisplayedSize(Math.ceil(content.width * zoomLevel.initial), Math.ceil(content.height * zoomLevel.initial));
  }

  return content;
}
/**
 * Lazy-loads specific slide.
 * This function is used both by Lightbox and PhotoSwipe core,
 * thus it can be called before dialog is opened.
 *
 * By default, it loads image based on viewport size and initial zoom level.
 *
 * @param {number} index Slide index
 * @param {PhotoSwipeBase} instance PhotoSwipe or PhotoSwipeLightbox eventable instance
 * @returns {Content | undefined}
 */

function lazyLoadSlide(index, instance) {
  const itemData = instance.getItemData(index);

  if (instance.dispatch('lazyLoadSlide', {
    index,
    itemData
  }).defaultPrevented) {
    return;
  }

  return lazyLoadData(itemData, instance, index);
}

class ContentLoader {
  /**
   * @param {PhotoSwipe} pswp
   */
  constructor(pswp) {
    this.pswp = pswp; // Total amount of cached images

    this.limit = Math.max(pswp.options.preload[0] + pswp.options.preload[1] + 1, MIN_SLIDES_TO_CACHE);
    /** @type {Content[]} */

    this._cachedItems = [];
  }
  /**
   * Lazy load nearby slides based on `preload` option.
   *
   * @param {number} [diff] Difference between slide indexes that was changed recently, or 0.
   */


  updateLazy(diff) {
    const {
      pswp
    } = this;

    if (pswp.dispatch('lazyLoad').defaultPrevented) {
      return;
    }

    const {
      preload
    } = pswp.options;
    const isForward = diff === undefined ? true : diff >= 0;
    let i; // preload[1] - num items to preload in forward direction

    for (i = 0; i <= preload[1]; i++) {
      this.loadSlideByIndex(pswp.currIndex + (isForward ? i : -i));
    } // preload[0] - num items to preload in backward direction


    for (i = 1; i <= preload[0]; i++) {
      this.loadSlideByIndex(pswp.currIndex + (isForward ? -i : i));
    }
  }
  /**
   * @param {number} initialIndex
   */


  loadSlideByIndex(initialIndex) {
    const index = this.pswp.getLoopedIndex(initialIndex); // try to get cached content

    let content = this.getContentByIndex(index);

    if (!content) {
      // no cached content, so try to load from scratch:
      content = lazyLoadSlide(index, this.pswp); // if content can be loaded, add it to cache:

      if (content) {
        this.addToCache(content);
      }
    }
  }
  /**
   * @param {Slide} slide
   * @returns {Content}
   */


  getContentBySlide(slide) {
    let content = this.getContentByIndex(slide.index);

    if (!content) {
      // create content if not found in cache
      content = this.pswp.createContentFromData(slide.data, slide.index);
      this.addToCache(content);
    } // assign slide to content


    content.setSlide(slide);
    return content;
  }
  /**
   * @param {Content} content
   */


  addToCache(content) {
    // move to the end of array
    this.removeByIndex(content.index);

    this._cachedItems.push(content);

    if (this._cachedItems.length > this.limit) {
      // Destroy the first content that's not attached
      const indexToRemove = this._cachedItems.findIndex(item => {
        return !item.isAttached && !item.hasSlide;
      });

      if (indexToRemove !== -1) {
        const removedItem = this._cachedItems.splice(indexToRemove, 1)[0];

        removedItem.destroy();
      }
    }
  }
  /**
   * Removes an image from cache, does not destroy() it, just removes.
   *
   * @param {number} index
   */


  removeByIndex(index) {
    const indexToRemove = this._cachedItems.findIndex(item => item.index === index);

    if (indexToRemove !== -1) {
      this._cachedItems.splice(indexToRemove, 1);
    }
  }
  /**
   * @param {number} index
   * @returns {Content | undefined}
   */


  getContentByIndex(index) {
    return this._cachedItems.find(content => content.index === index);
  }

  destroy() {
    this._cachedItems.forEach(content => content.destroy());

    this._cachedItems = [];
  }

}

/** @typedef {import("../photoswipe.js").default} PhotoSwipe */

/** @typedef {import("../slide/slide.js").SlideData} SlideData */

/**
 * PhotoSwipe base class that can retrieve data about every slide.
 * Shared by PhotoSwipe Core and PhotoSwipe Lightbox
 */

class PhotoSwipeBase extends Eventable {
  /**
   * Get total number of slides
   *
   * @returns {number}
   */
  getNumItems() {
    var _this$options;

    let numItems = 0;
    const dataSource = (_this$options = this.options) === null || _this$options === void 0 ? void 0 : _this$options.dataSource;

    if (dataSource && 'length' in dataSource) {
      // may be an array or just object with length property
      numItems = dataSource.length;
    } else if (dataSource && 'gallery' in dataSource) {
      // query DOM elements
      if (!dataSource.items) {
        dataSource.items = this._getGalleryDOMElements(dataSource.gallery);
      }

      if (dataSource.items) {
        numItems = dataSource.items.length;
      }
    } // legacy event, before filters were introduced


    const event = this.dispatch('numItems', {
      dataSource,
      numItems
    });
    return this.applyFilters('numItems', event.numItems, dataSource);
  }
  /**
   * @param {SlideData} slideData
   * @param {number} index
   * @returns {Content}
   */


  createContentFromData(slideData, index) {
    return new Content(slideData, this, index);
  }
  /**
   * Get item data by index.
   *
   * "item data" should contain normalized information that PhotoSwipe needs to generate a slide.
   * For example, it may contain properties like
   * `src`, `srcset`, `w`, `h`, which will be used to generate a slide with image.
   *
   * @param {number} index
   * @returns {SlideData}
   */


  getItemData(index) {
    var _this$options2;

    const dataSource = (_this$options2 = this.options) === null || _this$options2 === void 0 ? void 0 : _this$options2.dataSource;
    /** @type {SlideData | HTMLElement} */

    let dataSourceItem = {};

    if (Array.isArray(dataSource)) {
      // Datasource is an array of elements
      dataSourceItem = dataSource[index];
    } else if (dataSource && 'gallery' in dataSource) {
      // dataSource has gallery property,
      // thus it was created by Lightbox, based on
      // gallery and children options
      // query DOM elements
      if (!dataSource.items) {
        dataSource.items = this._getGalleryDOMElements(dataSource.gallery);
      }

      dataSourceItem = dataSource.items[index];
    }

    let itemData = dataSourceItem;

    if (itemData instanceof Element) {
      itemData = this._domElementToItemData(itemData);
    } // Dispatching the itemData event,
    // it's a legacy verion before filters were introduced


    const event = this.dispatch('itemData', {
      itemData: itemData || {},
      index
    });
    return this.applyFilters('itemData', event.itemData, index);
  }
  /**
   * Get array of gallery DOM elements,
   * based on childSelector and gallery element.
   *
   * @param {HTMLElement} galleryElement
   * @returns {HTMLElement[]}
   */


  _getGalleryDOMElements(galleryElement) {
    var _this$options3, _this$options4;

    if ((_this$options3 = this.options) !== null && _this$options3 !== void 0 && _this$options3.children || (_this$options4 = this.options) !== null && _this$options4 !== void 0 && _this$options4.childSelector) {
      return getElementsFromOption(this.options.children, this.options.childSelector, galleryElement) || [];
    }

    return [galleryElement];
  }
  /**
   * Converts DOM element to item data object.
   *
   * @param {HTMLElement} element DOM element
   * @returns {SlideData}
   */


  _domElementToItemData(element) {
    /** @type {SlideData} */
    const itemData = {
      element
    };
    const linkEl =
    /** @type {HTMLAnchorElement} */
    element.tagName === 'A' ? element : element.querySelector('a');

    if (linkEl) {
      // src comes from data-pswp-src attribute,
      // if it's empty link href is used
      itemData.src = linkEl.dataset.pswpSrc || linkEl.href;

      if (linkEl.dataset.pswpSrcset) {
        itemData.srcset = linkEl.dataset.pswpSrcset;
      }

      itemData.width = linkEl.dataset.pswpWidth ? parseInt(linkEl.dataset.pswpWidth, 10) : 0;
      itemData.height = linkEl.dataset.pswpHeight ? parseInt(linkEl.dataset.pswpHeight, 10) : 0; // support legacy w & h properties

      itemData.w = itemData.width;
      itemData.h = itemData.height;

      if (linkEl.dataset.pswpType) {
        itemData.type = linkEl.dataset.pswpType;
      }

      const thumbnailEl = element.querySelector('img');

      if (thumbnailEl) {
        var _thumbnailEl$getAttri;

        // msrc is URL to placeholder image that's displayed before large image is loaded
        // by default it's displayed only for the first slide
        itemData.msrc = thumbnailEl.currentSrc || thumbnailEl.src;
        itemData.alt = (_thumbnailEl$getAttri = thumbnailEl.getAttribute('alt')) !== null && _thumbnailEl$getAttri !== void 0 ? _thumbnailEl$getAttri : '';
      }

      if (linkEl.dataset.pswpCropped || linkEl.dataset.cropped) {
        itemData.thumbCropped = true;
      }
    }

    return this.applyFilters('domItemData', itemData, element, linkEl);
  }
  /**
   * Lazy-load by slide data
   *
   * @param {SlideData} itemData Data about the slide
   * @param {number} index
   * @returns {Content} Image that is being decoded or false.
   */


  lazyLoadData(itemData, index) {
    return lazyLoadData(itemData, this, index);
  }

}

/** @typedef {import('./photoswipe.js').default} PhotoSwipe */

/** @typedef {import('./slide/get-thumb-bounds.js').Bounds} Bounds */

/** @typedef {import('./util/animations.js').AnimationProps} AnimationProps */
// some browsers do not paint
// elements which opacity is set to 0,
// since we need to pre-render elements for the animation -
// we set it to the minimum amount

const MIN_OPACITY = 0.003;
/**
 * Manages opening and closing transitions of the PhotoSwipe.
 *
 * It can perform zoom, fade or no transition.
 */

class Opener {
  /**
   * @param {PhotoSwipe} pswp
   */
  constructor(pswp) {
    this.pswp = pswp;
    this.isClosed = true;
    this.isOpen = false;
    this.isClosing = false;
    this.isOpening = false;
    /**
     * @private
     * @type {number | false | undefined}
     */

    this._duration = undefined;
    /** @private */

    this._useAnimation = false;
    /** @private */

    this._croppedZoom = false;
    /** @private */

    this._animateRootOpacity = false;
    /** @private */

    this._animateBgOpacity = false;
    /**
     * @private
     * @type { HTMLDivElement | HTMLImageElement | null | undefined }
     */

    this._placeholder = undefined;
    /**
     * @private
     * @type { HTMLDivElement | undefined }
     */

    this._opacityElement = undefined;
    /**
     * @private
     * @type { HTMLDivElement | undefined }
     */

    this._cropContainer1 = undefined;
    /**
     * @private
     * @type { HTMLElement | null | undefined }
     */

    this._cropContainer2 = undefined;
    /**
     * @private
     * @type {Bounds | undefined}
     */

    this._thumbBounds = undefined;
    this._prepareOpen = this._prepareOpen.bind(this); // Override initial zoom and pan position

    pswp.on('firstZoomPan', this._prepareOpen);
  }

  open() {
    this._prepareOpen();

    this._start();
  }

  close() {
    if (this.isClosed || this.isClosing || this.isOpening) {
      // if we close during opening animation
      // for now do nothing,
      // browsers aren't good at changing the direction of the CSS transition
      return;
    }

    const slide = this.pswp.currSlide;
    this.isOpen = false;
    this.isOpening = false;
    this.isClosing = true;
    this._duration = this.pswp.options.hideAnimationDuration;

    if (slide && slide.currZoomLevel * slide.width >= this.pswp.options.maxWidthToAnimate) {
      this._duration = 0;
    }

    this._applyStartProps();

    setTimeout(() => {
      this._start();
    }, this._croppedZoom ? 30 : 0);
  }
  /** @private */


  _prepareOpen() {
    this.pswp.off('firstZoomPan', this._prepareOpen);

    if (!this.isOpening) {
      const slide = this.pswp.currSlide;
      this.isOpening = true;
      this.isClosing = false;
      this._duration = this.pswp.options.showAnimationDuration;

      if (slide && slide.zoomLevels.initial * slide.width >= this.pswp.options.maxWidthToAnimate) {
        this._duration = 0;
      }

      this._applyStartProps();
    }
  }
  /** @private */


  _applyStartProps() {
    const {
      pswp
    } = this;
    const slide = this.pswp.currSlide;
    const {
      options
    } = pswp;

    if (options.showHideAnimationType === 'fade') {
      options.showHideOpacity = true;
      this._thumbBounds = undefined;
    } else if (options.showHideAnimationType === 'none') {
      options.showHideOpacity = false;
      this._duration = 0;
      this._thumbBounds = undefined;
    } else if (this.isOpening && pswp._initialThumbBounds) {
      // Use initial bounds if defined
      this._thumbBounds = pswp._initialThumbBounds;
    } else {
      this._thumbBounds = this.pswp.getThumbBounds();
    }

    this._placeholder = slide === null || slide === void 0 ? void 0 : slide.getPlaceholderElement();
    pswp.animations.stopAll(); // Discard animations when duration is less than 50ms

    this._useAnimation = Boolean(this._duration && this._duration > 50);
    this._animateZoom = Boolean(this._thumbBounds) && (slide === null || slide === void 0 ? void 0 : slide.content.usePlaceholder()) && (!this.isClosing || !pswp.mainScroll.isShifted());

    if (!this._animateZoom) {
      this._animateRootOpacity = true;

      if (this.isOpening && slide) {
        slide.zoomAndPanToInitial();
        slide.applyCurrentZoomPan();
      }
    } else {
      var _options$showHideOpac;

      this._animateRootOpacity = (_options$showHideOpac = options.showHideOpacity) !== null && _options$showHideOpac !== void 0 ? _options$showHideOpac : false;
    }

    this._animateBgOpacity = !this._animateRootOpacity && this.pswp.options.bgOpacity > MIN_OPACITY;
    this._opacityElement = this._animateRootOpacity ? pswp.element : pswp.bg;

    if (!this._useAnimation) {
      this._duration = 0;
      this._animateZoom = false;
      this._animateBgOpacity = false;
      this._animateRootOpacity = true;

      if (this.isOpening) {
        if (pswp.element) {
          pswp.element.style.opacity = String(MIN_OPACITY);
        }

        pswp.applyBgOpacity(1);
      }

      return;
    }

    if (this._animateZoom && this._thumbBounds && this._thumbBounds.innerRect) {
      var _this$pswp$currSlide;

      // Properties are used when animation from cropped thumbnail
      this._croppedZoom = true;
      this._cropContainer1 = this.pswp.container;
      this._cropContainer2 = (_this$pswp$currSlide = this.pswp.currSlide) === null || _this$pswp$currSlide === void 0 ? void 0 : _this$pswp$currSlide.holderElement;

      if (pswp.container) {
        pswp.container.style.overflow = 'hidden';
        pswp.container.style.width = pswp.viewportSize.x + 'px';
      }
    } else {
      this._croppedZoom = false;
    }

    if (this.isOpening) {
      // Apply styles before opening transition
      if (this._animateRootOpacity) {
        if (pswp.element) {
          pswp.element.style.opacity = String(MIN_OPACITY);
        }

        pswp.applyBgOpacity(1);
      } else {
        if (this._animateBgOpacity && pswp.bg) {
          pswp.bg.style.opacity = String(MIN_OPACITY);
        }

        if (pswp.element) {
          pswp.element.style.opacity = '1';
        }
      }

      if (this._animateZoom) {
        this._setClosedStateZoomPan();

        if (this._placeholder) {
          // tell browser that we plan to animate the placeholder
          this._placeholder.style.willChange = 'transform'; // hide placeholder to allow hiding of
          // elements that overlap it (such as icons over the thumbnail)

          this._placeholder.style.opacity = String(MIN_OPACITY);
        }
      }
    } else if (this.isClosing) {
      // hide nearby slides to make sure that
      // they are not painted during the transition
      if (pswp.mainScroll.itemHolders[0]) {
        pswp.mainScroll.itemHolders[0].el.style.display = 'none';
      }

      if (pswp.mainScroll.itemHolders[2]) {
        pswp.mainScroll.itemHolders[2].el.style.display = 'none';
      }

      if (this._croppedZoom) {
        if (pswp.mainScroll.x !== 0) {
          // shift the main scroller to zero position
          pswp.mainScroll.resetPosition();
          pswp.mainScroll.resize();
        }
      }
    }
  }
  /** @private */


  _start() {
    if (this.isOpening && this._useAnimation && this._placeholder && this._placeholder.tagName === 'IMG') {
      // To ensure smooth animation
      // we wait till the current slide image placeholder is decoded,
      // but no longer than 250ms,
      // and no shorter than 50ms
      // (just using requestanimationframe is not enough in Firefox,
      // for some reason)
      new Promise(resolve => {
        let decoded = false;
        let isDelaying = true;
        decodeImage(
        /** @type {HTMLImageElement} */
        this._placeholder).finally(() => {
          decoded = true;

          if (!isDelaying) {
            resolve(true);
          }
        });
        setTimeout(() => {
          isDelaying = false;

          if (decoded) {
            resolve(true);
          }
        }, 50);
        setTimeout(resolve, 250);
      }).finally(() => this._initiate());
    } else {
      this._initiate();
    }
  }
  /** @private */


  _initiate() {
    var _this$pswp$element, _this$pswp$element2;

    (_this$pswp$element = this.pswp.element) === null || _this$pswp$element === void 0 || _this$pswp$element.style.setProperty('--pswp-transition-duration', this._duration + 'ms');
    this.pswp.dispatch(this.isOpening ? 'openingAnimationStart' : 'closingAnimationStart'); // legacy event

    this.pswp.dispatch(
    /** @type {'initialZoomIn' | 'initialZoomOut'} */
    'initialZoom' + (this.isOpening ? 'In' : 'Out'));
    (_this$pswp$element2 = this.pswp.element) === null || _this$pswp$element2 === void 0 || _this$pswp$element2.classList.toggle('pswp--ui-visible', this.isOpening);

    if (this.isOpening) {
      if (this._placeholder) {
        // unhide the placeholder
        this._placeholder.style.opacity = '1';
      }

      this._animateToOpenState();
    } else if (this.isClosing) {
      this._animateToClosedState();
    }

    if (!this._useAnimation) {
      this._onAnimationComplete();
    }
  }
  /** @private */


  _onAnimationComplete() {
    const {
      pswp
    } = this;
    this.isOpen = this.isOpening;
    this.isClosed = this.isClosing;
    this.isOpening = false;
    this.isClosing = false;
    pswp.dispatch(this.isOpen ? 'openingAnimationEnd' : 'closingAnimationEnd'); // legacy event

    pswp.dispatch(
    /** @type {'initialZoomInEnd' | 'initialZoomOutEnd'} */
    'initialZoom' + (this.isOpen ? 'InEnd' : 'OutEnd'));

    if (this.isClosed) {
      pswp.destroy();
    } else if (this.isOpen) {
      var _pswp$currSlide;

      if (this._animateZoom && pswp.container) {
        pswp.container.style.overflow = 'visible';
        pswp.container.style.width = '100%';
      }

      (_pswp$currSlide = pswp.currSlide) === null || _pswp$currSlide === void 0 || _pswp$currSlide.applyCurrentZoomPan();
    }
  }
  /** @private */


  _animateToOpenState() {
    const {
      pswp
    } = this;

    if (this._animateZoom) {
      if (this._croppedZoom && this._cropContainer1 && this._cropContainer2) {
        this._animateTo(this._cropContainer1, 'transform', 'translate3d(0,0,0)');

        this._animateTo(this._cropContainer2, 'transform', 'none');
      }

      if (pswp.currSlide) {
        pswp.currSlide.zoomAndPanToInitial();

        this._animateTo(pswp.currSlide.container, 'transform', pswp.currSlide.getCurrentTransform());
      }
    }

    if (this._animateBgOpacity && pswp.bg) {
      this._animateTo(pswp.bg, 'opacity', String(pswp.options.bgOpacity));
    }

    if (this._animateRootOpacity && pswp.element) {
      this._animateTo(pswp.element, 'opacity', '1');
    }
  }
  /** @private */


  _animateToClosedState() {
    const {
      pswp
    } = this;

    if (this._animateZoom) {
      this._setClosedStateZoomPan(true);
    } // do not animate opacity if it's already at 0


    if (this._animateBgOpacity && pswp.bgOpacity > 0.01 && pswp.bg) {
      this._animateTo(pswp.bg, 'opacity', '0');
    }

    if (this._animateRootOpacity && pswp.element) {
      this._animateTo(pswp.element, 'opacity', '0');
    }
  }
  /**
   * @private
   * @param {boolean} [animate]
   */


  _setClosedStateZoomPan(animate) {
    if (!this._thumbBounds) return;
    const {
      pswp
    } = this;
    const {
      innerRect
    } = this._thumbBounds;
    const {
      currSlide,
      viewportSize
    } = pswp;

    if (this._croppedZoom && innerRect && this._cropContainer1 && this._cropContainer2) {
      const containerOnePanX = -viewportSize.x + (this._thumbBounds.x - innerRect.x) + innerRect.w;
      const containerOnePanY = -viewportSize.y + (this._thumbBounds.y - innerRect.y) + innerRect.h;
      const containerTwoPanX = viewportSize.x - innerRect.w;
      const containerTwoPanY = viewportSize.y - innerRect.h;

      if (animate) {
        this._animateTo(this._cropContainer1, 'transform', toTransformString(containerOnePanX, containerOnePanY));

        this._animateTo(this._cropContainer2, 'transform', toTransformString(containerTwoPanX, containerTwoPanY));
      } else {
        setTransform(this._cropContainer1, containerOnePanX, containerOnePanY);
        setTransform(this._cropContainer2, containerTwoPanX, containerTwoPanY);
      }
    }

    if (currSlide) {
      equalizePoints(currSlide.pan, innerRect || this._thumbBounds);
      currSlide.currZoomLevel = this._thumbBounds.w / currSlide.width;

      if (animate) {
        this._animateTo(currSlide.container, 'transform', currSlide.getCurrentTransform());
      } else {
        currSlide.applyCurrentZoomPan();
      }
    }
  }
  /**
   * @private
   * @param {HTMLElement} target
   * @param {'transform' | 'opacity'} prop
   * @param {string} propValue
   */


  _animateTo(target, prop, propValue) {
    if (!this._duration) {
      target.style[prop] = propValue;
      return;
    }

    const {
      animations
    } = this.pswp;
    /** @type {AnimationProps} */

    const animProps = {
      duration: this._duration,
      easing: this.pswp.options.easing,
      onComplete: () => {
        if (!animations.activeAnimations.length) {
          this._onAnimationComplete();
        }
      },
      target
    };
    animProps[prop] = propValue;
    animations.startTransition(animProps);
  }

}

/**
 * @template T
 * @typedef {import('./types.js').Type<T>} Type<T>
 */

/** @typedef {import('./slide/slide.js').SlideData} SlideData */

/** @typedef {import('./slide/zoom-level.js').ZoomLevelOption} ZoomLevelOption */

/** @typedef {import('./ui/ui-element.js').UIElementData} UIElementData */

/** @typedef {import('./main-scroll.js').ItemHolder} ItemHolder */

/** @typedef {import('./core/eventable.js').PhotoSwipeEventsMap} PhotoSwipeEventsMap */

/** @typedef {import('./core/eventable.js').PhotoSwipeFiltersMap} PhotoSwipeFiltersMap */

/** @typedef {import('./slide/get-thumb-bounds').Bounds} Bounds */

/**
 * @template {keyof PhotoSwipeEventsMap} T
 * @typedef {import('./core/eventable.js').EventCallback<T>} EventCallback<T>
 */

/**
 * @template {keyof PhotoSwipeEventsMap} T
 * @typedef {import('./core/eventable.js').AugmentedEvent<T>} AugmentedEvent<T>
 */

/** @typedef {{ x: number; y: number; id?: string | number }} Point */

/** @typedef {{ top: number; bottom: number; left: number; right: number }} Padding */

/** @typedef {SlideData[]} DataSourceArray */

/** @typedef {{ gallery: HTMLElement; items?: HTMLElement[] }} DataSourceObject */

/** @typedef {DataSourceArray | DataSourceObject} DataSource */

/** @typedef {(point: Point, originalEvent: PointerEvent) => void} ActionFn */

/** @typedef {'close' | 'next' | 'zoom' | 'zoom-or-close' | 'toggle-controls'} ActionType */

/** @typedef {Type<PhotoSwipe> | { default: Type<PhotoSwipe> }} PhotoSwipeModule */

/** @typedef {PhotoSwipeModule | Promise<PhotoSwipeModule> | (() => Promise<PhotoSwipeModule>)} PhotoSwipeModuleOption */

/**
 * @typedef {string | NodeListOf<HTMLElement> | HTMLElement[] | HTMLElement} ElementProvider
 */

/** @typedef {Partial<PreparedPhotoSwipeOptions>} PhotoSwipeOptions https://photoswipe.com/options/ */

/**
 * @typedef {Object} PreparedPhotoSwipeOptions
 *
 * @prop {DataSource} [dataSource]
 * Pass an array of any items via dataSource option. Its length will determine amount of slides
 * (which may be modified further from numItems event).
 *
 * Each item should contain data that you need to generate slide
 * (for image slide it would be src (image URL), width (image width), height, srcset, alt).
 *
 * If these properties are not present in your initial array, you may "pre-parse" each item from itemData filter.
 *
 * @prop {number} bgOpacity
 * Background backdrop opacity, always define it via this option and not via CSS rgba color.
 *
 * @prop {number} spacing
 * Spacing between slides. Defined as ratio relative to the viewport width (0.1 = 10% of viewport).
 *
 * @prop {boolean} allowPanToNext
 * Allow swipe navigation to the next slide when the current slide is zoomed. Does not apply to mouse events.
 *
 * @prop {boolean} loop
 * If set to true you'll be able to swipe from the last to the first image.
 * Option is always false when there are less than 3 slides.
 *
 * @prop {boolean} [wheelToZoom]
 * By default PhotoSwipe zooms image with ctrl-wheel, if you enable this option - image will zoom just via wheel.
 *
 * @prop {boolean} pinchToClose
 * Pinch touch gesture to close the gallery.
 *
 * @prop {boolean} closeOnVerticalDrag
 * Vertical drag gesture to close the PhotoSwipe.
 *
 * @prop {Padding} [padding]
 * Slide area padding (in pixels).
 *
 * @prop {(viewportSize: Point, itemData: SlideData, index: number) => Padding} [paddingFn]
 * The option is checked frequently, so make sure it's performant. Overrides padding option if defined. For example:
 *
 * @prop {number | false} hideAnimationDuration
 * Transition duration in milliseconds, can be 0.
 *
 * @prop {number | false} showAnimationDuration
 * Transition duration in milliseconds, can be 0.
 *
 * @prop {number | false} zoomAnimationDuration
 * Transition duration in milliseconds, can be 0.
 *
 * @prop {string} easing
 * String, 'cubic-bezier(.4,0,.22,1)'. CSS easing function for open/close/zoom transitions.
 *
 * @prop {boolean} escKey
 * Esc key to close.
 *
 * @prop {boolean} arrowKeys
 * Left/right arrow keys for navigation.
 *
 * @prop {boolean} trapFocus
 * Trap focus within PhotoSwipe element while it's open.
 *
 * @prop {boolean} returnFocus
 * Restore focus the last active element after PhotoSwipe is closed.
 *
 * @prop {boolean} clickToCloseNonZoomable
 * If image is not zoomable (for example, smaller than viewport) it can be closed by clicking on it.
 *
 * @prop {ActionType | ActionFn | false} imageClickAction
 * Refer to click and tap actions page.
 *
 * @prop {ActionType | ActionFn | false} bgClickAction
 * Refer to click and tap actions page.
 *
 * @prop {ActionType | ActionFn | false} tapAction
 * Refer to click and tap actions page.
 *
 * @prop {ActionType | ActionFn | false} doubleTapAction
 * Refer to click and tap actions page.
 *
 * @prop {number} preloaderDelay
 * Delay before the loading indicator will be displayed,
 * if image is loaded during it - the indicator will not be displayed at all. Can be zero.
 *
 * @prop {string} indexIndicatorSep
 * Used for slide count indicator ("1 of 10 ").
 *
 * @prop {(options: PhotoSwipeOptions, pswp: PhotoSwipeBase) => Point} [getViewportSizeFn]
 * A function that should return slide viewport width and height, in format {x: 100, y: 100}.
 *
 * @prop {string} errorMsg
 * Message to display when the image wasn't able to load. If you need to display HTML - use contentErrorElement filter.
 *
 * @prop {[number, number]} preload
 * Lazy loading of nearby slides based on direction of movement. Should be an array with two integers,
 * first one - number of items to preload before the current image, second one - after the current image.
 * Two nearby images are always loaded.
 *
 * @prop {string} [mainClass]
 * Class that will be added to the root element of PhotoSwipe, may contain multiple separated by space.
 * Example on Styling page.
 *
 * @prop {HTMLElement} [appendToEl]
 * Element to which PhotoSwipe dialog will be appended when it opens.
 *
 * @prop {number} maxWidthToAnimate
 * Maximum width of image to animate, if initial rendered image width
 * is larger than this value - the opening/closing transition will be automatically disabled.
 *
 * @prop {string} [closeTitle]
 * Translating
 *
 * @prop {string} [zoomTitle]
 * Translating
 *
 * @prop {string} [arrowPrevTitle]
 * Translating
 *
 * @prop {string} [arrowNextTitle]
 * Translating
 *
 * @prop {'zoom' | 'fade' | 'none'} [showHideAnimationType]
 * To adjust opening or closing transition type use lightbox option `showHideAnimationType` (`String`).
 * It supports three values - `zoom` (default), `fade` (default if there is no thumbnail) and `none`.
 *
 * Animations are automatically disabled if user `(prefers-reduced-motion: reduce)`.
 *
 * @prop {number} index
 * Defines start slide index.
 *
 * @prop {(e: MouseEvent) => number} [getClickedIndexFn]
 *
 * @prop {boolean} [arrowPrev]
 * @prop {boolean} [arrowNext]
 * @prop {boolean} [zoom]
 * @prop {boolean} [close]
 * @prop {boolean} [counter]
 *
 * @prop {string} [arrowPrevSVG]
 * @prop {string} [arrowNextSVG]
 * @prop {string} [zoomSVG]
 * @prop {string} [closeSVG]
 * @prop {string} [counterSVG]
 *
 * @prop {string} [arrowPrevTitle]
 * @prop {string} [arrowNextTitle]
 * @prop {string} [zoomTitle]
 * @prop {string} [closeTitle]
 * @prop {string} [counterTitle]
 *
 * @prop {ZoomLevelOption} [initialZoomLevel]
 * @prop {ZoomLevelOption} [secondaryZoomLevel]
 * @prop {ZoomLevelOption} [maxZoomLevel]
 *
 * @prop {boolean} [mouseMovePan]
 * @prop {Point | null} [initialPointerPos]
 * @prop {boolean} [showHideOpacity]
 *
 * @prop {PhotoSwipeModuleOption} [pswpModule]
 * @prop {() => Promise<any>} [openPromise]
 * @prop {boolean} [preloadFirstSlide]
 * @prop {ElementProvider} [gallery]
 * @prop {string} [gallerySelector]
 * @prop {ElementProvider} [children]
 * @prop {string} [childSelector]
 * @prop {string | false} [thumbSelector]
 */

/** @type {PreparedPhotoSwipeOptions} */

const defaultOptions = {
  allowPanToNext: true,
  spacing: 0.1,
  loop: true,
  pinchToClose: true,
  closeOnVerticalDrag: true,
  hideAnimationDuration: 333,
  showAnimationDuration: 333,
  zoomAnimationDuration: 333,
  escKey: true,
  arrowKeys: true,
  trapFocus: true,
  returnFocus: true,
  maxWidthToAnimate: 4000,
  clickToCloseNonZoomable: true,
  imageClickAction: 'zoom-or-close',
  bgClickAction: 'close',
  tapAction: 'toggle-controls',
  doubleTapAction: 'zoom',
  indexIndicatorSep: ' / ',
  preloaderDelay: 2000,
  bgOpacity: 0.8,
  index: 0,
  errorMsg: 'The image cannot be loaded',
  preload: [1, 2],
  easing: 'cubic-bezier(.4,0,.22,1)'
};
/**
 * PhotoSwipe Core
 */

class PhotoSwipe extends PhotoSwipeBase {
  /**
   * @param {PhotoSwipeOptions} [options]
   */
  constructor(options) {
    super();
    this.options = this._prepareOptions(options || {});
    /**
     * offset of viewport relative to document
     *
     * @type {Point}
     */

    this.offset = {
      x: 0,
      y: 0
    };
    /**
     * @type {Point}
     * @private
     */

    this._prevViewportSize = {
      x: 0,
      y: 0
    };
    /**
     * Size of scrollable PhotoSwipe viewport
     *
     * @type {Point}
     */

    this.viewportSize = {
      x: 0,
      y: 0
    };
    /**
     * background (backdrop) opacity
     */

    this.bgOpacity = 1;
    this.currIndex = 0;
    this.potentialIndex = 0;
    this.isOpen = false;
    this.isDestroying = false;
    this.hasMouse = false;
    /**
     * @private
     * @type {SlideData}
     */

    this._initialItemData = {};
    /** @type {Bounds | undefined} */

    this._initialThumbBounds = undefined;
    /** @type {HTMLDivElement | undefined} */

    this.topBar = undefined;
    /** @type {HTMLDivElement | undefined} */

    this.element = undefined;
    /** @type {HTMLDivElement | undefined} */

    this.template = undefined;
    /** @type {HTMLDivElement | undefined} */

    this.container = undefined;
    /** @type {HTMLElement | undefined} */

    this.scrollWrap = undefined;
    /** @type {Slide | undefined} */

    this.currSlide = undefined;
    this.events = new DOMEvents();
    this.animations = new Animations();
    this.mainScroll = new MainScroll(this);
    this.gestures = new Gestures(this);
    this.opener = new Opener(this);
    this.keyboard = new Keyboard(this);
    this.contentLoader = new ContentLoader(this);
  }
  /** @returns {boolean} */


  init() {
    if (this.isOpen || this.isDestroying) {
      return false;
    }

    this.isOpen = true;
    this.dispatch('init'); // legacy

    this.dispatch('beforeOpen');

    this._createMainStructure(); // add classes to the root element of PhotoSwipe


    let rootClasses = 'pswp--open';

    if (this.gestures.supportsTouch) {
      rootClasses += ' pswp--touch';
    }

    if (this.options.mainClass) {
      rootClasses += ' ' + this.options.mainClass;
    }

    if (this.element) {
      this.element.className += ' ' + rootClasses;
    }

    this.currIndex = this.options.index || 0;
    this.potentialIndex = this.currIndex;
    this.dispatch('firstUpdate'); // starting index can be modified here
    // initialize scroll wheel handler to block the scroll

    this.scrollWheel = new ScrollWheel(this); // sanitize index

    if (Number.isNaN(this.currIndex) || this.currIndex < 0 || this.currIndex >= this.getNumItems()) {
      this.currIndex = 0;
    }

    if (!this.gestures.supportsTouch) {
      // enable mouse features if no touch support detected
      this.mouseDetected();
    } // causes forced synchronous layout


    this.updateSize();
    this.offset.y = window.pageYOffset;
    this._initialItemData = this.getItemData(this.currIndex);
    this.dispatch('gettingData', {
      index: this.currIndex,
      data: this._initialItemData,
      slide: undefined
    }); // *Layout* - calculate size and position of elements here

    this._initialThumbBounds = this.getThumbBounds();
    this.dispatch('initialLayout');
    this.on('openingAnimationEnd', () => {
      const {
        itemHolders
      } = this.mainScroll; // Add content to the previous and next slide

      if (itemHolders[0]) {
        itemHolders[0].el.style.display = 'block';
        this.setContent(itemHolders[0], this.currIndex - 1);
      }

      if (itemHolders[2]) {
        itemHolders[2].el.style.display = 'block';
        this.setContent(itemHolders[2], this.currIndex + 1);
      }

      this.appendHeavy();
      this.contentLoader.updateLazy();
      this.events.add(window, 'resize', this._handlePageResize.bind(this));
      this.events.add(window, 'scroll', this._updatePageScrollOffset.bind(this));
      this.dispatch('bindEvents');
    }); // set content for center slide (first time)

    if (this.mainScroll.itemHolders[1]) {
      this.setContent(this.mainScroll.itemHolders[1], this.currIndex);
    }

    this.dispatch('change');
    this.opener.open();
    this.dispatch('afterInit');
    return true;
  }
  /**
   * Get looped slide index
   * (for example, -1 will return the last slide)
   *
   * @param {number} index
   * @returns {number}
   */


  getLoopedIndex(index) {
    const numSlides = this.getNumItems();

    if (this.options.loop) {
      if (index > numSlides - 1) {
        index -= numSlides;
      }

      if (index < 0) {
        index += numSlides;
      }
    }

    return clamp(index, 0, numSlides - 1);
  }

  appendHeavy() {
    this.mainScroll.itemHolders.forEach(itemHolder => {
      var _itemHolder$slide;

      (_itemHolder$slide = itemHolder.slide) === null || _itemHolder$slide === void 0 || _itemHolder$slide.appendHeavy();
    });
  }
  /**
   * Change the slide
   * @param {number} index New index
   */


  goTo(index) {
    this.mainScroll.moveIndexBy(this.getLoopedIndex(index) - this.potentialIndex);
  }
  /**
   * Go to the next slide.
   */


  next() {
    this.goTo(this.potentialIndex + 1);
  }
  /**
   * Go to the previous slide.
   */


  prev() {
    this.goTo(this.potentialIndex - 1);
  }
  /**
   * @see slide/slide.js zoomTo
   *
   * @param {Parameters<Slide['zoomTo']>} args
   */


  zoomTo(...args) {
    var _this$currSlide;

    (_this$currSlide = this.currSlide) === null || _this$currSlide === void 0 || _this$currSlide.zoomTo(...args);
  }
  /**
   * @see slide/slide.js toggleZoom
   */


  toggleZoom() {
    var _this$currSlide2;

    (_this$currSlide2 = this.currSlide) === null || _this$currSlide2 === void 0 || _this$currSlide2.toggleZoom();
  }
  /**
   * Close the gallery.
   * After closing transition ends - destroy it
   */


  close() {
    if (!this.opener.isOpen || this.isDestroying) {
      return;
    }

    this.isDestroying = true;
    this.dispatch('close');
    this.events.removeAll();
    this.opener.close();
  }
  /**
   * Destroys the gallery:
   * - instantly closes the gallery
   * - unbinds events,
   * - cleans intervals and timeouts
   * - removes elements from DOM
   */


  destroy() {
    var _this$element;

    if (!this.isDestroying) {
      this.options.showHideAnimationType = 'none';
      this.close();
      return;
    }

    this.dispatch('destroy');
    this._listeners = {};

    if (this.scrollWrap) {
      this.scrollWrap.ontouchmove = null;
      this.scrollWrap.ontouchend = null;
    }

    (_this$element = this.element) === null || _this$element === void 0 || _this$element.remove();
    this.mainScroll.itemHolders.forEach(itemHolder => {
      var _itemHolder$slide2;

      (_itemHolder$slide2 = itemHolder.slide) === null || _itemHolder$slide2 === void 0 || _itemHolder$slide2.destroy();
    });
    this.contentLoader.destroy();
    this.events.removeAll();
  }
  /**
   * Refresh/reload content of a slide by its index
   *
   * @param {number} slideIndex
   */


  refreshSlideContent(slideIndex) {
    this.contentLoader.removeByIndex(slideIndex);
    this.mainScroll.itemHolders.forEach((itemHolder, i) => {
      var _this$currSlide$index, _this$currSlide3;

      let potentialHolderIndex = ((_this$currSlide$index = (_this$currSlide3 = this.currSlide) === null || _this$currSlide3 === void 0 ? void 0 : _this$currSlide3.index) !== null && _this$currSlide$index !== void 0 ? _this$currSlide$index : 0) - 1 + i;

      if (this.canLoop()) {
        potentialHolderIndex = this.getLoopedIndex(potentialHolderIndex);
      }

      if (potentialHolderIndex === slideIndex) {
        // set the new slide content
        this.setContent(itemHolder, slideIndex, true); // activate the new slide if it's current

        if (i === 1) {
          var _itemHolder$slide3;

          this.currSlide = itemHolder.slide;
          (_itemHolder$slide3 = itemHolder.slide) === null || _itemHolder$slide3 === void 0 || _itemHolder$slide3.setIsActive(true);
        }
      }
    });
    this.dispatch('change');
  }
  /**
   * Set slide content
   *
   * @param {ItemHolder} holder mainScroll.itemHolders array item
   * @param {number} index Slide index
   * @param {boolean} [force] If content should be set even if index wasn't changed
   */


  setContent(holder, index, force) {
    if (this.canLoop()) {
      index = this.getLoopedIndex(index);
    }

    if (holder.slide) {
      if (holder.slide.index === index && !force) {
        // exit if holder already contains this slide
        // this could be common when just three slides are used
        return;
      } // destroy previous slide


      holder.slide.destroy();
      holder.slide = undefined;
    } // exit if no loop and index is out of bounds


    if (!this.canLoop() && (index < 0 || index >= this.getNumItems())) {
      return;
    }

    const itemData = this.getItemData(index);
    holder.slide = new Slide(itemData, index, this); // set current slide

    if (index === this.currIndex) {
      this.currSlide = holder.slide;
    }

    holder.slide.append(holder.el);
  }
  /** @returns {Point} */


  getViewportCenterPoint() {
    return {
      x: this.viewportSize.x / 2,
      y: this.viewportSize.y / 2
    };
  }
  /**
   * Update size of all elements.
   * Executed on init and on page resize.
   *
   * @param {boolean} [force] Update size even if size of viewport was not changed.
   */


  updateSize(force) {
    // let item;
    // let itemIndex;
    if (this.isDestroying) {
      // exit if PhotoSwipe is closed or closing
      // (to avoid errors, as resize event might be delayed)
      return;
    } //const newWidth = this.scrollWrap.clientWidth;
    //const newHeight = this.scrollWrap.clientHeight;


    const newViewportSize = getViewportSize(this.options, this);

    if (!force && pointsEqual(newViewportSize, this._prevViewportSize)) {
      // Exit if dimensions were not changed
      return;
    } //this._prevViewportSize.x = newWidth;
    //this._prevViewportSize.y = newHeight;


    equalizePoints(this._prevViewportSize, newViewportSize);
    this.dispatch('beforeResize');
    equalizePoints(this.viewportSize, this._prevViewportSize);

    this._updatePageScrollOffset();

    this.dispatch('viewportSize'); // Resize slides only after opener animation is finished
    // and don't re-calculate size on inital size update

    this.mainScroll.resize(this.opener.isOpen);

    if (!this.hasMouse && window.matchMedia('(any-hover: hover)').matches) {
      this.mouseDetected();
    }

    this.dispatch('resize');
  }
  /**
   * @param {number} opacity
   */


  applyBgOpacity(opacity) {
    this.bgOpacity = Math.max(opacity, 0);

    if (this.bg) {
      this.bg.style.opacity = String(this.bgOpacity * this.options.bgOpacity);
    }
  }
  /**
   * Whether mouse is detected
   */


  mouseDetected() {
    if (!this.hasMouse) {
      var _this$element2;

      this.hasMouse = true;
      (_this$element2 = this.element) === null || _this$element2 === void 0 || _this$element2.classList.add('pswp--has_mouse');
    }
  }
  /**
   * Page resize event handler
   *
   * @private
   */


  _handlePageResize() {
    this.updateSize(); // In iOS webview, if element size depends on document size,
    // it'll be measured incorrectly in resize event
    //
    // https://bugs.webkit.org/show_bug.cgi?id=170595
    // https://hackernoon.com/onresize-event-broken-in-mobile-safari-d8469027bf4d

    if (/iPhone|iPad|iPod/i.test(window.navigator.userAgent)) {
      setTimeout(() => {
        this.updateSize();
      }, 500);
    }
  }
  /**
   * Page scroll offset is used
   * to get correct coordinates
   * relative to PhotoSwipe viewport.
   *
   * @private
   */


  _updatePageScrollOffset() {
    this.setScrollOffset(0, window.pageYOffset);
  }
  /**
   * @param {number} x
   * @param {number} y
   */


  setScrollOffset(x, y) {
    this.offset.x = x;
    this.offset.y = y;
    this.dispatch('updateScrollOffset');
  }
  /**
   * Create main HTML structure of PhotoSwipe,
   * and add it to DOM
   *
   * @private
   */


  _createMainStructure() {
    // root DOM element of PhotoSwipe (.pswp)
    this.element = createElement('pswp', 'div');
    this.element.setAttribute('tabindex', '-1');
    this.element.setAttribute('role', 'dialog'); // template is legacy prop

    this.template = this.element; // Background is added as a separate element,
    // as animating opacity is faster than animating rgba()

    this.bg = createElement('pswp__bg', 'div', this.element);
    this.scrollWrap = createElement('pswp__scroll-wrap', 'section', this.element);
    this.container = createElement('pswp__container', 'div', this.scrollWrap); // aria pattern: carousel

    this.scrollWrap.setAttribute('aria-roledescription', 'carousel');
    this.container.setAttribute('aria-live', 'off');
    this.container.setAttribute('id', 'pswp__items');
    this.mainScroll.appendHolders();
    this.ui = new UI(this);
    this.ui.init(); // append to DOM

    (this.options.appendToEl || document.body).appendChild(this.element);
  }
  /**
   * Get position and dimensions of small thumbnail
   *   {x:,y:,w:}
   *
   * Height is optional (calculated based on the large image)
   *
   * @returns {Bounds | undefined}
   */


  getThumbBounds() {
    return getThumbBounds(this.currIndex, this.currSlide ? this.currSlide.data : this._initialItemData, this);
  }
  /**
   * If the PhotoSwipe can have continuous loop
   * @returns Boolean
   */


  canLoop() {
    return this.options.loop && this.getNumItems() > 2;
  }
  /**
   * @private
   * @param {PhotoSwipeOptions} options
   * @returns {PreparedPhotoSwipeOptions}
   */


  _prepareOptions(options) {
    if (window.matchMedia('(prefers-reduced-motion), (update: slow)').matches) {
      options.showHideAnimationType = 'none';
      options.zoomAnimationDuration = 0;
    }
    /** @type {PreparedPhotoSwipeOptions} */


    return { ...defaultOptions,
      ...options
    };
  }

}


//# sourceMappingURL=photoswipe.esm.js.map


/***/ }),

/***/ "./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js":
/*!****************************************************************************!*\
  !*** ./node_modules/style-loader/dist/runtime/injectStylesIntoStyleTag.js ***!
  \****************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";


var isOldIE = function isOldIE() {
  var memo;
  return function memorize() {
    if (typeof memo === 'undefined') {
      // Test for IE <= 9 as proposed by Browserhacks
      // @see http://browserhacks.com/#hack-e71d8692f65334173fee715c222cb805
      // Tests for existence of standard globals is to allow style-loader
      // to operate correctly into non-standard environments
      // @see https://github.com/webpack-contrib/style-loader/issues/177
      memo = Boolean(window && document && document.all && !window.atob);
    }

    return memo;
  };
}();

var getTarget = function getTarget() {
  var memo = {};
  return function memorize(target) {
    if (typeof memo[target] === 'undefined') {
      var styleTarget = document.querySelector(target); // Special case to return head of iframe instead of iframe itself

      if (window.HTMLIFrameElement && styleTarget instanceof window.HTMLIFrameElement) {
        try {
          // This will throw an exception if access to iframe is blocked
          // due to cross-origin restrictions
          styleTarget = styleTarget.contentDocument.head;
        } catch (e) {
          // istanbul ignore next
          styleTarget = null;
        }
      }

      memo[target] = styleTarget;
    }

    return memo[target];
  };
}();

var stylesInDom = [];

function getIndexByIdentifier(identifier) {
  var result = -1;

  for (var i = 0; i < stylesInDom.length; i++) {
    if (stylesInDom[i].identifier === identifier) {
      result = i;
      break;
    }
  }

  return result;
}

function modulesToDom(list, options) {
  var idCountMap = {};
  var identifiers = [];

  for (var i = 0; i < list.length; i++) {
    var item = list[i];
    var id = options.base ? item[0] + options.base : item[0];
    var count = idCountMap[id] || 0;
    var identifier = "".concat(id, " ").concat(count);
    idCountMap[id] = count + 1;
    var index = getIndexByIdentifier(identifier);
    var obj = {
      css: item[1],
      media: item[2],
      sourceMap: item[3]
    };

    if (index !== -1) {
      stylesInDom[index].references++;
      stylesInDom[index].updater(obj);
    } else {
      stylesInDom.push({
        identifier: identifier,
        updater: addStyle(obj, options),
        references: 1
      });
    }

    identifiers.push(identifier);
  }

  return identifiers;
}

function insertStyleElement(options) {
  var style = document.createElement('style');
  var attributes = options.attributes || {};

  if (typeof attributes.nonce === 'undefined') {
    var nonce =  true ? __webpack_require__.nc : 0;

    if (nonce) {
      attributes.nonce = nonce;
    }
  }

  Object.keys(attributes).forEach(function (key) {
    style.setAttribute(key, attributes[key]);
  });

  if (typeof options.insert === 'function') {
    options.insert(style);
  } else {
    var target = getTarget(options.insert || 'head');

    if (!target) {
      throw new Error("Couldn't find a style target. This probably means that the value for the 'insert' parameter is invalid.");
    }

    target.appendChild(style);
  }

  return style;
}

function removeStyleElement(style) {
  // istanbul ignore if
  if (style.parentNode === null) {
    return false;
  }

  style.parentNode.removeChild(style);
}
/* istanbul ignore next  */


var replaceText = function replaceText() {
  var textStore = [];
  return function replace(index, replacement) {
    textStore[index] = replacement;
    return textStore.filter(Boolean).join('\n');
  };
}();

function applyToSingletonTag(style, index, remove, obj) {
  var css = remove ? '' : obj.media ? "@media ".concat(obj.media, " {").concat(obj.css, "}") : obj.css; // For old IE

  /* istanbul ignore if  */

  if (style.styleSheet) {
    style.styleSheet.cssText = replaceText(index, css);
  } else {
    var cssNode = document.createTextNode(css);
    var childNodes = style.childNodes;

    if (childNodes[index]) {
      style.removeChild(childNodes[index]);
    }

    if (childNodes.length) {
      style.insertBefore(cssNode, childNodes[index]);
    } else {
      style.appendChild(cssNode);
    }
  }
}

function applyToTag(style, options, obj) {
  var css = obj.css;
  var media = obj.media;
  var sourceMap = obj.sourceMap;

  if (media) {
    style.setAttribute('media', media);
  } else {
    style.removeAttribute('media');
  }

  if (sourceMap && typeof btoa !== 'undefined') {
    css += "\n/*# sourceMappingURL=data:application/json;base64,".concat(btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))), " */");
  } // For old IE

  /* istanbul ignore if  */


  if (style.styleSheet) {
    style.styleSheet.cssText = css;
  } else {
    while (style.firstChild) {
      style.removeChild(style.firstChild);
    }

    style.appendChild(document.createTextNode(css));
  }
}

var singleton = null;
var singletonCounter = 0;

function addStyle(obj, options) {
  var style;
  var update;
  var remove;

  if (options.singleton) {
    var styleIndex = singletonCounter++;
    style = singleton || (singleton = insertStyleElement(options));
    update = applyToSingletonTag.bind(null, style, styleIndex, false);
    remove = applyToSingletonTag.bind(null, style, styleIndex, true);
  } else {
    style = insertStyleElement(options);
    update = applyToTag.bind(null, style, options);

    remove = function remove() {
      removeStyleElement(style);
    };
  }

  update(obj);
  return function updateStyle(newObj) {
    if (newObj) {
      if (newObj.css === obj.css && newObj.media === obj.media && newObj.sourceMap === obj.sourceMap) {
        return;
      }

      update(obj = newObj);
    } else {
      remove();
    }
  };
}

module.exports = function (list, options) {
  options = options || {}; // Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
  // tags it will allow on a page

  if (!options.singleton && typeof options.singleton !== 'boolean') {
    options.singleton = isOldIE();
  }

  list = list || [];
  var lastIdentifiers = modulesToDom(list, options);
  return function update(newList) {
    newList = newList || [];

    if (Object.prototype.toString.call(newList) !== '[object Array]') {
      return;
    }

    for (var i = 0; i < lastIdentifiers.length; i++) {
      var identifier = lastIdentifiers[i];
      var index = getIndexByIdentifier(identifier);
      stylesInDom[index].references--;
    }

    var newLastIdentifiers = modulesToDom(newList, options);

    for (var _i = 0; _i < lastIdentifiers.length; _i++) {
      var _identifier = lastIdentifiers[_i];

      var _index = getIndexByIdentifier(_identifier);

      if (stylesInDom[_index].references === 0) {
        stylesInDom[_index].updater();

        stylesInDom.splice(_index, 1);
      }
    }

    lastIdentifiers = newLastIdentifiers;
  };
};

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
/******/ 			id: moduleId,
/******/ 			loaded: false,
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/amd options */
/******/ 	(() => {
/******/ 		__webpack_require__.amdO = {};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
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
/******/ 	/* webpack/runtime/harmony module decorator */
/******/ 	(() => {
/******/ 		__webpack_require__.hmd = (module) => {
/******/ 			module = Object.create(module);
/******/ 			if (!module.children) module.children = [];
/******/ 			Object.defineProperty(module, 'exports', {
/******/ 				enumerable: true,
/******/ 				set: () => {
/******/ 					throw new Error('ES Modules may not assign module.exports or exports.*, Use ESM export syntax, instead: ' + module.id);
/******/ 				}
/******/ 			});
/******/ 			return module;
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
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/assets/js/common.bundle": 0,
/******/ 			"assets/css/admin.bundle": 0,
/******/ 			"assets/css/donation-form.bundle": 0,
/******/ 			"assets/css/common.bundle": 0,
/******/ 			"assets/css/block-campaign-status-bar.bundle": 0,
/******/ 			"assets/css/block-campaign-single-content.bundle": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkgiftflow"] = self["webpackChunkgiftflow"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/nonce */
/******/ 	(() => {
/******/ 		__webpack_require__.nc = undefined;
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["assets/css/admin.bundle","assets/css/donation-form.bundle","assets/css/common.bundle","assets/css/block-campaign-status-bar.bundle","assets/css/block-campaign-single-content.bundle"], () => (__webpack_require__("./assets/js/common.js")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/admin.bundle","assets/css/donation-form.bundle","assets/css/common.bundle","assets/css/block-campaign-status-bar.bundle","assets/css/block-campaign-single-content.bundle"], () => (__webpack_require__("./assets/css/block-campaign-single-content.scss")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/admin.bundle","assets/css/donation-form.bundle","assets/css/common.bundle","assets/css/block-campaign-status-bar.bundle","assets/css/block-campaign-single-content.bundle"], () => (__webpack_require__("./assets/css/block-campaign-status-bar.scss")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/admin.bundle","assets/css/donation-form.bundle","assets/css/common.bundle","assets/css/block-campaign-status-bar.bundle","assets/css/block-campaign-single-content.bundle"], () => (__webpack_require__("./assets/css/common.scss")))
/******/ 	__webpack_require__.O(undefined, ["assets/css/admin.bundle","assets/css/donation-form.bundle","assets/css/common.bundle","assets/css/block-campaign-status-bar.bundle","assets/css/block-campaign-single-content.bundle"], () => (__webpack_require__("./assets/css/donation-form.scss")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["assets/css/admin.bundle","assets/css/donation-form.bundle","assets/css/common.bundle","assets/css/block-campaign-status-bar.bundle","assets/css/block-campaign-single-content.bundle"], () => (__webpack_require__("./admin/css/admin.scss")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;