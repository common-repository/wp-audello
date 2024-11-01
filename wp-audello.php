<?php
/*
Plugin Name: WP-Audello (Audello WordPress plugin)
Plugin URI: https://audello.com/
Description: Insert audio from the powerful internet marketer's podcasting and audio solution, Audello.
Date: 2016, August, 11
Author: WebActix
Author URI: http://webactix.com
Version: 1.0.1
*/

// Helpers
include('helpers/extract_tags.php');

// Admin area things - including settings and editor integrations
include('admin/admin.php');
include('integrate_tinymce.php');
include('integrate_basic.php');
include('integrate_audiobar.php');

// Include the widget things
//include('widgets/audio-widget/widget.php');

// Set up oEmbed?
$audello_location = get_option('audello_location');
$audello_oembed_support = get_option('audello_oembed_support');
if(!empty($audello_location) && ($audello_oembed_support == 'true' || $audello_oembed_support === true)) {
	wp_oembed_add_provider($audello_location.'/*', $audello_location.'/oembed.php', false);
}

// Add our CSS file
function wp_audello_addcss() {
	wp_register_style('wp-audello-css', plugins_url('/assets/audello.css', __FILE__));
	wp_enqueue_style('wp-audello-css');
}
//add_action('wp_enqueue_scripts', 'wp_audello_addcss');

// Now the actual filter to convert
if(!function_exists('wp_audello_filter')) {
	function wp_audello_filter($content) {
		$images = wp_audello_extract_tags($content, 'img', null, true);

		if(!empty($images)) {
			foreach($images as $img) {
				$alt = $img['attributes']['alt'];
				$evscode = '';

				if(!empty($alt)) {
					$evscode = @base64_decode($alt);
				}

				if(stripos($evscode, '<div id="audello') !== false) { // If it's got Audello code..
					$evsprefix = '';
					$evssuffix = '';

					if(stripos($img['full_tag'], 'aligncenter')) {
						$evsprefix = '<center>';
						$evssuffix = '</center>';
					}

					$evscode = str_replace(' src="', ' data-cfasync="false" src="', $evscode); // Add the data-cfasync tag to prevent CloudFlare's Rocker Loader from breaking us!

					$content = str_replace($img['full_tag'], $evsprefix.$evscode.$evssuffix, $content);
				}
			}
		}

		return $content;
	}
}

add_filter('the_content', 'wp_audello_filter', 9999);
add_filter('the_excerpt', 'wp_audello_filter', 9999);

add_filter('the_content', 'wp_audello_filter');
add_filter('the_excerpt', 'wp_audello_filter');
?>
