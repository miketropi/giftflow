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
