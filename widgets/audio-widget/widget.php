<?php
/**
 * Class
 */
class WP_audello_Widget_AudioWidget extends WP_Widget {
	function WP_audello_Widget_AudioWidget() {
		$widget_ops = array('classname' => 'example', 'description' => 'Play an Audello audio widget.');

		/* Widget control settings. */
		$control_ops = array('width' => 300, 'height' => 350, 'id_base' => 'wp-audello-widget-audio-widget');

		/* Create the widget. */
		$this->WP_Widget('wp-audello-widget-audio-widget', 'Audello audio widget', $widget_ops, $control_ops);
	}

	function widget($args, $instance) {
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		$javascript_code = $instance['javascript_code'];
		$iframe_code = $instance['iframe_code'];
		$object_code = $instance['object_code'];
		?>
			<?php echo $before_widget; ?>
					<?php if($title) echo $before_title . $title . $after_title; ?>
					<?php echo $javascript_code; ?>
			<?php echo $after_widget; ?>
		<?php
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		
		$audello_subdomain = get_option('audello_subdomain');
		$audello_location = 'http://'.$audello_subdomain.'.audello.com';
		$audello_username = get_option('audello_username');
		$audello_password = sha1(get_option('audello_password'));
		$audello_api = $audello_location.'/api.php';
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['collection_id'] = strip_tags($new_instance['collection_id']);
		
		$collection_id = $new_instance['collection_id'];
		
	//if($instance['collection_id'] !== $new_instance['collection_id']) {
			$api_response = wp_remote_get($audello_api.'?'.http_build_query(array(
				'username' => $audello_username,
				'password' => $audello_password,
				'method' => 'collections-embed-code',
				'collection_id' => $collection_id,
			)));
			
			$embed_code = json_decode($api_response['body']);
			
			$instance['javascript_code'] = $embed_code->embed_code;
	//}
		
		return $instance;
	}

	function form($instance) {
		if(!empty($_POST)) return; // Don't re-display the form if we're posting to save stuff!
		$title = esc_attr($instance['title']);
		$selected_collection = $instance['collection_id'];
		
		$audello_subdomain = get_option('audello_subdomain');
		$audello_location = 'http://'.$audello_subdomain.'.audello.com';
		$audello_username = get_option('audello_username');
		$audello_password = sha1(get_option('audello_password'));
		$audello_api = $audello_location.'/api.php';
		
		$ui_title_id = $this->get_field_id('title');
		$ui_title_name = $this->get_field_name('title');
		$ui_collection_id_id = $this->get_field_id('collection_id');
		$ui_collection_id_name = $this->get_field_name('collection_id');
		
		?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {
			var hasLoaded = !!window.WPAudelloHasLoaded;
			if(hasLoaded) return false;
			window.WPAudelloHasLoaded = true;
			$.ajax({
				'url': '<?php echo $audello_api; ?>',
				'dataType': 'jsonp',
				'global': false,
				'data': {
					'responseType': 'jsonp',
					'method': 'collections-list',
					'username': '<?php echo $audello_username; ?>',
					'password': '<?php echo $audello_password; ?>'
				},
				'complete': function(r, status) {
					if(status !== 'success') {
						$('.wp-audello-widget-audiowidget-list').empty().append('<option disabled="disabled">There was an error loading your widgets! Please try again!</option>');
					} else {
						try {
							var widgets = r.responseJSON.collections.audiowidget.children, selects = $('.wp-audello-widget-audiowidget-list');
							$.each(widgets, function(idx, widget) {
								var el = $('<option value="'+widget.id+'">'+widget.name+'</option>');
								selects.each(function(idx, sel) {
									var orig = $(sel).attr('data-originally-selected') || false;
									if(orig && orig == widget.id) el.attr('selected', true);
									el.clone().appendTo(sel);
								});
							});
							$('.wp-audello-widget-audiowidget-list option:disabled').remove();
						} catch(e) {
							//
						}
					}
				}
			});
		});
		</script>

		<p>
		 <label for="<?php echo $ui_title_id; ?>"><?php _e('Title:'); ?></label> 
		 <input class="widefat" id="<?php echo $ui_title_id; ?>" name="<?php echo $ui_title_name; ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
		 <label for="<?php echo $ui_collection_id_id; ?>" style="margin-right:5px;"><?php _e('Choose the audio widget to display:'); ?></label>
		 <select class="widefat wp-audello-widget-audiowidget-list" name="<?php echo $ui_collection_id_name; ?>" id="<?php echo $ui_collection_id_id; ?>" data-originally-selected="<?php echo $selected_collection; ?>">
		 	<option disabled="disabled">Loading your widgets... please wait...</option>
		 </select>
		</p>
		<?php
	}
}

add_action('widgets_init', create_function('', 'return register_widget("wp_audello_Widget_AudioWidget");'));
?>