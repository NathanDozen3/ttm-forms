/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, useInnerBlocksProps } from '@wordpress/block-editor';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */
export default function save({ attributes }) {
	const blockProps = useBlockProps.save();
	const innerBlocksProps = useInnerBlocksProps.save();

    const { to, subject } = attributes;

	return (
		<div { ...blockProps }>
            <form method="post">
                { innerBlocksProps.children }
                <input type="hidden" id="ttm_form" name="ttm_form" value="1"/>
                <input type="hidden" id="to" name="to" value={to}/>
                <input type="hidden" id="subject" name="subject" value={subject}/>
                <input type="submit" value="Submit"></input>
            </form>
		</div>
	);
}
