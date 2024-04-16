const { Button } = wp.components
import { dispatch } from '@wordpress/data';
const { updateBlockAttributes } = dispatch( 'core/block-editor' );
import { cloneElement } from 'react';

const RepeaterControl = ( props ) => {

	const {saveElement} = props;
	const selectedBlock = wp.data.select( 'core/block-editor' ).getSelectedBlock();

	let repeaterValues = selectedBlock.attributes[saveElement];
	if( ! repeaterValues ) {
		repeaterValues = '[]';
	}
	repeaterValues = JSON.parse( repeaterValues );

	let children = props.children;

	return <>
		{ repeaterValues.map( (row, index) => {

			let useChildren = [];

			children.map( (e,i) => {

				const child = cloneElement( children[i] );
				useChildren.push(child);

				useChildren[i].props.value = repeaterValues[index][e.props.name];

				useChildren[i].props.onChange = function( value ) {
					e.props.value = value;
					repeaterValues[index][e.props.name] = value;
					let newVal = {};
					newVal[saveElement] = JSON.stringify(repeaterValues);
					updateBlockAttributes(selectedBlock.clientId,newVal);
				}
			});

			return <div className="repeater-item" style={ { marginTop:"1rem", marginBottom:"1rem" } } >
				{useChildren}
				{
					<Button isLink isDestructive onClick={ () => {
						repeaterValues = repeaterValues.filter((obj,loopIndex) => loopIndex !== index)
						let newVal = {};
						newVal[saveElement] = JSON.stringify(repeaterValues);
						updateBlockAttributes(selectedBlock.clientId,newVal);
					}}>
						Remove
					</Button>
				}
			</div>
		} ) }
		<div>
			<Button
				variant="secondary"
				onClick={() => {
					repeaterValues.push({})
					repeaterValues = repeaterValues.splice(0)
					let newVal = {};
					newVal[saveElement] = JSON.stringify(repeaterValues);
					updateBlockAttributes(selectedBlock.clientId,newVal);
				} }>Add Item
			</Button>
		</div>
	</>
}

export {RepeaterControl};
