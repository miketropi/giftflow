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
/* harmony import */ var _util_campaign_single_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./util/campaign-single.js */ "./assets/js/util/campaign-single.js");
/* harmony import */ var _util_share_block_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./util/share-block.js */ "./assets/js/util/share-block.js");
/* harmony import */ var _util_campaign_images_gallery_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./util/campaign-images-gallery.js */ "./assets/js/util/campaign-images-gallery.js");
/* harmony import */ var _util_helpers_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./util/helpers.js */ "./assets/js/util/helpers.js");
/* harmony import */ var _util_donation_button_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./util/donation-button.js */ "./assets/js/util/donation-button.js");
/* harmony import */ var _util_gfw_image_lightbox_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./util/gfw-image-lightbox.js */ "./assets/js/util/gfw-image-lightbox.js");

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
                (0,_util_helpers_js__WEBPACK_IMPORTED_MODULE_6__.replaceContentBySelector)(__replace_content_selector, __html);
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
  gfw.donationButton_Handle = _util_donation_button_js__WEBPACK_IMPORTED_MODULE_7__["default"];

  // lightbox (vanilla overlay — avoids PhotoSwipe globals / `.pswp` clashes with other plugins)
  gfw.lightbox_initialize = function () {
    var galleryElements = document.querySelector('.giftflow-campaign-single-images:not(.giftflow-campaign-single-images--placeholder)');
    if (!galleryElements) {
      return;
    }
    var openBtn = galleryElements.querySelector('.giftflow-campaign-single-images-lightbox-open-btn');
    if (!openBtn) {
      return;
    }
    var sourceData = Array.from(galleryElements.querySelectorAll('.giftflow-campaign-single-images-image')).map(function (element) {
      return {
        src: element.dataset.pswpSrc,
        width: element.dataset.pswpWidth,
        height: element.dataset.pswpHeight
      };
    }).filter(function (item) {
      return item.src;
    });
    var lightbox = (0,_util_gfw_image_lightbox_js__WEBPACK_IMPORTED_MODULE_8__.createGiftflowLightbox)({
      items: sourceData
    });
    openBtn.addEventListener('click', function () {
      lightbox.open(0);
    });
  };

  // dom loaded
  document.addEventListener('DOMContentLoaded', function () {
    gfw.lightbox_initialize();
    (0,_util_helpers_js__WEBPACK_IMPORTED_MODULE_6__.initClickToCopyByClass)({
      className: 'gfw-click-to-copy'
    });
  });
})(window, jQuery);

/***/ }),

/***/ "./assets/js/util/campaign-images-gallery.js":
/*!***************************************************!*\
  !*** ./assets/js/util/campaign-images-gallery.js ***!
  \***************************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");
/* module decorator */ module = __webpack_require__.hmd(module);



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
      (0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_2__["default"])(e, r, t[r]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) {
      Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r));
    });
  }
  return e;
}
/**
 * GiftFlow Campaign Images Gallery Class
 *
 * A reusable class for managing campaign image galleries with thumbnail navigation.
 *
 * @package GiftFlow
 * @since 1.0.0
 */
