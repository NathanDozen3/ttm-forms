<?php namespace ttm\forms; ?>
<?php $atts = [
	'name' => 'name',
	'label' => 'Name:',
	'type' => 'text',
]; ?>

<div <?php echo get_block_wrapper_attributes(); ?>>
	<label for="<?php echo $atts[ 'name' ]; ?>"><?php echo $atts[ 'label' ]; ?></label>
	<input type="<?php echo $atts[ 'type' ]; ?>" id="<?php echo $atts[ 'name' ]; ?>" name="<?php echo $atts[ 'name' ]; ?>">
</div>
