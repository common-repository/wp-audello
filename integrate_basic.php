<?php
// And for non-tinyMCE and general functions
function wp_audello_integrate_basic() {
	?>
	<script type="text/javascript">
	function WPAudelloPopup() {
	 var audello_code = prompt('Please enter your Audello embed code here. Code for playlists, audio widgets etc will all work too!');
	 if(typeof(audello_code) == 'string' && audello_code.length > 0) {
			var url = '<?php echo plugins_url('/wp-audello/images/placeholder.png?v=1'); ?>';
			
			audello_code = audello_code.replace('<!--', '').replace('//-->', '');
			
			audello_code = base64_encode(audello_code);
			
			var html = '<img class="wpaudello-container" src="'+url+'" style="display: block; width: 625px; height: 57px;" alt="'+audello_code+'" />';
			edInsertContent(edCanvas, html);
	 }
	}
	
	jQuery(document).ready(function() {
		setTimeout(function() {
			jQuery('#ed_toolbar').append('<input type="button" class="ed_button button button-small" onclick="WPAudelloPopup()" title="" value="Embed via Audello" />');
		}, 500);
	});
	</script>
	<?php
}
add_action('edit_form_advanced', 'wp_audello_integrate_basic');
add_action('edit_page_form', 'wp_audello_integrate_basic');
?>