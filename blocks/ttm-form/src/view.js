/**
 * Use this file for JavaScript code that you want to run in the front-end
 * on posts/pages that contain this block.
 *
 * When this file is defined as the value of the `viewScript` property
 * in `block.json` it will be enqueued on the front end of the site.
 *
 * Example:
 *
 * ```js
 * {
 *   "viewScript": "file:./view.js"
 * }
 * ```
 *
 * If you're not making any changes to this file because your project doesn't need any
 * JavaScript running in the front-end, then you should delete this file and remove
 * the `viewScript` property from `block.json`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/#view-script
 */

(function(){

	const isExternalURL = (url) => new URL(url).origin !== location.origin;

	const getParentByTagName = function(node, tagname) {
		var parent;
		if (node === null || tagname === '') return;
		parent  = node.parentNode;
		tagname = tagname.toUpperCase();

		while (parent.tagName !== "HTML") {
			if (parent.tagName === tagname) {
				return parent;
			}
			parent = parent.parentNode;
		}

		return parent;
	}

	String.prototype.hashCode = function() {
		var hash = 0,
		  i, chr;
		if (this.length === 0) return hash;
		for (i = 0; i < this.length; i++) {
		  chr = this.charCodeAt(i);
		  hash = ((hash << 5) - hash) + chr;
		  hash |= 0; // Convert to 32bit integer
		}
		return hash;
	  }

	let ids = document.querySelectorAll("[name='post_id']");
	ids.forEach(id => { id.setAttribute('value', ttm_post_id);});

	let inputs = document.querySelectorAll(".wp-block-ttm-form input, .wp-block-ttm-form textarea");

	let r = Math.floor(Math.random() * Number.MAX_SAFE_INTEGER);

	document.querySelectorAll(".wp-block-ttm-form form").forEach(form => {
		form.dataset.partial = r + "-" + form.innerHTML.hashCode();

		var input = document.createElement("input");
		input.setAttribute("type", "hidden");
		input.setAttribute("name", "partial");
		input.setAttribute("value", form.dataset.partial);
		form.appendChild(input);
	});

	inputs.forEach(input => {
		input.addEventListener( 'input', function(e) {
			let parentForm = getParentByTagName(this,'form');
			let els = parentForm.elements;

			let a = {
				partial: r + "-" + parentForm.innerHTML.hashCode(),
			};

			for (const el of els) {
				if (
					el.type == 'submit' ||
					el.name == 'g-recaptcha-response' ||
					el.name.startsWith('credit-card')
				) {
					continue;
				}
				a[ el.name ] = el.value;
			}
			let url = document.location.origin + '/wp-json/ttm-forms/v1/partial';

			const credentials = isExternalURL(url) ? 'same-origin' : 'omit';
			fetch(url, {
				cache: 'no-store',
				credentials: credentials,
				method: 'POST',
				redirect: 'follow',
				headers: {
					'Accept': 'application/json',
					'Content-Type': 'application/json',
				},
				body: JSON.stringify(a)
			})
			.then((response) => {
				if (!response.ok) {
					throw new Error(`HTTP error! Status: ${response.status}`);
				}
				return response.json();
			})
			.then((response) => {
			});
		});
	});
})();