var GiftFlowImageGallery = /*#__PURE__*/function () {
  /**
   * Constructor
   *
   * @param {string|HTMLElement} selector - The gallery container selector or element.
   * @param {Object} options - Configuration options.
   */
  function GiftFlowImageGallery(selector) {
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_0__["default"])(this, GiftFlowImageGallery);
    this.container = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!this.container) {
      console.warn('GiftFlowImageGallery: Gallery element not found.');
      return;
    }
    this.options = _objectSpread(_objectSpread({}, GiftFlowImageGallery.defaults), options);
    this.currentIndex = 0;
    this.autoPlayTimer = null;
    this.isExpanded = false;
    this.cacheElements();
    this.bindEvents();
    this.initState();
    if (this.options.autoPlay) {
      this.startAutoPlay();
    }
  }

  /**
   * Cache DOM elements
   */
  return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_1__["default"])(GiftFlowImageGallery, [{
    key: "cacheElements",
    value: function cacheElements() {
      this.thumbnails = this.container.querySelectorAll(this.options.thumbnailSelector);
      this.mainImage = this.container.querySelector(this.options.mainImageSelector);
      this.expandButton = this.container.querySelector(this.options.expandButtonSelector);
    }

    /**
     * Bind event listeners
     */
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      var _this = this;
      // Thumbnail clicks
      this.thumbnails.forEach(function (thumbnail, index) {
        thumbnail.addEventListener('click', function () {
          _this.goTo(index);
        });

        // Keyboard navigation on thumbnails
        thumbnail.addEventListener('keydown', function (e) {
          _this.handleThumbnailKeydown(e, index);
        });
      });

      // Expand button
      if (this.expandButton) {
        // Store original labels
        this.expandButton.dataset.expandLabel = this.expandButton.getAttribute('aria-label') || this.options.i18n.expandLabel;
        this.expandButton.dataset.collapseLabel = this.options.i18n.collapseLabel;
        this.expandButton.addEventListener('click', function (e) {
          e.preventDefault();
          _this.toggleExpand();
        });
        this.expandButton.addEventListener('keydown', function (e) {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            _this.toggleExpand();
          }
        });
      }

      // Main image click (for lightbox integration)
      if (this.mainImage) {
        this.mainImage.addEventListener('click', function () {
          _this.dispatchEvent('mainImageClick', {
            index: _this.currentIndex,
            image: _this.getCurrentImageData()
          });
        });
      }
    }

    /**
     * Initialize gallery state
     */
  }, {
    key: "initState",
    value: function initState() {
      var _this2 = this;
      // Find initially active thumbnail
      var activeThumb = this.container.querySelector("".concat(this.options.thumbnailSelector, ".").concat(this.options.activeClass));
      if (activeThumb) {
        this.currentIndex = Array.from(this.thumbnails).indexOf(activeThumb);
      }

      // Set up ARIA attributes
      this.thumbnails.forEach(function (thumb, index) {
        thumb.setAttribute('role', 'button');
        thumb.setAttribute('tabindex', index === _this2.currentIndex ? '0' : '-1');
        thumb.setAttribute('aria-selected', index === _this2.currentIndex ? 'true' : 'false');
      });
    }

    /**
     * Handle keydown on thumbnails
     *
     * @param {KeyboardEvent} e - Keyboard event.
     * @param {number} index - Current thumbnail index.
     */
  }, {
    key: "handleThumbnailKeydown",
    value: function handleThumbnailKeydown(e, index) {
      var newIndex = index;
      switch (e.key) {
        case 'ArrowRight':
        case 'ArrowDown':
          e.preventDefault();
          newIndex = (index + 1) % this.thumbnails.length;
          break;
        case 'ArrowLeft':
        case 'ArrowUp':
          e.preventDefault();
          newIndex = (index - 1 + this.thumbnails.length) % this.thumbnails.length;
          break;
        case 'Home':
          e.preventDefault();
          newIndex = 0;
          break;
        case 'End':
          e.preventDefault();
          newIndex = this.thumbnails.length - 1;
          break;
        case 'Enter':
        case ' ':
          e.preventDefault();
          this.goTo(index);
          return;
        default:
          return;
      }
      this.goTo(newIndex);
      this.thumbnails[newIndex].focus();
    }

    /**
     * Go to a specific image by index
     *
     * @param {number} index - The image index to display.
     * @returns {GiftFlowImageGallery} Returns this for chaining.
     */
  }, {
    key: "goTo",
    value: function goTo(index) {
      var _this3 = this;
      if (index < 0 || index >= this.thumbnails.length) {
        return this;
      }
      var thumbnail = this.thumbnails[index];
      var previousIndex = this.currentIndex;

      // Update active states
      this.thumbnails.forEach(function (thumb, i) {
        thumb.classList.toggle(_this3.options.activeClass, i === index);
        thumb.setAttribute('aria-selected', i === index ? 'true' : 'false');
        thumb.setAttribute('tabindex', i === index ? '0' : '-1');
      });

      // Update main image
      if (this.mainImage && thumbnail) {
        this.mainImage.src = thumbnail.dataset.imageUrl || thumbnail.src;
        this.mainImage.alt = thumbnail.dataset.imageAlt || thumbnail.alt || '';
        if (thumbnail.dataset.imageFullUrl) {
          this.mainImage.dataset.fullUrl = thumbnail.dataset.imageFullUrl;
        }
        if (thumbnail.dataset.imageId) {
          this.mainImage.dataset.imageId = thumbnail.dataset.imageId;
        }
      }
      this.currentIndex = index;
      this.dispatchEvent('change', {
        index: index,
        previousIndex: previousIndex,
        image: this.getCurrentImageData()
      });
      return this;
    }

    /**
     * Go to next image
     *
     * @returns {GiftFlowImageGallery} Returns this for chaining.
     */
  }, {
    key: "next",
    value: function next() {
      var nextIndex = (this.currentIndex + 1) % this.thumbnails.length;
      return this.goTo(nextIndex);
    }

    /**
     * Go to previous image
     *
     * @returns {GiftFlowImageGallery} Returns this for chaining.
     */
  }, {
    key: "prev",
    value: function prev() {
      var prevIndex = (this.currentIndex - 1 + this.thumbnails.length) % this.thumbnails.length;
      return this.goTo(prevIndex);
    }

    /**
     * Toggle expand/collapse of hidden thumbnails
     *
     * @returns {GiftFlowImageGallery} Returns this for chaining.
     */
  }, {
    key: "toggleExpand",
    value: function toggleExpand() {
      if (this.isExpanded) {
        this.collapse();
      } else {
        this.expand();
      }
      return this;
    }

    /**
     * Expand to show all thumbnails
     *
     * @returns {GiftFlowImageGallery} Returns this for chaining.
     */
  }, {
    key: "expand",
    value: function expand() {
      var _this4 = this;
      var hiddenThumbnails = this.container.querySelectorAll(".".concat(this.options.hiddenClass));
      hiddenThumbnails.forEach(function (thumb) {
        thumb.classList.remove(_this4.options.hiddenClass);
      });
      this.isExpanded = true;
      if (this.expandButton) {
        this.expandButton.classList.add(this.options.expandedClass);
        this.expandButton.setAttribute('aria-label', this.expandButton.dataset.collapseLabel);
        this.expandButton.setAttribute('aria-expanded', 'true');
        if (this.options.removeExpandButton) {
          this.expandButton.remove();
          this.expandButton = null;
        }
      }
      this.dispatchEvent('expanded');
      return this;
    }

    /**
     * Collapse to hide extra thumbnails
     *
     * @returns {GiftFlowImageGallery} Returns this for chaining.
     */
  }, {
    key: "collapse",
    value: function collapse() {
      // This requires knowing which thumbnails should be hidden
      // Usually handled by CSS or initial state
      this.isExpanded = false;
      if (this.expandButton) {
        this.expandButton.classList.remove(this.options.expandedClass);
        this.expandButton.setAttribute('aria-label', this.expandButton.dataset.expandLabel);
        this.expandButton.setAttribute('aria-expanded', 'false');
      }
      this.dispatchEvent('collapsed');
      return this;
    }

    /**
     * Get current image data
     *
     * @returns {Object} Current image data.
     */
  }, {
    key: "getCurrentImageData",
    value: function getCurrentImageData() {
      var thumbnail = this.thumbnails[this.currentIndex];
      if (!thumbnail) {
        return null;
      }
      return {
        index: this.currentIndex,
        url: thumbnail.dataset.imageUrl || thumbnail.src,
        fullUrl: thumbnail.dataset.imageFullUrl || thumbnail.dataset.imageUrl || thumbnail.src,
        alt: thumbnail.dataset.imageAlt || thumbnail.alt || '',
        id: thumbnail.dataset.imageId || null
      };
    }

    /**
     * Get all images data
     *
     * @returns {Object[]} Array of image data objects.
     */
  }, {
    key: "getAllImages",
    value: function getAllImages() {
      return Array.from(this.thumbnails).map(function (thumbnail, index) {
        return {
          index: index,
          url: thumbnail.dataset.imageUrl || thumbnail.src,
          fullUrl: thumbnail.dataset.imageFullUrl || thumbnail.dataset.imageUrl || thumbnail.src,
          alt: thumbnail.dataset.imageAlt || thumbnail.alt || '',
          id: thumbnail.dataset.imageId || null
        };
      });
    }

    /**
     * Get total image count
     *
     * @returns {number} Total number of images.
     */
  }, {
    key: "getCount",
    value: function getCount() {
      return this.thumbnails.length;
    }

    /**
     * Get current index
     *
     * @returns {number} Current image index.
     */
  }, {
    key: "getCurrentIndex",
    value: function getCurrentIndex() {
      return this.currentIndex;
    }

    /**
     * Start auto-play slideshow
     *
     * @param {number} interval - Optional interval in milliseconds.
     * @returns {GiftFlowImageGallery} Returns this for chaining.
     */
  }, {
    key: "startAutoPlay",
    value: function startAutoPlay() {
      var _this5 = this;
      var interval = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      this.stopAutoPlay();
      var ms = interval || this.options.autoPlayInterval;
      this.autoPlayTimer = setInterval(function () {
        _this5.next();
      }, ms);
      this.dispatchEvent('autoPlayStarted', {
        interval: ms
      });
      return this;
    }

    /**
     * Stop auto-play slideshow
     *
     * @returns {GiftFlowImageGallery} Returns this for chaining.
     */
  }, {
    key: "stopAutoPlay",
    value: function stopAutoPlay() {
      if (this.autoPlayTimer) {
        clearInterval(this.autoPlayTimer);
        this.autoPlayTimer = null;
        this.dispatchEvent('autoPlayStopped');
      }
      return this;
    }

    /**
     * Dispatch a custom event
     *
     * @param {string} eventName - Event name (without prefix).
     * @param {Object} detail - Event detail data.
     */
  }, {
    key: "dispatchEvent",
    value: function dispatchEvent(eventName) {
      var detail = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      this.container.dispatchEvent(new CustomEvent("giftflow:gallery:".concat(eventName), {
        detail: _objectSpread(_objectSpread({}, detail), {}, {
          instance: this
        }),
        bubbles: true
      }));
    }

    /**
     * Destroy the instance and clean up
     */
  }, {
    key: "destroy",
    value: function destroy() {
      this.stopAutoPlay();

      // Clone and replace to remove event listeners
      this.thumbnails.forEach(function (thumb) {
        thumb.replaceWith(thumb.cloneNode(true));
      });
      if (this.expandButton) {
        this.expandButton.replaceWith(this.expandButton.cloneNode(true));
      }
      if (this.mainImage) {
        this.mainImage.replaceWith(this.mainImage.cloneNode(true));
      }
      this.container = null;
      this.thumbnails = null;
      this.mainImage = null;
      this.expandButton = null;
    }

    /**
     * Initialize all galleries matching a selector
     *
     * @param {string} selector - Selector for gallery containers.
     * @param {Object} options - Configuration options.
     * @returns {GiftFlowImageGallery[]} Array of gallery instances.
     */
  }], [{
    key: "initAll",
    value: function initAll(selector) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var instances = [];
      document.querySelectorAll(selector).forEach(function (element) {
        instances.push(new GiftFlowImageGallery(element, options));
      });
      return instances;
    }
  }]);
}(); // Auto-initialize on DOMContentLoaded
/**
 * Default configuration options
 *
 * @type {Object}
 */
