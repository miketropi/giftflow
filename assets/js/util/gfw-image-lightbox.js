/**
 * Fullscreen image lightbox for GiftFlow (PhotoSwipe-like UI, no `.pswp` / globals).
 *
 * @typedef {{ src: string, width?: string|number, height?: string|number }} GfwLightboxItem
 */

const NS = 'gfw-lightbox';

const SVG_PREV =
	'<svg class="' +
	NS +
	'__chev" width="32" height="32" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>';
const SVG_NEXT =
	'<svg class="' +
	NS +
	'__chev" width="32" height="32" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/></svg>';
const SVG_CLOSE =
	'<svg class="' +
	NS +
	'__icon-close" width="24" height="24" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>';

/**
 * @param {object} options
 * @param {GfwLightboxItem[]} options.items
 * @param {string} [options.closeLabel]
 * @param {string} [options.prevLabel]
 * @param {string} [options.nextLabel]
 */
export function createGiftflowLightbox(options) {
	const { items } = options;
	const closeLabel = options.closeLabel || 'Close';
	const prevLabel = options.prevLabel || 'Previous';
	const nextLabel = options.nextLabel || 'Next';

	if (!Array.isArray(items) || items.length === 0) {
		return {
			open() {},
			close() {},
			destroy() {},
		};
	}

	const multi = items.length > 1;

	let root = null;
	let index = 0;
	let lastFocus = null;

	function buildDom() {
		const el = document.createElement('div');
		el.className = NS;
		el.setAttribute('role', 'dialog');
		el.setAttribute('aria-modal', 'true');
		el.setAttribute('aria-label', 'Gallery');
		el.innerHTML = `
			<div class="${NS}__bg" role="presentation"></div>
			<div class="${NS}__ui">
				<div class="${NS}__toolbar">
					<div class="${NS}__counter" aria-live="polite"></div>
					<button type="button" class="${NS}__btn ${NS}__btn--close" aria-label="${escapeAttr(closeLabel)}">${SVG_CLOSE}</button>
				</div>
				${
					multi
						? `<button type="button" class="${NS}__btn ${NS}__btn--arrow ${NS}__btn--arrow--prev" aria-label="${escapeAttr(prevLabel)}">${SVG_PREV}</button>
				<button type="button" class="${NS}__btn ${NS}__btn--arrow ${NS}__btn--arrow--next" aria-label="${escapeAttr(nextLabel)}">${SVG_NEXT}</button>`
						: ''
				}
				<div class="${NS}__stage-wrap">
					<div class="${NS}__stage">
						<div class="${NS}__loader" aria-hidden="true">
							<span class="${NS}__spinner"></span>
						</div>
						<img class="${NS}__img" alt="" decoding="async" />
					</div>
				</div>
			</div>
		`;
		return el;
	}

	function escapeAttr(s) {
		return String(s)
			.replace(/&/g, '&amp;')
			.replace(/"/g, '&quot;')
			.replace(/</g, '&lt;');
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
		const stage = root.querySelector(`.${NS}__stage`);
		stage?.classList.toggle(`${NS}__stage--loading`, loading);
	}

	function render() {
		if (!root) {
			return;
		}
		const item = items[index];
		const img = root.querySelector(`.${NS}__img`);
		const counter = root.querySelector(`.${NS}__counter`);
		if (counter) {
			counter.textContent = `${index + 1} / ${items.length}`;
		}
		if (!img) {
			return;
		}

		setLoading(true);
		img.classList.remove(`${NS}__img--ready`);

		let settled = false;
		const finishOk = () => {
			if (settled) {
				return;
			}
			settled = true;
			setLoading(false);
			img.classList.add(`${NS}__img--ready`);
		};
		const finishErr = () => {
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

	function open(startIndex = 0) {
		index = Math.max(0, Math.min(startIndex | 0, items.length - 1));
		if (!root) {
			root = buildDom();
			document.body.appendChild(root);

			const bg = root.querySelector(`.${NS}__bg`);
			const btnClose = root.querySelector(`.${NS}__btn--close`);
			const prev = root.querySelector(`.${NS}__btn--arrow--prev`);
			const next = root.querySelector(`.${NS}__btn--arrow--next`);

			bg?.addEventListener('click', close);
			btnClose?.addEventListener('click', (e) => {
				e.stopPropagation();
				close();
			});
			prev?.addEventListener('click', (e) => {
				e.stopPropagation();
				go(-1);
			});
			next?.addEventListener('click', (e) => {
				e.stopPropagation();
				go(1);
			});
		}

		lastFocus = document.activeElement;
		render();
		root.hidden = false;
		root.classList.add(`${NS}--visible`);
		document.body.classList.add(`${NS}-open`);
		document.addEventListener('keydown', onKeydown);
		requestAnimationFrame(() => {
			root.querySelector(`.${NS}__btn--close`)?.focus();
		});
	}

	function close() {
		if (!root || root.hidden) {
			return;
		}
		root.classList.remove(`${NS}--visible`);
		root.hidden = true;
		document.body.classList.remove(`${NS}-open`);
		document.removeEventListener('keydown', onKeydown);
		const img = root.querySelector(`.${NS}__img`);
		if (img) {
			img.removeAttribute('src');
			img.classList.remove(`${NS}__img--ready`);
		}
		if (lastFocus && typeof lastFocus.focus === 'function') {
			lastFocus.focus();
		}
		lastFocus = null;
	}

	function destroy() {
		close();
		root?.remove();
		root = null;
	}

	return { open, close, destroy };
}
