/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {
    useBlockProps,
} from '@wordpress/block-editor';

import { select } from "@wordpress/data";

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit( { clientId, setAttributes } ) {

	const name = "credit-card";

	const parentClientId = select( 'core/block-editor' ).getBlockHierarchyRootClientId(clientId);
	const parentAttributes = select('core/block-editor').getBlockAttributes( parentClientId );

	let parentID = '';
	if (parentAttributes != null) {
		if ('post_id' in parentAttributes) {
			parentID = String( parentAttributes.post_id );
		}
		else if ('ref' in parentAttributes) {
			parentID = String( parentAttributes.ref );
		}
	}

	if(parentID != '') {
		setAttributes( { parentID: parentID } )
	}

	return (
		<div { ...useBlockProps() }>
			<label class="sr-only" for={parentID + "_" + name}>Credit Card Number</label>
			<input class="wp-block-ttm-credit-card--number" type="text" id={parentID + "_" + name} name={name + "_number"} placeholder="4012 8888 8888 1881" disabled></input>

			<div class="wp-block-ttm-credit-card--flex">
				<label class="sr-only" for={parentID + "_" + name + "_cvv"}>Credit Card CVV</label>
				<input class="wp-block-ttm-credit-card--cvv" type="text" id={parentID + "_" + name + "_cvv"} name={name + "_cvv"} placeholder="CVV" disabled></input>
				<label class="sr-only" for={parentID + "_" + name + "_zip"}>Credit Card ZIP</label>
				<input class="wp-block-ttm-credit-card--zip" type="text" id={parentID + "_" + name + "_zip"} name={name + "_zip"} placeholder="Zip" disabled></input>
			</div>
		</div>
	);
}