(0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_2__["default"])(GiftFlowImageGallery, "defaults", {
  thumbnailSelector: '.giftflow-campaign-single-images-gallery-thumbnail',
  mainImageSelector: '.giftflow-campaign-single-images-main',
  expandButtonSelector: '.giftflow-campaign-single-images-gallery-expand',
  hiddenClass: 'giftflow-thumbnail-hidden',
  activeClass: 'active',
  expandedClass: 'expanded',
  removeExpandButton: true,
  autoPlay: false,
  autoPlayInterval: 5000,
  i18n: {
    expandLabel: 'Show more images',
    collapseLabel: 'Show fewer images'
  }
});
document.addEventListener('DOMContentLoaded', function () {
  window.giftflowImageGalleries = GiftFlowImageGallery.initAll('.giftflow-campaign-single-images-gallery');
});

// Export for module systems
if ( true && module.exports) {
  module.exports = GiftFlowImageGallery;
}

// Make available globally
window.GiftFlowImageGallery = GiftFlowImageGallery;

/***/ }),

/***/ "./assets/js/util/campaign-single.js":
/*!*******************************************!*\
  !*** ./assets/js/util/campaign-single.js ***!
  \*******************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/slicedToArray */ "./node_modules/@babel/runtime/helpers/esm/slicedToArray.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");
/* module decorator */ module = __webpack_require__.hmd(module);




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
      (0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_3__["default"])(e, r, t[r]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) {
      Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r));
    });
  }
  return e;
}
/**
 * GiftFlow Tab Widget Class
 *
 * A reusable class for managing tabbed content widgets.
 *
 * @package GiftFlow
 * @since 1.0.0
 */
var GiftFlowTabWidget = /*#__PURE__*/function () {
  /**
   * Constructor
   *
   * @param {string|HTMLElement} selector - The tab widget container selector or element.
   * @param {Object} options - Configuration options.
   */
  function GiftFlowTabWidget(selector) {
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__["default"])(this, GiftFlowTabWidget);
    this.container = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!this.container) {
      console.warn('GiftFlowTabWidget: Tab widget element not found.');
      return;
    }
    this.options = _objectSpread(_objectSpread({}, GiftFlowTabWidget.defaults), options);
    this.activeTabId = null;
    this.cacheElements();
    this.bindEvents();
    this.initFromHash();
  }

  /**
   * Cache DOM elements
   */
  return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__["default"])(GiftFlowTabWidget, [{
    key: "cacheElements",
    value: function cacheElements() {
      this.contentContainer = this.container.querySelector(this.options.contentContainerSelector) || this.container;
      this.tabItems = this.container.querySelectorAll(this.options.tabItemSelector);
      this.contentItems = this.contentContainer.querySelectorAll(this.options.contentItemSelector);
    }

    /**
     * Bind event listeners
     */
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      var _this = this;
      this.tabItems.forEach(function (tabItem) {
        tabItem.addEventListener('click', function (e) {
          e.preventDefault();
          var tabId = tabItem.dataset[_this.options.tabIdAttribute];
          _this.activateTab(tabId);
        });

        // Keyboard accessibility
        tabItem.addEventListener('keydown', function (e) {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            var tabId = tabItem.dataset[_this.options.tabIdAttribute];
            _this.activateTab(tabId);
          }

          // Arrow key navigation
          if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
            e.preventDefault();
            _this.navigateWithArrows(e.key === 'ArrowRight' ? 1 : -1, tabItem);
          }
        });
      });

      // Listen for hash changes
      if (this.options.useHash) {
        window.addEventListener('hashchange', function () {
          return _this.initFromHash();
        });
      }
    }

    /**
     * Initialize tab from URL hash
     */
  }, {
    key: "initFromHash",
    value: function initFromHash() {
      if (!this.options.useHash) {
        return;
      }
      var hash = window.location.hash.substring(1);
      if (hash) {
        // Check for keyword matches (e.g., 'comment' -> 'comments' tab)
        for (var _i = 0, _Object$entries = Object.entries(this.options.hashKeywords); _i < _Object$entries.length; _i++) {
          var _Object$entries$_i = (0,_babel_runtime_helpers_slicedToArray__WEBPACK_IMPORTED_MODULE_0__["default"])(_Object$entries[_i], 2),
            keyword = _Object$entries$_i[0],
            tabId = _Object$entries$_i[1];
          if (hash.includes(keyword)) {
            this.activateTab(tabId);
            return;
          }
        }

        // Try direct tab ID match
        this.activateTab(hash);
      }
    }

    /**
     * Navigate tabs with arrow keys
     *
     * @param {number} direction - Direction to navigate (1 for next, -1 for previous).
     * @param {HTMLElement} currentTab - The currently focused tab.
     */
  }, {
    key: "navigateWithArrows",
    value: function navigateWithArrows(direction, currentTab) {
      var tabsArray = Array.from(this.tabItems);
      var currentIndex = tabsArray.indexOf(currentTab);
      var newIndex = currentIndex + direction;

      // Wrap around
      if (newIndex < 0) {
        newIndex = tabsArray.length - 1;
      } else if (newIndex >= tabsArray.length) {
        newIndex = 0;
      }
      var newTab = tabsArray[newIndex];
      newTab.focus();
      this.activateTab(newTab.dataset[this.options.tabIdAttribute]);
    }

    /**
     * Activate a tab by its ID
     *
     * @param {string} tabId - The tab ID to activate.
     * @returns {GiftFlowTabWidget} Returns this for chaining.
     */
  }, {
    key: "activateTab",
    value: function activateTab(tabId) {
      var _this2 = this;
      if (!tabId || tabId === this.activeTabId) {
        return this;
      }
      var targetTab = this.container.querySelector("".concat(this.options.tabItemSelector, "[data-").concat(this.toKebabCase(this.options.tabIdAttribute), "=\"").concat(tabId, "\"]"));
      var targetContent = this.contentContainer.querySelector("".concat(this.options.contentItemSelector, "[data-").concat(this.toKebabCase(this.options.tabIdAttribute), "=\"").concat(tabId, "\"]"));
      if (!targetTab || !targetContent) {
        return this;
      }

      // Deactivate all tabs and content
      this.tabItems.forEach(function (tab) {
        tab.classList.remove(_this2.options.activeClass);
        tab.setAttribute('aria-selected', 'false');
        tab.setAttribute('tabindex', '-1');
      });
      this.contentItems.forEach(function (content) {
        content.classList.remove(_this2.options.activeClass);
        content.setAttribute('aria-hidden', 'true');
      });

      // Activate target tab and content
      targetTab.classList.add(this.options.activeClass);
      targetTab.setAttribute('aria-selected', 'true');
      targetTab.setAttribute('tabindex', '0');
      targetContent.classList.add(this.options.activeClass);
      targetContent.setAttribute('aria-hidden', 'false');
      this.activeTabId = tabId;

      // Dispatch custom event
      this.container.dispatchEvent(new CustomEvent('giftflow:tab:changed', {
        detail: {
          tabId: tabId,
          tab: targetTab,
          content: targetContent,
          instance: this
        },
        bubbles: true
      }));
      return this;
    }

    /**
     * Convert camelCase to kebab-case
     *
     * @param {string} str - The string to convert.
     * @returns {string} The kebab-case string.
     */
  }, {
    key: "toKebabCase",
    value: function toKebabCase(str) {
      return str.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
    }

    /**
     * Get the currently active tab ID
     *
     * @returns {string|null} The active tab ID or null.
     */
  }, {
    key: "getActiveTabId",
    value: function getActiveTabId() {
      return this.activeTabId;
    }

    /**
     * Get the currently active tab element
     *
     * @returns {HTMLElement|null} The active tab element or null.
     */
  }, {
    key: "getActiveTab",
    value: function getActiveTab() {
      if (!this.activeTabId) {
        return null;
      }
      return this.container.querySelector("".concat(this.options.tabItemSelector, "[data-").concat(this.toKebabCase(this.options.tabIdAttribute), "=\"").concat(this.activeTabId, "\"]"));
    }

    /**
     * Get the currently active content element
     *
     * @returns {HTMLElement|null} The active content element or null.
     */
  }, {
    key: "getActiveContent",
    value: function getActiveContent() {
      if (!this.activeTabId) {
        return null;
      }
      return this.contentContainer.querySelector("".concat(this.options.contentItemSelector, "[data-").concat(this.toKebabCase(this.options.tabIdAttribute), "=\"").concat(this.activeTabId, "\"]"));
    }

    /**
     * Go to the next tab
     *
     * @returns {GiftFlowTabWidget} Returns this for chaining.
     */
  }, {
    key: "next",
    value: function next() {
      var _this3 = this;
      var tabsArray = Array.from(this.tabItems);
      var currentIndex = tabsArray.findIndex(function (tab) {
        return tab.dataset[_this3.options.tabIdAttribute] === _this3.activeTabId;
      });
      var nextIndex = (currentIndex + 1) % tabsArray.length;
      var nextTabId = tabsArray[nextIndex].dataset[this.options.tabIdAttribute];
      return this.activateTab(nextTabId);
    }

    /**
     * Go to the previous tab
     *
     * @returns {GiftFlowTabWidget} Returns this for chaining.
     */
  }, {
    key: "prev",
    value: function prev() {
      var _this4 = this;
      var tabsArray = Array.from(this.tabItems);
      var currentIndex = tabsArray.findIndex(function (tab) {
        return tab.dataset[_this4.options.tabIdAttribute] === _this4.activeTabId;
      });
      var prevIndex = (currentIndex - 1 + tabsArray.length) % tabsArray.length;
      var prevTabId = tabsArray[prevIndex].dataset[this.options.tabIdAttribute];
      return this.activateTab(prevTabId);
    }

    /**
     * Refresh the cached elements (useful after dynamic content changes)
     *
     * @returns {GiftFlowTabWidget} Returns this for chaining.
     */
  }, {
    key: "refresh",
    value: function refresh() {
      this.cacheElements();
      return this;
    }

    /**
     * Destroy the instance and clean up
     */
  }, {
    key: "destroy",
    value: function destroy() {
      this.tabItems.forEach(function (tabItem) {
        tabItem.replaceWith(tabItem.cloneNode(true));
      });
      if (this.options.useHash) {
        window.removeEventListener('hashchange', this.initFromHash);
      }
      this.container = null;
      this.contentContainer = null;
      this.tabItems = null;
      this.contentItems = null;
    }

    /**
     * Initialize all tab widgets matching a selector
     *
     * @param {string} selector - Selector for tab widget containers.
     * @param {Object} options - Configuration options.
     * @returns {GiftFlowTabWidget[]} Array of tab widget instances.
     */
  }], [{
    key: "initAll",
    value: function initAll(selector) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var instances = [];
      document.querySelectorAll(selector).forEach(function (element) {
        instances.push(new GiftFlowTabWidget(element, options));
      });
      return instances;
    }
  }]);
}(); // Auto-initialize on DOMContentLoaded
/**
 * Default configuration options
 *
 * @type {Object}
 */
