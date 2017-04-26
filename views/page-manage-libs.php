<?php
	$libs = $this->getLibs();
?>
<div class="wrap">
	<?php settings_errors(); ?>

	<h1><?php echo esc_html( 'Manage libs', 'qoob' ); ?></h1>
	<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
		<div id="qoob-libs-container">
			<table class="wp-list-table widefat striped fix">
				<thead>
					<tr>
						<th><?php _e( 'URL', 'qoob' ); ?></th>
						<th><?php _e( 'Name', 'qoob' ); ?></th>
						<th><?php _e( 'Action', 'qoob' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($libs as $lib) { ?>
						<tr>
							<td><?php echo $lib['url']; ?></td>
							<td><?php echo $lib['name']; ?></td>
							<td>
								<?php if (isset($lib['external'])) : ?>
									<a href="<?php menu_page_url( 'qoob-manage-libs' ); ?>&action=remove&lib_url=<?php echo urlencode_deep( $lib['url'] ); ?>" title="<?php __( 'Delete', 'qoob' ) ?>"><span class="dashicons dashicons-trash"></span></a>
								<?php endif; ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
		</div>
	</form>

	<h2><?php echo esc_html( 'Choose how to add a Qoob library', 'qoob' ); ?></h2>

	<form enctype="multipart/form-data" id="qoob-filters" method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
		<p><label><input type="radio" name="radio" checked="checked" value="url" /> <?php _e( 'Add a Qoob library', 'qoob' ); ?></label></p>
		<ul id="qoob-url" class="qoob-filters">
			<li>
				<input type="url" class="regular-text" name="lib_url" placeholder="<?php echo esc_html( 'Json url', 'qoob' ); ?>" value="" />
			</li>
		</ul>

		<p><label><input type="radio" name="radio" value="file" /> <?php _e( 'Upload a Qoob library', 'qoob' ); ?></label></p>
		<ul id="qoob-file" class="qoob-filters">
			<li>
				<input name="lib_file" type="file" />
			</li>
		</ul>
		<input type="hidden" name="action" value="qoob_add_library">
		<input type="hidden" name="_wp_http_referer" value="<?php echo admin_url( 'admin.php?page=qoob-manage-libs' ) ?>" />
		<?php 
			wp_nonce_field( 'qoob_add_lib', '_wpnonce', false );
			submit_button(esc_html( 'Add library', 'qoob' ));
		?>
	</form>

</div><!-- .wrap -->

