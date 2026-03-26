export const replaceContentBySelector = (selector, content) => {
  const elem = document.querySelector(selector);
  if (elem) {
    elem.innerHTML = content;
  } else {
    console.error(`Element not found for selector: ${selector}`);
  }
}

/**
 * Apply a slideDown or slideUp effect to a DOM element.
 * @param {HTMLElement} dom - The target element.
 * @param {'slidedown'|'slideup'} effect - The effect type.
 * @param {number} duration - Duration in ms. Default: 300
 * @param {string} displayType - The display style to use (e.g., 'block', 'grid'). Default: 'block'
 */
export function applySlideEffect(dom, effect = 'slidedown', duration = 300, displayType = 'block') {
  if (!dom) return;

  if (!['slidedown', 'slideup'].includes(effect)) {
    console.error('Invalid effect:', effect);
    return;
  }

  dom.style.overflow = 'hidden';

  if (effect === 'slidedown') {
    dom.style.display = displayType;
    let height = dom.scrollHeight;
    dom.style.height = '0px';

    // force reflow to ensure setting height is registered
    // eslint-disable-next-line no-unused-expressions
    dom.offsetHeight;

    dom.style.transition = `height ${duration}ms ease`;
    dom.style.height = height + 'px';

    const onEnd = () => {
      dom.style.display = displayType;
      dom.style.height = '';
      dom.style.overflow = '';
      dom.style.transition = '';
      dom.removeEventListener('transitionend', onEnd);
    };

    dom.addEventListener('transitionend', onEnd);
  } else if (effect === 'slideup') {
    // Remember current display style in case we want to restore it
    let prevDisplay = dom.style.display;

    let height = dom.scrollHeight;
    dom.style.height = height + 'px';

    // force reflow
    // eslint-disable-next-line no-unused-expressions
    dom.offsetHeight;

    dom.style.transition = `height ${duration}ms ease`;
    dom.style.height = '0px';

    const onEnd = () => {
      dom.style.display = 'none';
      dom.style.height = '';
      dom.style.overflow = '';
      dom.style.transition = '';
      dom.removeEventListener('transitionend', onEnd);
      // Optionally restore previous style if needed in future
    };

    dom.addEventListener('transitionend', onEnd);
  }
}

/**
 * Validate a value according to given validation types.
 * @param {string} type - Comma-separated string of validation types, e.g. "required,email".
 * @param {any} value - Value to validate.
 * @param {Object|null} extraData - Optional extra data for some validations (min/max).
 * @returns {boolean} - True if passes all validations, false otherwise.
 */
export function validateValue(type, value, extraData = null) {
  // Accept multiple comma-delimited validation types, pass if all pass
  const types = type ? type.split(',').map(s => s.trim()) : [];
  let overallValid = true;

  for (let t of types) {
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
        const __min = parseInt(extraData?.min || 0, 10);
        if (value < __min || value === '') overallValid = false;
        break;

      // max
      case 'max':
        const __max = parseInt(extraData?.max || 0, 10);
        if (value > __max || value === '') overallValid = false;
        break;

      // default: always pass unknown validators
      default:
        // do nothing
        break;
    }
    if (!overallValid) break; // stop on first failure
  }

  return overallValid;
}

export function createElementFromTemplate(template) {
  const div = document.createElement('div');
  div.innerHTML = template;
  return div.children[0] || null;
}

/** @type {WeakMap<Document|HTMLElement, Set<string>>} */
const clickToCopyRoots = new WeakMap();

/**
 * Copy string to clipboard (Clipboard API when available, else execCommand fallback).
 * @param {string} text
 * @returns {Promise<boolean>}
 */
async function copyTextToClipboard(text) {
  if (!text) return false;
  try {
    if (navigator.clipboard && window.isSecureContext) {
      await navigator.clipboard.writeText(text);
      return true;
    }
  } catch {
    // fall through to legacy
  }
  const ta = document.createElement('textarea');
  ta.value = text;
  ta.setAttribute('readonly', '');
  ta.style.position = 'fixed';
  ta.style.left = '-9999px';
  ta.style.top = '0';
  document.body.appendChild(ta);
  ta.select();
  ta.setSelectionRange(0, text.length);
  let ok = false;
  try {
    ok = document.execCommand('copy');
  } catch {
    ok = false;
  }
  document.body.removeChild(ta);
  return ok;
}

/**
 * Short visual feedback after a successful copy (Web Animations API).
 * @param {HTMLElement} element
 */
function playCopySuccessEffect(element) {
  if (typeof element.animate !== 'function') return;
  const computed = window.getComputedStyle(element);
  const fromBg = computed.backgroundColor || 'transparent';
  element.animate(
    [
      { backgroundColor: fromBg, transform: 'scale(1)' },
      { backgroundColor: 'rgba(0, 255, 247, 0.48)', transform: 'scale(1.01)' },
      { backgroundColor: fromBg, transform: 'scale(1)' },
    ],
    { duration: 450, easing: 'ease-out' }
  );
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
export function initClickToCopyByClass(options) {
  if (!options || typeof options.className !== 'string') {
    console.warn('initClickToCopyByClass: `className` (string) is required');
    return;
  }

  const cls = options.className.replace(/^\./, '').trim().split(/\s+/)[0];
  if (!cls) {
    console.warn('initClickToCopyByClass: empty className');
    return;
  }

  const root = options.root && options.root.nodeType ? options.root : document;
  const successClass = options.successClass ?? 'gfw-click-to-copy--copied';
  const successDuration = typeof options.successDuration === 'number' ? options.successDuration : 1500;
  const tooltipLabel =
    typeof options.tooltipLabel === 'string' && options.tooltipLabel
      ? options.tooltipLabel
      : (typeof window !== 'undefined' &&
          window.giftflow_common &&
          typeof window.giftflow_common.click_to_copy_tooltip === 'string' &&
          window.giftflow_common.click_to_copy_tooltip) ||
        'Click to copy';

  let set = clickToCopyRoots.get(root);
  if (!set) {
    set = new Set();
    clickToCopyRoots.set(root, set);
  }
  if (set.has(cls)) return;
  set.add(cls);

  const selector = `.${CSS.escape(cls)}`;

  root.addEventListener(
    'pointerover',
    (e) => {
      const target = e.target;
      if (!(target instanceof Element)) return;

      const el = target.closest(selector);
      if (!el || !root.contains(el)) return;
      if (el.hasAttribute('data-no-copy-tooltip')) return;
      if (el.hasAttribute('data-copy-tooltip')) return;

      el.setAttribute('data-copy-tooltip', tooltipLabel);
    },
    true
  );

  root.addEventListener(
    'click',
    (e) => {
      const target = e.target;
      if (!(target instanceof Element)) return;

      const el = target.closest(selector);
      if (!el || !root.contains(el)) return;

      const explicit =
        el.getAttribute('data-copy') ||
        el.getAttribute('data-copy-text') ||
        '';
      const text = (explicit || el.textContent || '').trim();
      if (!text) return;

      e.preventDefault();

      copyTextToClipboard(text).then((ok) => {
        if (!ok) return;

        el.classList.add(successClass);
        playCopySuccessEffect(el);

        const prev = el._gfwCopySuccessTimer;
        if (prev) clearTimeout(prev);
        el._gfwCopySuccessTimer = setTimeout(() => {
          el.classList.remove(successClass);
          el._gfwCopySuccessTimer = undefined;
        }, successDuration);
      });
    },
    false
  );
}