(0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_3__["default"])(GiftFlowTabWidget, "defaults", {
  tabItemSelector: '.giftflow-tab-widget-tab-item',
  contentItemSelector: '.giftflow-tab-widget-content-item',
  contentContainerSelector: '.giftflow-tab-widget-content',
  activeClass: 'active',
  tabIdAttribute: 'tabId',
  useHash: true,
  hashKeywords: {
    comment: 'comments'
  }
});
document.addEventListener('DOMContentLoaded', function () {
  // Auto-init any tab widgets with default class
  window.giftflowTabWidgets = GiftFlowTabWidget.initAll('.giftflow-campaign-single-content');
});

// Export for module systems
if ( true && module.exports) {
  module.exports = GiftFlowTabWidget;
}

// Make available globally
window.GiftFlowTabWidget = GiftFlowTabWidget;

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
      if (!originalFormTitle) {
        originalFormTitle = form.find('#reply-title').html();
        console.log('originalFormTitle', 3, originalFormTitle);
      }

      // Store original position if not already stored
      if (!originalFormPosition) {
        originalFormPosition = form.parent();
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
        console.log('originalFormPosition', 1);
      }

      // Reset form title
      if (originalFormTitle) {
        form.find('#reply-title').html(originalFormTitle);
        console.log('originalFormTitle', 2, originalFormTitle);
      }
    });
  };
  $(function () {
    // if window have variable __giftflow_disable_reply_comment_handle = true when return
    if (window.__giftflow_disable_reply_comment_handle) {
      return;
    }
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
    var _window;
    var _el$dataset, campaignId, campaignTitle, modalWidth, ajaxModal;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.n) {
        case 0:
          _el$dataset = el.dataset, campaignId = _el$dataset.campaignId, campaignTitle = _el$dataset.campaignTitle;
          modalWidth = ((_window = window) === null || _window === void 0 || (_window = _window._giftflow_common) === null || _window === void 0 ? void 0 : _window.modalWidth) || '720px';
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
            width: modalWidth,
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

/***/ "./assets/js/util/gfw-image-lightbox.js":
/*!**********************************************!*\
  !*** ./assets/js/util/gfw-image-lightbox.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createGiftflowLightbox: () => (/* binding */ createGiftflowLightbox)
/* harmony export */ });
/**
 * Fullscreen image lightbox for GiftFlow (PhotoSwipe-like UI, no `.pswp` / globals).
 *
 * @typedef {{ src: string, width?: string|number, height?: string|number }} GfwLightboxItem
 */

var NS = 'gfw-lightbox';
var SVG_PREV = '<svg class="' + NS + '__chev" width="32" height="32" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>';
var SVG_NEXT = '<svg class="' + NS + '__chev" width="32" height="32" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>';
var SVG_CLOSE = '<svg class="' + NS + '__icon-close" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>';

/**
 * @param {object} options
 * @param {GfwLightboxItem[]} options.items
 * @param {string} [options.closeLabel]
 * @param {string} [options.prevLabel]
 * @param {string} [options.nextLabel]
 */
