
<h2>LSS Custom Code Options</h2>

<p>None yet!</p>

<form method="post" action="options.php">
    <?php
    	//pass slug name of page, also referred to in Settings API as option group name
    	settings_fields( 'lss-custom-code-settings-group' );
    	do_settings_sections( 'lss-custom-code-settings-group' );
    ?>
    <!-- you can define fields via the 2 functions above, or you can place them as they are below: -->

	<table class="form-table">
		<tr>
			<th scope="row">Option Test</th>
			<td><input type="text" name="lss_option_test" value="<?php echo get_option('lss_option_test'); ?>" /></td>
		</tr>
	</table>
    
    <?php submit_button(); ?>

</form>
