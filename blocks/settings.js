import { __ } from '@wordpress/i18n';

import {
    InspectorControls,
} from '@wordpress/block-editor';

import {
	PanelBody,
	TextControl,
	ToggleControl,
} from '@wordpress/components';

import React from "react";

/**
 *
 */
export default function Settings( { attributes, setAttributes, settings } ) {

	const { label, placeholder, required, sronly } = attributes;

	if( typeof settings == "undefined" ) {
		settings = {};
	}

	return (
		<InspectorControls>
			<PanelBody
				title = {__( 'Settings', 'ttm-form' ) }
				initialOpen = { true }
			>
				<fieldset>
					{ ( typeof settings.required == "undefined" || settings.required != false) &&
						<ToggleControl
							label="Required"
							checked={ required }
							onChange={ ( value ) => setAttributes( { required: value }  ) }
						/>
					}
					{ ( typeof settings.label == "undefined" || settings.label != false) &&
						<TextControl
							label="Label"
							value={ label }
							onChange={ ( value ) => setAttributes( { label: value } ) }
						/>
					}
					{ ( typeof settings.sronly == "undefined" || settings.sronly != false) &&
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
					}
					{ ( typeof settings.placeholder == "undefined" || settings.placeholder != false) &&
						<TextControl
							label="Placeholder"
							value={ placeholder }
							onChange={ ( value ) => setAttributes( { placeholder: value } ) }
						/>
					}
				</fieldset>
			</PanelBody>
		</InspectorControls>
	);
}