function createGiftflowLightbox(options) {
  var items = options.items;
  var closeLabel = options.closeLabel || 'Close';
  var prevLabel = options.prevLabel || 'Previous';
  var nextLabel = options.nextLabel || 'Next';
  if (!Array.isArray(items) || items.length === 0) {
    return {
      open: function open() {},
      close: function close() {},
      destroy: function destroy() {}
    };
  }
  var multi = items.length > 1;
  var root = null;
  var index = 0;
  var lastFocus = null;
  function buildDom() {
    var el = document.createElement('div');
    el.className = NS;
    el.setAttribute('role', 'dialog');
    el.setAttribute('aria-modal', 'true');
    el.setAttribute('aria-label', 'Gallery');
    el.innerHTML = "\n\t\t\t<div class=\"".concat(NS, "__bg\" role=\"presentation\"></div>\n\t\t\t<div class=\"").concat(NS, "__ui\">\n\t\t\t\t<div class=\"").concat(NS, "__toolbar\">\n\t\t\t\t\t<div class=\"").concat(NS, "__counter\" aria-live=\"polite\"></div>\n\t\t\t\t\t<button type=\"button\" class=\"").concat(NS, "__btn ").concat(NS, "__btn--close\" aria-label=\"").concat(escapeAttr(closeLabel), "\">").concat(SVG_CLOSE, "</button>\n\t\t\t\t</div>\n\t\t\t\t").concat(multi ? "<button type=\"button\" class=\"".concat(NS, "__btn ").concat(NS, "__btn--arrow ").concat(NS, "__btn--arrow--prev\" aria-label=\"").concat(escapeAttr(prevLabel), "\">").concat(SVG_PREV, "</button>\n\t\t\t\t<button type=\"button\" class=\"").concat(NS, "__btn ").concat(NS, "__btn--arrow ").concat(NS, "__btn--arrow--next\" aria-label=\"").concat(escapeAttr(nextLabel), "\">").concat(SVG_NEXT, "</button>") : '', "\n\t\t\t\t<div class=\"").concat(NS, "__stage-wrap\">\n\t\t\t\t\t<div class=\"").concat(NS, "__stage\">\n\t\t\t\t\t\t<div class=\"").concat(NS, "__loader\" aria-hidden=\"true\">\n\t\t\t\t\t\t\t<span class=\"").concat(NS, "__spinner\"></span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<img class=\"").concat(NS, "__img\" alt=\"\" decoding=\"async\" />\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t");
    return el;
  }
  function escapeAttr(s) {
    return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
  }
  function onKeydown(e) {
    if (e.key === 'Escape') {
      e.preventDefault();
      close();
      return;
    }
    if (!multi) {
      return;
    }
    if (e.key === 'ArrowLeft') {
      e.preventDefault();
      go(-1);
    } else if (e.key === 'ArrowRight') {
      e.preventDefault();
      go(1);
    }
  }
  function setLoading(loading) {
    if (!root) {
      return;
    }
    var stage = root.querySelector(".".concat(NS, "__stage"));
    stage === null || stage === void 0 || stage.classList.toggle("".concat(NS, "__stage--loading"), loading);
  }
  function render() {
    if (!root) {
      return;
    }
    var item = items[index];
    var img = root.querySelector(".".concat(NS, "__img"));
    var counter = root.querySelector(".".concat(NS, "__counter"));
    if (counter) {
      counter.textContent = "".concat(index + 1, " / ").concat(items.length);
    }
    if (!img) {
      return;
    }
    setLoading(true);
    img.classList.remove("".concat(NS, "__img--ready"));
    var settled = false;
    var finishOk = function finishOk() {
      if (settled) {
        return;
      }
      settled = true;
      setLoading(false);
      img.classList.add("".concat(NS, "__img--ready"));
    };
    var finishErr = function finishErr() {
      if (settled) {
        return;
      }
      settled = true;
      setLoading(false);
      img.alt = '';
    };
    img.onload = finishOk;
    img.onerror = finishErr;
    img.src = item.src;
    if (img.complete && img.naturalWidth > 0) {
      finishOk();
    }
  }
  function go(delta) {
    if (!multi) {
      return;
    }
    index = (index + delta + items.length) % items.length;
    render();
  }
  function open() {
    var startIndex = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 0;
    index = Math.max(0, Math.min(startIndex | 0, items.length - 1));
    if (!root) {
      root = buildDom();
      document.body.appendChild(root);
      var bg = root.querySelector(".".concat(NS, "__bg"));
      var btnClose = root.querySelector(".".concat(NS, "__btn--close"));
      var prev = root.querySelector(".".concat(NS, "__btn--arrow--prev"));
      var next = root.querySelector(".".concat(NS, "__btn--arrow--next"));
      bg === null || bg === void 0 || bg.addEventListener('click', close);
      btnClose === null || btnClose === void 0 || btnClose.addEventListener('click', function (e) {
        e.stopPropagation();
        close();
      });
      prev === null || prev === void 0 || prev.addEventListener('click', function (e) {
        e.stopPropagation();
        go(-1);
      });
      next === null || next === void 0 || next.addEventListener('click', function (e) {
        e.stopPropagation();
        go(1);
      });
    }
    lastFocus = document.activeElement;
    render();
    root.hidden = false;
    root.classList.add("".concat(NS, "--visible"));
    document.body.classList.add("".concat(NS, "-open"));
    document.addEventListener('keydown', onKeydown);
    requestAnimationFrame(function () {
      var _root$querySelector;
      (_root$querySelector = root.querySelector(".".concat(NS, "__btn--close"))) === null || _root$querySelector === void 0 || _root$querySelector.focus();
    });
  }
  function close() {
    if (!root || root.hidden) {
      return;
    }
    root.classList.remove("".concat(NS, "--visible"));
    root.hidden = true;
    document.body.classList.remove("".concat(NS, "-open"));
    document.removeEventListener('keydown', onKeydown);
    var img = root.querySelector(".".concat(NS, "__img"));
    if (img) {
      img.removeAttribute('src');
      img.classList.remove("".concat(NS, "__img--ready"));
    }
    if (lastFocus && typeof lastFocus.focus === 'function') {
      lastFocus.focus();
    }
    lastFocus = null;
  }
  function destroy() {
    var _root;
    close();
    (_root = root) === null || _root === void 0 || _root.remove();
    root = null;
  }
  return {
    open: open,
    close: close,
    destroy: destroy
  };
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
/* harmony export */   createElementFromTemplate: () => (/* binding */ createElementFromTemplate),
/* harmony export */   initClickToCopyByClass: () => (/* binding */ initClickToCopyByClass),
/* harmony export */   replaceContentBySelector: () => (/* binding */ replaceContentBySelector),
/* harmony export */   validateValue: () => (/* binding */ validateValue)
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

        // price
        case 'price':
          // Accepts numeric value, optional decimals, >0, not empty
          if (value === '' || value === null || value === undefined || isNaN(value) || Number(value) <= 0 || !/^(\d+)(\.\d{1,2})?$/.test(value.toString())) {
            overallValid = false;
          }
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
function createElementFromTemplate(template) {
  var div = document.createElement('div');
  div.innerHTML = template;
  return div.children[0] || null;
}

/** @type {WeakMap<Document|HTMLElement, Set<string>>} */
var clickToCopyRoots = new WeakMap();

/**
 * Copy string to clipboard (Clipboard API when available, else execCommand fallback).
 * @param {string} text
 * @returns {Promise<boolean>}
 */
function copyTextToClipboard(_x) {
  return _copyTextToClipboard.apply(this, arguments);
}
/**
 * Short visual feedback after a successful copy (Web Animations API).
 * @param {HTMLElement} element
 */
function _copyTextToClipboard() {
  _copyTextToClipboard = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee(text) {
    var ta, ok, _t;
    return _regenerator().w(function (_context) {
      while (1) switch (_context.p = _context.n) {
        case 0:
          if (text) {
            _context.n = 1;
            break;
          }
          return _context.a(2, false);
        case 1:
          _context.p = 1;
          if (!(navigator.clipboard && window.isSecureContext)) {
            _context.n = 3;
            break;
          }
          _context.n = 2;
          return navigator.clipboard.writeText(text);
        case 2:
          return _context.a(2, true);
        case 3:
          _context.n = 5;
          break;
        case 4:
          _context.p = 4;
          _t = _context.v;
        case 5:
          ta = document.createElement('textarea');
          ta.value = text;
          ta.setAttribute('readonly', '');
          ta.style.position = 'fixed';
          ta.style.left = '-9999px';
          ta.style.top = '0';
          document.body.appendChild(ta);
          ta.select();
          ta.setSelectionRange(0, text.length);
          ok = false;
          try {
            ok = document.execCommand('copy');
          } catch (_unused2) {
            ok = false;
          }
          document.body.removeChild(ta);
          return _context.a(2, ok);
      }
    }, _callee, null, [[1, 4]]);
  }));
  return _copyTextToClipboard.apply(this, arguments);
}
function playCopySuccessEffect(element) {
  if (typeof element.animate !== 'function') return;
  var computed = window.getComputedStyle(element);
  var fromBg = computed.backgroundColor || 'transparent';
  element.animate([{
    backgroundColor: fromBg,
    transform: 'scale(1)'
  }, {
    backgroundColor: 'rgba(0, 255, 247, 0.48)',
    transform: 'scale(1.01)'
  }, {
    backgroundColor: fromBg,
    transform: 'scale(1)'
  }], {
    duration: 450,
    easing: 'ease-out'
  });
}

