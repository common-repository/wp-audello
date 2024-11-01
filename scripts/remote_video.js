(function() {
	tinymce.create('tinymce.plugins.wpaudello_remote', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			var t = this;

			t.editor = ed;
			
			// Set the Audello settings
			var audello_location = '//'+WPAudelloSettings.subdomain+'.audello.com';
			var audello_username = WPAudelloSettings.username;
			var audello_password = WPAudelloSettings.password;
			var audello_lite = !!WPAudelloSettings.lite;
			var audello_permissions = WPAudelloSettings.permissions;

			// Register commands
			ed.addCommand('mceWPAudelloRemote', function(ui) {
				ed.windowManager.open({
					file : url + '/remote_video.html?v=10-1',
					width : 902,
					height : 637,
					inline : 1
				}, {
					'plugin_url': url,
					'audello_lite': audello_lite,
					'audello_permissions': audello_permissions,
					'audello_location': audello_location,
					'audello_username': audello_username,
					'audello_password': audello_password
				});
			});

			ed.addCommand('mceInsertTemplate', t._insertTemplate, t);

			// Register buttons
			ed.addButton('wpaudello_remote', {'title': 'Embed via Audello', 'image': url + '/../images/logo.png?v=1', 'cmd': 'mceWPAudelloRemote'});
		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : "WP-Audello",
				author : 'WebActix',
				authorurl : 'http://webactix.com',
				infourl : 'http://webactix.com/wp-audello-plugin/',
				version : '1.0'
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('wpaudello_remote', tinymce.plugins.wpaudello_remote);
})();