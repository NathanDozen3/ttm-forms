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

import Settings from '../../settings.js';

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
export default function Edit( { context, clientId, attributes, setAttributes } ) {

    const { label } = attributes;
	const slug = label.trim().replaceAll(":", "").replaceAll(" ", "-").toLowerCase();
	const name = context[ 'ttm-form/label' ].trim().replaceAll(":", "").replaceAll(" ", "-").toLowerCase();

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
	if(name != '') {
		setAttributes( { name: name } )
	}

	return (
		<div { ...useBlockProps() }>
			<Settings
				attributes={attributes}
				setAttributes={setAttributes}
				settings={{
					required: false,
					sronly: false,
					placeholder: false
				}}
			/>
			<label for={name + "-" + slug}>{label}</label>
			<input type="checkbox" id={name + "-" + slug} name={name + "[]"} value={slug} disabled></input>
		</div>
	);
}