/**
 * Delegate clicks: elements with the given class copy text and show success feedback.
 * Works for content injected after load (AJAX) because the listener is on document/root.
 *
 * Text source: `data-copy` / `data-copy-text` attribute if set, otherwise trimmed `textContent`.
 * Tooltip: on first hover, `data-copy-tooltip` is set from `tooltipLabel` (or localized
 * `giftflow_common.click_to_copy_tooltip`). Override per element with `data-copy-tooltip` in HTML,
 * or disable with `data-no-copy-tooltip`.
 *
 * @param {Object} options
 * @param {string} options.className - Single CSS class (no leading dot), e.g. `'gfw-click-to-copy'`.
 * @param {Document|HTMLElement} [options.root=document] - Delegation root (use a modal container if needed).
 * @param {string} [options.successClass='gfw-click-to-copy--copied'] - Class added briefly after success.
 * @param {number} [options.successDuration=1500] - How long successClass stays before removal (ms).
 * @param {string} [options.tooltipLabel] - Hover tooltip text; falls back to `giftflow_common.click_to_copy_tooltip` or English default.
 * @returns {void}
 */
function initClickToCopyByClass(options) {
  var _options$successClass;
  if (!options || typeof options.className !== 'string') {
    console.warn('initClickToCopyByClass: `className` (string) is required');
    return;
  }
  var cls = options.className.replace(/^\./, '').trim().split(/\s+/)[0];
  if (!cls) {
    console.warn('initClickToCopyByClass: empty className');
    return;
  }
  var root = options.root && options.root.nodeType ? options.root : document;
  var successClass = (_options$successClass = options.successClass) !== null && _options$successClass !== void 0 ? _options$successClass : 'gfw-click-to-copy--copied';
  var successDuration = typeof options.successDuration === 'number' ? options.successDuration : 1500;
  var tooltipLabel = typeof options.tooltipLabel === 'string' && options.tooltipLabel ? options.tooltipLabel : typeof window !== 'undefined' && window.giftflow_common && typeof window.giftflow_common.click_to_copy_tooltip === 'string' && window.giftflow_common.click_to_copy_tooltip || 'Click to copy';
  var set = clickToCopyRoots.get(root);
  if (!set) {
    set = new Set();
    clickToCopyRoots.set(root, set);
  }
  if (set.has(cls)) return;
  set.add(cls);
  var selector = ".".concat(CSS.escape(cls));
  root.addEventListener('pointerover', function (e) {
    var target = e.target;
    if (!(target instanceof Element)) return;
    var el = target.closest(selector);
    if (!el || !root.contains(el)) return;
    if (el.hasAttribute('data-no-copy-tooltip')) return;
    if (el.hasAttribute('data-copy-tooltip')) return;
    el.setAttribute('data-copy-tooltip', tooltipLabel);
  }, true);
  root.addEventListener('click', function (e) {
    var target = e.target;
    if (!(target instanceof Element)) return;
    var el = target.closest(selector);
    if (!el || !root.contains(el)) return;
    var explicit = el.getAttribute('data-copy') || el.getAttribute('data-copy-text') || '';
    var text = (explicit || el.textContent || '').trim();
    if (!text) return;
    e.preventDefault();
    copyTextToClipboard(text).then(function (ok) {
      if (!ok) return;
      el.classList.add(successClass);
      playCopySuccessEffect(el);
      var prev = el._gfwCopySuccessTimer;
      if (prev) clearTimeout(prev);
      el._gfwCopySuccessTimer = setTimeout(function () {
        el.classList.remove(successClass);
        el._gfwCopySuccessTimer = undefined;
      }, successDuration);
    });
  }, false);
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
              // return;
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

/***/ "./assets/js/util/share-block.js":
/*!***************************************!*\
  !*** ./assets/js/util/share-block.js ***!
  \***************************************/
/***/ ((module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @babel/runtime/helpers/asyncToGenerator */ "./node_modules/@babel/runtime/helpers/esm/asyncToGenerator.js");
/* harmony import */ var _babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @babel/runtime/helpers/classCallCheck */ "./node_modules/@babel/runtime/helpers/esm/classCallCheck.js");
/* harmony import */ var _babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @babel/runtime/helpers/createClass */ "./node_modules/@babel/runtime/helpers/esm/createClass.js");
/* harmony import */ var _babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @babel/runtime/helpers/defineProperty */ "./node_modules/@babel/runtime/helpers/esm/defineProperty.js");
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
      (0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_3__["default"])(e, r, t[r]);
    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) {
      Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r));
    });
  }
  return e;
}
/**
 * GiftFlow Share Block Class
 *
 * A reusable class for managing social sharing and clipboard copy functionality.
 *
 * @package GiftFlow
 * @since 1.0.0
 */
