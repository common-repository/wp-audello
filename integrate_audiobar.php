<?php
function wp_audello_sitewide_audiobar() {
	$embed = get_option('audello_sitewide_audiobar_code');
	if(!empty($embed)) echo $embed;
}
add_action('wp_footer', 'wp_audello_sitewide_audiobar', 100000);
?>