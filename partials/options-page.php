<?php
namespace ttm\forms;
?>
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<h2 class="description"></h2>
	<form method="post">
		<input type="hidden" name="page" value="<?php echo $_REQUEST[ 'page' ]; ?>"/>
		<?php
		$table = new TTM_Forms_List_Table();
		$table->prepare_items();
		$table->display();
		?>
	</form>
</div>
