<label for="<?php echo esc_attr( $args[ 'id' ] ?: '' ); ?>"><?php echo esc_attr( $args[ 'name' ] ?: '' ); ?></label>
<input
	type="checkbox"
	id="<?php echo esc_attr( $args[ 'id' ] ?: '' ); ?>"
	name="ttm_forms[<?php echo esc_attr( $args[ 'id' ] ?: '' ); ?>]"
	class="ttm-forms--checkbox"
	<?php echo $args[ 'value' ] === 'on' ? 'checked' : ''; ?>
	<?php
	foreach( $args[ 'dataset' ] ?? [] as $key => $val ) {
		if( $val ) {
			$dataset[ $key ] = $val;
			printf( 'data-%1$s="%2$s"', $key, $val );
		}
	}
	?>
>