var GiftFlowShare = /*#__PURE__*/function () {
  /**
   * Constructor
   *
   * @param {string|HTMLElement} selector - The share block container selector or element.
   * @param {Object} options - Configuration options.
   */
  function GiftFlowShare(selector) {
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
    (0,_babel_runtime_helpers_classCallCheck__WEBPACK_IMPORTED_MODULE_1__["default"])(this, GiftFlowShare);
    this.container = typeof selector === 'string' ? document.querySelector(selector) : selector;
    if (!this.container) {
      console.warn('GiftFlowShare: Share block element not found.');
      return;
    }
    this.options = _objectSpread(_objectSpread({}, GiftFlowShare.defaults), options);
    this.feedbackTimeout = null;
    this.cacheElements();
    this.bindEvents();
  }

  /**
   * Cache DOM elements
   */
  return (0,_babel_runtime_helpers_createClass__WEBPACK_IMPORTED_MODULE_2__["default"])(GiftFlowShare, [{
    key: "cacheElements",
    value: function cacheElements() {
      this.copyButton = this.container.querySelector(this.options.copyButtonSelector);
      this.feedback = this.container.querySelector(this.options.feedbackSelector);
      this.shareButtons = this.container.querySelectorAll(this.options.shareButtonSelector);
    }

    /**
     * Bind event listeners
     */
  }, {
    key: "bindEvents",
    value: function bindEvents() {
      var _this = this;
      // Copy button
      if (this.copyButton) {
        this.copyButton.addEventListener('click', function (e) {
          e.preventDefault();
          _this.copyToClipboard();
        });
      }

      // Share buttons
      this.shareButtons.forEach(function (button) {
        button.addEventListener('click', function (e) {
          e.preventDefault();
          var network = button.getAttribute(_this.options.networkAttribute);
          var url = button.getAttribute(_this.options.urlAttribute);
          _this.share(network, url);
        });
      });
    }

    /**
     * Get the share URL
     *
     * @returns {string} The URL to share.
     */
  }, {
    key: "getShareUrl",
    value: function getShareUrl() {
      return this.options.shareUrl || window.location.href;
    }

    /**
     * Get the share title
     *
     * @returns {string} The title to share.
     */
  }, {
    key: "getShareTitle",
    value: function getShareTitle() {
      return this.options.shareTitle || document.title;
    }

    /**
     * Get the share text
     *
     * @returns {string} The text to share.
     */
  }, {
    key: "getShareText",
    value: function getShareText() {
      return this.options.shareText || '';
    }

    /**
     * Copy URL to clipboard
     *
     * @param {string} url - Optional URL to copy. Defaults to share URL.
     * @returns {Promise<boolean>} Promise resolving to success status.
     */
  }, {
    key: "copyToClipboard",
    value: function () {
      var _copyToClipboard = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee() {
        var url,
          textToCopy,
          _args = arguments,
          _t;
        return _regenerator().w(function (_context) {
          while (1) switch (_context.p = _context.n) {
            case 0:
              url = _args.length > 0 && _args[0] !== undefined ? _args[0] : null;
              textToCopy = url || this.getShareUrl();
              _context.p = 1;
              if (!(navigator.clipboard && window.isSecureContext)) {
                _context.n = 3;
                break;
              }
              _context.n = 2;
              return navigator.clipboard.writeText(textToCopy);
            case 2:
              this.showFeedback();
              this.dispatchEvent('copied', {
                url: textToCopy
              });
              return _context.a(2, true);
            case 3:
              return _context.a(2, this.fallbackCopy(textToCopy));
            case 4:
              _context.n = 6;
              break;
            case 5:
              _context.p = 5;
              _t = _context.v;
              console.error('GiftFlowShare: Failed to copy:', _t);
              return _context.a(2, this.fallbackCopy(textToCopy));
            case 6:
              return _context.a(2);
          }
        }, _callee, this, [[1, 5]]);
      }));
      function copyToClipboard() {
        return _copyToClipboard.apply(this, arguments);
      }
      return copyToClipboard;
    }()
    /**
     * Fallback copy method for older browsers
     *
     * @param {string} text - Text to copy.
     * @returns {boolean} Success status.
     */
  }, {
    key: "fallbackCopy",
    value: function fallbackCopy(text) {
      var textArea = document.createElement('textarea');
      textArea.value = text;

      // Make it invisible
      Object.assign(textArea.style, {
        position: 'fixed',
        left: '-999999px',
        top: '-999999px',
        opacity: '0'
      });
      document.body.appendChild(textArea);
      textArea.focus();
      textArea.select();
      try {
        var success = document.execCommand('copy');
        if (success) {
          this.showFeedback();
          this.dispatchEvent('copied', {
            url: text
          });
        }
        return success;
      } catch (err) {
        console.error('GiftFlowShare: Fallback copy failed:', err);
        return false;
      } finally {
        document.body.removeChild(textArea);
      }
    }

    /**
     * Show copy feedback message
     */
  }, {
    key: "showFeedback",
    value: function showFeedback() {
      var _this2 = this;
      if (!this.feedback) {
        return;
      }

      // Clear any existing timeout
      if (this.feedbackTimeout) {
        clearTimeout(this.feedbackTimeout);
      }
      this.feedback.style.display = 'block';
      this.feedback.classList.add('is-visible');
      this.feedbackTimeout = setTimeout(function () {
        _this2.feedback.style.display = 'none';
        _this2.feedback.classList.remove('is-visible');
      }, this.options.feedbackDuration);
    }

    /**
     * Share to a social network
     *
     * @param {string} network - The network name (facebook, twitter, etc.).
     * @param {string} url - Optional URL to share. Defaults to share URL.
     * @returns {Window|null} The popup window or null.
     */
  }, {
    key: "share",
    value: function share(network) {
      var url = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : null;
      var shareUrl = url || this.getShareUrl();
      var shareTitle = this.getShareTitle();
      var shareText = this.getShareText();

      // Use Web Share API if available and network is 'native'
      if (network === 'native' && navigator.share) {
        return this.nativeShare(shareUrl, shareTitle, shareText);
      }

      // Get network URL template
      var template = GiftFlowShare.networks[network === null || network === void 0 ? void 0 : network.toLowerCase()];
      if (!template) {
        console.warn("GiftFlowShare: Unknown network \"".concat(network, "\""));
        return null;
      }

      // Build share URL
      var networkUrl = template.replace('{url}', encodeURIComponent(shareUrl)).replace('{title}', encodeURIComponent(shareTitle)).replace('{text}', encodeURIComponent(shareText));

      // Handle email differently
      if (network.toLowerCase() === 'email') {
        window.location.href = networkUrl;
        this.dispatchEvent('shared', {
          network: network,
          url: shareUrl
        });
        return null;
      }

      // Open popup
      var popup = this.openPopup(networkUrl, network);
      this.dispatchEvent('shared', {
        network: network,
        url: shareUrl,
        popup: popup
      });
      return popup;
    }

    /**
     * Use native Web Share API
     *
     * @param {string} url - URL to share.
     * @param {string} title - Title to share.
     * @param {string} text - Text to share.
     * @returns {Promise} Share promise.
     */
  }, {
    key: "nativeShare",
    value: function () {
      var _nativeShare = (0,_babel_runtime_helpers_asyncToGenerator__WEBPACK_IMPORTED_MODULE_0__["default"])(/*#__PURE__*/_regenerator().m(function _callee2(url, title, text) {
        var _t2;
        return _regenerator().w(function (_context2) {
          while (1) switch (_context2.p = _context2.n) {
            case 0:
              _context2.p = 0;
              _context2.n = 1;
              return navigator.share({
                title: title,
                text: text,
                url: url
              });
            case 1:
              this.dispatchEvent('shared', {
                network: 'native',
                url: url
              });
              return _context2.a(2, true);
            case 2:
              _context2.p = 2;
              _t2 = _context2.v;
              if (_t2.name !== 'AbortError') {
                console.error('GiftFlowShare: Native share failed:', _t2);
              }
              return _context2.a(2, false);
          }
        }, _callee2, this, [[0, 2]]);
      }));
      function nativeShare(_x, _x2, _x3) {
        return _nativeShare.apply(this, arguments);
      }
      return nativeShare;
    }()
    /**
     * Open a popup window
     *
     * @param {string} url - URL to open.
     * @param {string} name - Window name.
     * @returns {Window|null} The popup window.
     */
  }, {
    key: "openPopup",
    value: function openPopup(url, name) {
      var _this$options = this.options,
        popupWidth = _this$options.popupWidth,
        popupHeight = _this$options.popupHeight;

      // Center the popup
      var left = (window.innerWidth - popupWidth) / 2 + window.screenX;
      var top = (window.innerHeight - popupHeight) / 2 + window.screenY;
      var features = ["width=".concat(popupWidth), "height=".concat(popupHeight), "left=".concat(left), "top=".concat(top), 'toolbar=no', 'menubar=no', 'scrollbars=yes', 'resizable=yes'].join(',');
      return window.open(url, name, features);
    }

    /**
     * Dispatch a custom event
     *
     * @param {string} eventName - Event name (without prefix).
     * @param {Object} detail - Event detail data.
     */
  }, {
    key: "dispatchEvent",
    value: function dispatchEvent(eventName) {
      var detail = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      this.container.dispatchEvent(new CustomEvent("giftflow:share:".concat(eventName), {
        detail: _objectSpread(_objectSpread({}, detail), {}, {
          instance: this
        }),
        bubbles: true
      }));
    }

    /**
     * Check if native sharing is supported
     *
     * @returns {boolean} True if Web Share API is available.
     */
  }, {
    key: "destroy",
    value:
    /**
     * Destroy the instance and clean up
     */
    function destroy() {
      if (this.feedbackTimeout) {
        clearTimeout(this.feedbackTimeout);
      }

      // Clone and replace to remove event listeners
      if (this.copyButton) {
        this.copyButton.replaceWith(this.copyButton.cloneNode(true));
      }
      this.shareButtons.forEach(function (button) {
        button.replaceWith(button.cloneNode(true));
      });
      this.container = null;
      this.copyButton = null;
      this.feedback = null;
      this.shareButtons = null;
    }

    /**
     * Initialize all share blocks matching a selector
     *
     * @param {string} selector - Selector for share block containers.
     * @param {Object} options - Configuration options.
     * @returns {GiftFlowShare[]} Array of share instances.
     */
  }], [{
    key: "isNativeShareSupported",
    value: function isNativeShareSupported() {
      return typeof navigator.share === 'function';
    }

    /**
     * Add a custom network
     *
     * @param {string} name - Network name.
     * @param {string} urlTemplate - URL template with {url}, {title}, {text} placeholders.
     */
  }, {
    key: "addNetwork",
    value: function addNetwork(name, urlTemplate) {
      GiftFlowShare.networks[name.toLowerCase()] = urlTemplate;
    }
  }, {
    key: "initAll",
    value: function initAll(selector) {
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var instances = [];
      document.querySelectorAll(selector).forEach(function (element) {
        instances.push(new GiftFlowShare(element, options));
      });
      return instances;
    }
  }]);
}(); // Legacy function support (backwards compatibility)
/**
 * Default configuration options
 *
 * @type {Object}
 */
