<fieldset>
  <legend><?php echo $args[ 'args' ][ 'description' ] ?? ''; ?></legend>
  <?php foreach( $args[ 'args' ][ 'options' ] ?? [] as $key => $value ) : ?>
	<div>
		<input
			type="radio"
			id="<?php echo $key; ?>"
			name="ttm_forms[<?php echo esc_attr( $args[ 'id' ] ?: '' ); ?>]"
			value="<?php echo $key; ?>"
			<?php checked( $key, $args[ 'value' ] ); ?>
		/>
		<label for="<?php echo $key; ?>"><?php echo $value; ?></label>
	</div>
  <?php endforeach; ?>
</fieldset>
