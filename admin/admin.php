<?php
add_action('admin_menu', 'wp_audello_admin_menu');
add_filter('plugin_action_links', 'wp_audello_admin_settingslink', 10, 2);
add_action('admin_init', 'wp_audello_admin_init');

function wp_audello_admin_init() {
	wp_register_script('wp-audello-phpjs', plugins_url('scripts/phpjs.js', __FILE__));
	wp_register_script('wp-audello-spinner', plugins_url('scripts/spinner.js', __FILE__));
}

function wp_audello_admin_styles() {
	wp_enqueue_script('wp-audello-phpjs');
	wp_enqueue_script('wp-audello-spinner');
}

function wp_audello_admin_menu() {
	$page = add_options_page('Audello Options', 'Audello', 'manage_options', 'wp-audello', 'wp_audello_admin_options');
	add_action('admin_print_styles-' . $page, 'wp_audello_admin_styles');
}

function wp_audello_admin_settingslink($links, $file) {
	if ($file == 'wp-audello/wp-audello.php'){
		$settings_link = '<a href="options-general.php?page=wp-audello">'.__("Settings", "wp-audello").'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}

function wp_audello_admin_options() {
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.'));
	}
	
	// Display the header
	echo '<div class="wrap"><h2>'.__( 'Audello Settings', 'wp-audello' ).'</h2>';

	// See if the user has posted us some information
	// If they did, this hidden field will be set to 'Y'
	if(isset($_POST['hidden_submit']) && $_POST['hidden_submit'] == 'Y') {
		// Save the posted value in the database
		update_option('wp-audello-configured', true);
		
		update_option('audello_subdomain', trim(strtolower($_POST['audello_subdomain'])));
		update_option('audello_username', $_POST['audello_username']);
		update_option('audello_password', $_POST['audello_password']);
		update_option('audello_lite', $_POST['audello_lite']);
		update_option('audello_permissions', $_POST['audello_permissions']);
		update_option('audello_oembed_support', $_POST['audello_oembed_support']);
		update_option('audello_sitewide_audiobar', $_POST['audello_sitewide_audiobar']);
		
		// Do we need to set sitewide audio bar embed code?
		if(empty($_POST['audello_sitewide_audiobar']) || $_POST['audello_sitewide_audiobar'] == 'false' || $_POST['audello_sitewide_audiobar'] == '0') {
			update_option('audello_sitewide_audiobar_code', '');
		} else {
			$audello_subdomain = get_option('audello_subdomain');
			$audello_location = 'http://'.$audello_subdomain.'.audello.com';
			$audello_username = get_option('audello_username');
			$audello_password = sha1(get_option('audello_password'));
			$audello_api = $audello_location.'/api.php';
			
			$api_response = wp_remote_get($audello_api.'?'.http_build_query(array(
				'username' => $audello_username,
				'password' => $audello_password,
				'method' => 'collections-embed-code',
				'collection_id' => $_POST['audello_sitewide_audiobar'],
			)));
			
			$embed_code = json_decode($api_response['body']);
			
			update_option('audello_sitewide_audiobar_code', $embed_code->embed_code);
		}

		// Put an settings updated message on the screen
		echo "<div class=\"updated\"><p><strong>".__('Your settings have been successfully saved! Now go create a post or page!', 'wp-audello' )."</strong></p></div>";
	}
	
	// Read in existing option value from database
	$audello_subdomain = get_option('audello_subdomain');
	$audello_username = get_option('audello_username');
	$audello_password = get_option('audello_password');
	$audello_location = '//'.$audello_subdomain.'.audello.com';
	$audello_oembed_support = get_option('audello_oembed_support');
	$audello_sitewide_audiobar = get_option('audello_sitewide_audiobar');

	// Now display the settings editing screen
	?>
	
	<script type="text/javascript">
	jQuery(document).ready(function($) {	
		var populateAudioBars = function() {
			$.ajax({
				'url': 'http://'+$('#audello_subdomain').val()+'.audello.com/api.php',
				'dataType': 'jsonp',
				'global': false,
				'data': {
					'responseType': 'jsonp',
					'method': 'collections-list',
					'username': $('#audello_username').val(),
					'password': sha1($('#audello_password').val()),
					'appIntegration': true
				},
				'complete': function(r, status) {
					if(status !== 'success' || r.responseJSON.success === false) {
						$('.wp-audello-audiobar-list').empty().attr('disabled', true).append('<option value="false">There was an error loading your audio bars! Please try again!</option>');
					} else {
						try {
							var widgets = r.responseJSON.collections.audiobar.children, selects = $('.wp-audello-audiobar-list');
							$.each(widgets, function(idx, widget) {
								var el = $('<option value="'+widget.id+'">'+widget.name+'</option>');
								selects.each(function(idx, sel) {
									var orig = $(sel).attr('data-originally-selected') || false;
									if(orig && orig == widget.id) el.attr('selected', true);
									el.clone().appendTo(sel);
								});
							});
							$('.wp-audello-audiobar-list option[value="false"]').remove();
							$('.wp-audello-audiobar-list').attr('disabled', false);
							$('#wp-audello-admin-sitewide-audiobar-wrap').addClass('active');
						} catch(e) {
							console.log(e);
						}
					}
				}
			});
		};
		
		<?php if(!empty($audello_subdomain) && !empty($audello_username) && !empty($audello_password)) : ?>
		populateAudioBars();
		<?php endif; ?>
		
		$('#wp-audello-admin-test').click(function() {
			var audello_subdomain = $('#wp-audello-admin-form input[name="audello_subdomain"]').val();
			var audello_password = $('#wp-audello-admin-form input[name="audello_password"]').val();
			
			// Correct the location?
			audello_subdomain = audello_subdomain.replace('http://', '');
			audello_subdomain = audello_subdomain.replace('https://', '');
			audello_subdomain = audello_subdomain.replace('//', '');
			audello_subdomain = audello_subdomain.replace('.audello.com/api.php', '');
			audello_subdomain = audello_subdomain.replace('.audello.com/', '');
			audello_subdomain = audello_subdomain.replace('.audello.com', '');
			
			// Reset the location
			$('#wp-audello-admin-form input[name="audello_subdomain"]').val(audello_subdomain);
			
			// Set the API location
			var audello_api = '//'+audello_subdomain+'.audello.com/api.php';
			
			// Spin
			var button = $(this);
			button.spinner({
				'img': '<?php echo plugins_url('wp-audello/images/spinner.gif'); ?>',
				'position': 'center',
				'width': 20,
				'height': 21,
				'hide': true
			});
			
			$.ajax({
				'url': audello_api,
				'data': {'responseType': 'jsonp', 'method': 'integrate-details', 'username': $('#audello_username').val(), 'password': sha1(audello_password)},
				'dataType': 'jsonp',
				'timeout': 5000,
				'success': function(response) {
					if(response.success == true) {
						$('#audello_oembed_support').val(''+(response.oembed_support || false));
						$('#audello_lite').val(''+(response.lite || false));
						$('#audello_permissions').val(json_encode({
							'perm_files': (response.authorising_user.permissions_files == 1),
							'perm_uploads': (response.authorising_user.permissions_uploads == 1),
							'perm_podcasts': (response.authorising_user.permissions_podcasts == 1),
							'perm_playlists': (response.authorising_user.permissions_playlists == 1),
							'perm_audiobars': (response.authorising_user.permissions_audiobars == 1),
							'perm_splittests': (response.authorising_user.permissions_splittests == 1),
							'perm_stats': (response.authorising_user.permissions_stats == 1),
							'assigned_folders': response.authorising_user.assigned_folders,
							'isAdmin': (response.authorising_user.group && response.authorising_user.group.name === 'Administrators')
						}));
						
						if($('#wp-audello-admin-form').hasClass('audello-authenticated')) {
							$('#wp-audello-admin-form').submit();
						} else {
							populateAudioBars();
							$('#wp-audello-admin-form').addClass('audello-authenticated');
							button.val('Save my settings');
							button.spinner('remove');
						}
						
					//$('#wp-audello-admin-form').submit();
					} else {
						button.spinner('remove');
						alert('There has been an error!\r\r'+response.message);
						$('#wp-audello-admin-form').removeClass('audello-authenticated');
						button.val('Check my Audello details');
					}
				},
				'error': function() {
					button.spinner('remove');
					alert('It looks like the URL you entered does not point to a valid Audello account! Make sure you have entered your account\'s subdomain.');
				}
			});
		});
		
		$('#wp-audello-admin-form input').keyup(function(event) {
			if(event.which == 13) $('#wp-audello-admin-test').trigger('click');
		});
		
	});
	</script>
	
	<style type="text/css">
	#wp-audello-admin-sitewide-audiobar-wrap { opacity: 0.5; }
	#wp-audello-admin-sitewide-audiobar-wrap.active { opacity: 1; }
	</style>

	<form name="form1" method="post" action="options-general.php?page=wp-audello" id="wp-audello-admin-form" class="<?php echo (empty($audello_subdomain) ? '' : 'audello-authenticated'); ?>">
	<input type="hidden" name="hidden_submit" value="Y">
	<input type="hidden" name="audello_oembed_support" id="audello_oembed_support" value="">
	<input type="hidden" name="audello_lite" id="audello_lite" value="">
	<input type="hidden" name="audello_permissions" id="audello_permissions" value="">
	<p><strong>This plugin will not work if you haven't fully set up your Audello account.</strong> <a href="https://audello.com/" target="_blank">Don't have Audello? Click here to buy now!</a></p>
	
	<table class="form-table">
		<tbody>
			<tr align="top">
				<th scope="row">
					<label for="audello_subdomain"><?php _e("Enter your subdomain:", 'wp-audello' ); ?></label>
				</th>
				<td>
					<input type="text" name="audello_subdomain" id="audello_subdomain" value="<?php echo $audello_subdomain; ?>" size="10" class="code" />
					<span class="description">.audello.com</span>
				</td>
			</tr>
			<tr align="top">
				<th scope="row">
					<label for="audello_subdomain"><?php _e("Enter your email:", 'wp-audello' ); ?></label>
				</th>
				<td>
					<input type="text" name="audello_username" id="audello_username" value="<?php echo $audello_username; ?>" size="20" class="regular-text code" />
				</td>
			</tr>
			<tr align="top">
				<th scope="row">
					<label for="audello_subdomain"><?php _e("Enter your password:", 'wp-audello' ); ?></label>
				</th>
				<td>
					<input type="password" name="audello_password" id="audello_password" value="<?php echo $audello_password; ?>" size="20" class="regular-text code" />
				</td>
			</tr>
			<tr align="top" id="wp-audello-admin-sitewide-audiobar-wrap">
				<th scope="row">
					<label for="audello_sitewide_audiobar"><?php _e("Display an audio bar on your entire site:", 'wp-audello' ); ?></label>
				</th>
				<td>	
				 <select class="wp-audello-audiobar-list" name="audello_sitewide_audiobar" id="audello_sitewide_audiobar" disabled="disabled" data-originally-selected="<?php echo $audello_sitewide_audiobar; ?>">
				 	<option value="false"><?php echo (empty($audello_subdomain) ? 'Enter your authentication details above before using this option!' : 'Loading your audio bars... please wait...'); ?></option>
				 	<option value="0">I don't want an audio bar on my site</option>
				 </select>
				</td>
			</tr>
		</tbody>
	</table>
	
	<?php if($audello_oembed_support == 'true' || $audello_oembed_support === true) : ?>
	<div style="height:1px;background:#aaa;margin:20px 0 20px 0;padding:0;"></div>
	<div style="background:#EAF5DA;border:1px solid #A6B58D;padding:0 10px;"><p>Audello supports oEmbed! You can simply paste in the URL to one of Audello's audio pages, and it will be transformed into that file's embed code!</p></div>
	<?php endif; ?>
	
	<p class="submit">
		<input type="button" id="wp-audello-admin-test" class="button-primary" value="<?php esc_attr_e((empty($audello_subdomain) ? 'Check my Audello details' : 'Save my settings')); ?>" />
	</p>
	
	</form>
	</div>

<?php
}
?>