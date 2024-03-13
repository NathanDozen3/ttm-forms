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
	InnerBlocks,
	useBlockProps,
	InspectorControls
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
export default function Edit({attributes, setAttributes}) {
	const blockProps = useBlockProps();
	const allowedBlocks = [
		"ttm/input-date",
		"ttm/input-email",
		"ttm/input-hidden",
		"ttm/input-password",
		"ttm/input-tel",
		"ttm/input-text",
		"ttm/textarea"
	];
	const { to, subject } = attributes;
	return (
		<div { ...blockProps }>
			<InspectorControls key="setting">
				<PanelBody
					title = {__( 'Settings', 'ttm-form' ) }
					initialOpen = { true }
				>
					<fieldset>
						<TextControl
							label="To"
							value={ to }
							onChange={ ( value ) => setAttributes( { to: value } ) }
						/>
						<TextControl
							label="Subject"
							value={ subject }
							onChange={ ( value ) => setAttributes( { subject: value } ) }
						/>
					</fieldset>
				</PanelBody>
			</InspectorControls>
			<InnerBlocks allowedBlocks={ allowedBlocks } />
			<button disabled>Submit</button>
		</div>
	);
}
