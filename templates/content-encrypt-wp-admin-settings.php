<?php
/**
 * @var $option_group string
 * @var $prefix string
 */
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	<form action="options.php" method="post">
		<?php
		// output security fields for the registered setting "wporg"
		settings_fields( $option_group );
		// output setting sections and their fields
		// (sections are registered for "wporg", each field is registered to a specific section)
		do_settings_sections( $option_group );

		?>
		<table class="form-table">
			<tbody>
			<tr class="encrypt-wp_settings_row">
				<th scope="row"><label for="strict_mode">Secure Database</label></th>
				<td>
					<button class="btn btn-warning" id="encrypt-all">ENCRYPT ALL FIELDS</button>
					<p class="description">
						This will encrypt the data in all of the sensitive fields in your database. This cannot be undone. Be sure to take a database backup first.
					</p>
				</td>
			</tr>
			</tbody>
		</table>
	</form>

	<?php submit_button( 'Save Settings' ); ?>
</div>
