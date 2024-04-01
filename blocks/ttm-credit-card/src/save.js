/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */
export default function save( { attributes } ) {
    const { parentID } = attributes;

	const name = "credit-card";

    return (
        <div { ...useBlockProps.save() }>
            <label class="sr-only" for={parentID + "_" + name}>Credit Card Number</label>
			<input class="wp-block-ttm-credit-card--number" type="text" id={parentID + "_" + name} name={name + "_number"} placeholder="4012 8888 8888 1881"></input>

			<div class="wp-block-ttm-credit-card--flex">
				<label class="sr-only" for={parentID + "_" + name + "_cvv"}>Credit Card CVV</label>
				<input class="wp-block-ttm-credit-card--cvv" type="text" id={parentID + "_" + name + "_cvv"} name={name + "_cvv"} placeholder="CVV"></input>
				<label class="sr-only" for={parentID + "_" + name + "_zip"}>Credit Card ZIP</label>
				<input class="wp-block-ttm-credit-card--zip" type="text" id={parentID + "_" + name + "_zip"} name={name + "_zip"} placeholder="Zip"></input>
			</div>
        </div>
    );
}
