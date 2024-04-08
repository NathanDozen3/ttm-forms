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
	TextareaControl
} from '@wordpress/components';

import RepeaterControl from './RepeaterControl';

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

    const { args, name, url } = attributes;

	return (
		<div { ...useBlockProps() }>
			<InspectorControls key="setting">
				<PanelBody
					title = {__( 'Settings', 'ttm-form' ) }
					initialOpen = { true }
				>
					<fieldset>
						<TextControl
							label="Webhook Name"
							value={ name }
							onChange={ ( value ) => setAttributes( { name: value } ) }
						/>
						<TextControl
							label="Webhook Endpoint"
							value={ url }
							onChange={ ( value ) => setAttributes( { url: value } ) }
						/>
						<TextareaControl
							style={ {visibility: "hidden", height: 0 }}
							label="Webhook Arguments"
							value={ args }
							onChange={ ( value ) => setAttributes( { args: value } ) }
						/>
					</fieldset>
					<fieldset>
						<RepeaterControl
							saveElement="args"
						>
							<input type="text" name="name" label="Name"/>
							<input type="text" name="value" label="Value"/>
						</RepeaterControl>
					</fieldset>
				</PanelBody>
			</InspectorControls>

			<div>
				<p>Webhook</p>
				<input type="text" name={name} value={name} placeholder="Webhook Name" disabled/ >
				<input type="text" name={"webhooks[" + name + "][url]"} value={url} placeholder="Webhook URL" disabled/ >
				<input type="text" name={"webhooks[" + name + "][args]"} value={args} placeholder="Webhook Arguments" disabled/ >
			</div>
		</div>
	);
}