(0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_3__["default"])(GiftFlowShare, "defaults", {
  copyButtonSelector: '.giftflow-share__button--copy-url',
  feedbackSelector: '.giftflow-share__copy-feedback',
  shareButtonSelector: '.giftflow-share__button',
  urlAttribute: 'data-url',
  networkAttribute: 'data-network',
  feedbackDuration: 2000,
  shareUrl: null,
  // If null, uses current page URL
  shareTitle: null,
  // If null, uses document title
  shareText: null,
  popupWidth: 600,
  popupHeight: 400
});
/**
 * Share URL templates for different networks
 *
 * @type {Object}
 */
(0,_babel_runtime_helpers_defineProperty__WEBPACK_IMPORTED_MODULE_3__["default"])(GiftFlowShare, "networks", {
  facebook: 'https://www.facebook.com/sharer/sharer.php?u={url}',
  twitter: 'https://twitter.com/intent/tweet?url={url}&text={title}',
  x: 'https://twitter.com/intent/tweet?url={url}&text={title}',
  linkedin: 'https://www.linkedin.com/sharing/share-offsite/?url={url}',
  pinterest: 'https://pinterest.com/pin/create/button/?url={url}&description={title}',
  reddit: 'https://reddit.com/submit?url={url}&title={title}',
  telegram: 'https://t.me/share/url?url={url}&text={title}',
  whatsapp: 'https://api.whatsapp.com/send?text={title}%20{url}',
  email: 'mailto:?subject={title}&body={text}%20{url}'
});
function giftflowCopyUrlToClipboard(url, button) {
  var container = button.closest('.giftflow-share');
  if (container && container._giftflowShare) {
    container._giftflowShare.copyToClipboard(url);
  } else {
    // Standalone copy
    var tempShare = new GiftFlowShare(container || document.body);
    tempShare.copyToClipboard(url);
  }
}

// Auto-initialize on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function () {
  window.giftflowShareBlocks = GiftFlowShare.initAll('.giftflow-share');

  // Store instance reference on elements
  window.giftflowShareBlocks.forEach(function (instance) {
    if (instance.container) {
      instance.container._giftflowShare = instance;
    }
  });
});

// Export for module systems
if ( true && module.exports) {
  module.exports = GiftFlowShare;
}

// Make available globally
window.GiftFlowShare = GiftFlowShare;

/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js":
/*!*********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/arrayLikeToArray.js ***!
  \*********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
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

/***/ "./node_modules/@babel/runtime/helpers/esm/arrayWithHoles.js":
/*!*******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/arrayWithHoles.js ***!
  \*******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _arrayWithHoles)
/* harmony export */ });
function _arrayWithHoles(r) {
  if (Array.isArray(r)) return r;
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

/***/ "./node_modules/@babel/runtime/helpers/esm/iterableToArrayLimit.js":
/*!*************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/iterableToArrayLimit.js ***!
  \*************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _iterableToArrayLimit)
/* harmony export */ });
function _iterableToArrayLimit(r, l) {
  var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"];
  if (null != t) {
    var e,
      n,
      i,
      u,
      a = [],
      f = !0,
      o = !1;
    try {
      if (i = (t = t.call(r)).next, 0 === l) {
        if (Object(t) !== t) return;
        f = !1;
      } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0);
    } catch (r) {
      o = !0, n = r;
    } finally {
      try {
        if (!f && null != t["return"] && (u = t["return"](), Object(u) !== u)) return;
      } finally {
        if (o) throw n;
      }
    }
    return a;
  }
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/nonIterableRest.js":
/*!********************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/nonIterableRest.js ***!
  \********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _nonIterableRest)
/* harmony export */ });
function _nonIterableRest() {
  throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.");
}


/***/ }),

/***/ "./node_modules/@babel/runtime/helpers/esm/slicedToArray.js":
/*!******************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/slicedToArray.js ***!
  \******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ _slicedToArray)
/* harmony export */ });
/* harmony import */ var _arrayWithHoles_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./arrayWithHoles.js */ "./node_modules/@babel/runtime/helpers/esm/arrayWithHoles.js");
/* harmony import */ var _iterableToArrayLimit_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./iterableToArrayLimit.js */ "./node_modules/@babel/runtime/helpers/esm/iterableToArrayLimit.js");
/* harmony import */ var _unsupportedIterableToArray_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./unsupportedIterableToArray.js */ "./node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js");
/* harmony import */ var _nonIterableRest_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./nonIterableRest.js */ "./node_modules/@babel/runtime/helpers/esm/nonIterableRest.js");




function _slicedToArray(r, e) {
  return (0,_arrayWithHoles_js__WEBPACK_IMPORTED_MODULE_0__["default"])(r) || (0,_iterableToArrayLimit_js__WEBPACK_IMPORTED_MODULE_1__["default"])(r, e) || (0,_unsupportedIterableToArray_js__WEBPACK_IMPORTED_MODULE_2__["default"])(r, e) || (0,_nonIterableRest_js__WEBPACK_IMPORTED_MODULE_3__["default"])();
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

/***/ "./node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js":
/*!*******************************************************************************!*\
  !*** ./node_modules/@babel/runtime/helpers/esm/unsupportedIterableToArray.js ***!
  \*******************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
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