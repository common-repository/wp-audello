<?php
// Load the custom TinyMCE buttons
function wp_audello_integrate_tinymce($buttons) {
	if(get_option('wp-audello-configured') == true && is_admin()) {
		array_push($buttons, 'wpaudello_remote');
	}
	return $buttons;
}
add_filter('mce_buttons', 'wp_audello_integrate_tinymce');

// Load the custom TinyMCE plugin
function wp_audello_integrate_tinymce_plugin($plugins) {
	if(get_option('wp-audello-configured') == true && is_admin()) {
		$plugins['wpaudello_remote'] = plugins_url('/wp-audello/scripts/remote_video.js');
	}
	return $plugins;
}
add_filter('mce_external_plugins', 'wp_audello_integrate_tinymce_plugin');

// Break the browser cache of TinyMCE
function wp_audello_integrate_tinymce_version($version) {
	return $version . '-wp-audello';
}
add_filter('tiny_mce_version', 'wp_audello_integrate_tinymce_version');

// Are we an admin and do we need to load things up?
add_action('admin_print_scripts', 'wp_audello_integrate_tinymce_admin');
function wp_audello_integrate_tinymce_admin() {
	if(is_admin()) {
		wp_enqueue_script('phpjs', plugins_url('/wp-audello/admin/scripts/phpjs.js'));
		$audello_settings = array(
			'subdomain' => get_option('audello_subdomain'),
			'lite' => (get_option('audello_lite') === 'true'),
			'username' => get_option('audello_username'),
			'password' => sha1(get_option('audello_password')),
			'permissions' => json_decode(stripslashes(get_option('audello_permissions')))
		);
		echo '<script type="text/javascript">';
		echo '(function() {';
		echo "window.WPAudelloSettings = ".json_encode($audello_settings);
		echo '})();';
		echo '</script>';
	}
	//}
}
?>