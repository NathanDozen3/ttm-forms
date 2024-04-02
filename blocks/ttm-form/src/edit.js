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
	InspectorControls,
} from '@wordpress/block-editor';

import {
	PanelBody,
	TextControl,
	ToggleControl,
	TextareaControl,
} from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

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

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {

	const blockProps = useBlockProps();
	const allowedBlocks = [
		"core/heading",
		"ttm/columns",
		"ttm/input-checkbox",
		"ttm/input-date",
		"ttm/input-email",
		"ttm/input-hidden",
		"ttm/input-password",
		"ttm/input-radio",
		"ttm/input-submit",
		"ttm/input-tel",
		"ttm/input-text",
		"ttm/textarea"
	];
	const { to, subject, thankYouLink } = attributes;
	let { post_id } = attributes;

	post_id = String(post_id);
	if( ! post_id.startsWith( 'block' ) ) {
		post_id = blockProps.id;
		setAttributes( { post_id: post_id } )
	}

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
						<TextControl
							label="Thank You Link"
							value={ thankYouLink }
							onChange={ ( value ) => setAttributes( { thankYouLink: value }  ) }
						/>
					</fieldset>
				</PanelBody>
			</InspectorControls>
			{ ( ! to || ! subject ) && <p>Make sure to set the to and subject in the form settings.</p>}
			{ ( to && subject ) && <InnerBlocks allowedBlocks={ allowedBlocks } /> }
		</div>
	);
}
