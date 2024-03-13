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
	SelectControl,
	TextControl
} from '@wordpress/components';

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
export default function Edit( { attributes, setAttributes } ) {

    const { label } = attributes;

	let name =label.trim().replaceAll(":", "").toLowerCase();

	return (
		<div { ...useBlockProps() }>
			<InspectorControls key="setting">
				<PanelBody
					title = {__( 'Settings', 'ttm-form' ) }
					initialOpen = { true }
				>
					<fieldset>
						<TextControl
							label="Label"
							value={ label }
							onChange={ ( value ) => setAttributes( { label: value } ) }
						/>
					</fieldset>
				</PanelBody>
			</InspectorControls>
			<label for={name}>{label}</label>
			<input type="tel" id={name} name={name} disabled></input>
		</div>
	);
}
