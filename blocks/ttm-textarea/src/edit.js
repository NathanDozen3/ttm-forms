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
    InspectorControls,
} from '@wordpress/block-editor';


import {
	PanelBody,
	TextControl,
	ToggleControl
} from '@wordpress/components';

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
export default function Edit( { clientId, attributes, setAttributes } ) {

    const { label, placeholder, sronly } = attributes;
	const className = sronly ? 'sr-only' : '';
	const name =label.trim().replaceAll(":", "").toLowerCase();

	const parentClientId = select( 'core/block-editor' ).getBlockHierarchyRootClientId(clientId);
	const parentAttributes = select('core/block-editor').getBlockAttributes( parentClientId );
	const parentID = String( parentAttributes.post_id ?? (parentAttributes.ref ?? '') );

	setAttributes( { parentID: parentID } );

	return (
		<div { ...useBlockProps() }>
			<InspectorControls key="setting">
				<PanelBody
					title = {__( 'Settings', 'ttm-form' ) }
					initialOpen = { true }
				>
					<fieldset>
						<TextControl
							label="Input Label"
							value={ label }
							onChange={ ( value ) => setAttributes( { label: value } ) }
						/>
						<ToggleControl
							label="Screen Reader Only"
							help={
								sronly
									? 'Only shown to screen readers.'
									: 'Shown to everyone.'
							}
							checked={ sronly }
							onChange={ ( value ) => setAttributes( { sronly: value }  ) }
						/>
						<TextControl
							label="Placeholder"
							value={ placeholder }
							onChange={ ( value ) => setAttributes( { placeholder: value } ) }
						/>
					</fieldset>
				</PanelBody>
			</InspectorControls>
			<label class={className} for={name}>{label}</label>
			<textarea id={name} name={name}  placeholder={placeholder} disabled />
		</div>
	);
}
