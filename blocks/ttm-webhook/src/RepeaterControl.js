const { TextControl, Button } = wp.components
import { dispatch } from '@wordpress/data';
const { updateBlockAttributes } = dispatch( 'core/block-editor' );

const RepeaterControl = ( props ) => {

	const {saveElement} = props;

	const selectedBlock = wp.data.select( 'core/block-editor' ).getSelectedBlock();
	let repeaterValues = JSON.parse(wp.data.select( 'core/block-editor' ).getSelectedBlock().attributes[saveElement]);

	return <>
		{ repeaterValues.map( (row, index) => {
			return <div style={{ display: 'flex', gap: '1rem' }}>
				<TextControl
					label="Name"
					value={ row.name }
					onChange={ (value) => {
						repeaterValues[index].name = value;
						updateBlockAttributes(selectedBlock.clientId,{ args: JSON.stringify(repeaterValues)});
					} }
				/>
				<TextControl
					label="Value"
					value={ row.value }
					onChange={ (value) => {
						repeaterValues[index].value = value;
						updateBlockAttributes(selectedBlock.clientId,{ args: JSON.stringify(repeaterValues)});
					} }
				/>
				{
					<Button isLink isDestructive onClick={ () => {
						repeaterValues = repeaterValues.filter((obj,loopIndex) => loopIndex !== index)
						updateBlockAttributes(selectedBlock.clientId,{ args: JSON.stringify(repeaterValues)});
					}}>
						Remove
					</Button>
				}
			</div>
		} ) }
		<Button
			variant="secondary"
			onClick={() => {
				repeaterValues.push({})
				repeaterValues = repeaterValues.splice(0)
				updateBlockAttributes(selectedBlock.clientId,{ args: JSON.stringify(repeaterValues)});
			} }>Add Item
		</Button>
	</>
}

export default RepeaterControl
