var WPAudelloRemoteDialog = {
	init : function() {
		//
	},

	insert : function(audello_code, embed_type, plugin_url) {
		if(audello_code.length <= 0) {
			tinyMCEPopup.close();
			return;
		}
		
		if(!embed_type) embed_type = 'file';
		if(embed_type == 'splittest' || embed_type == 'podcast') embed_type = 'file'; // No special images

		var url = plugin_url+'/../images/placeholder-'+embed_type+'.png?v=1';
		
		var audello_code = base64_encode(audello_code);
		
		var dimensions = {
			'file': {
				'x': 625,
				'y': 57
			},
			'playlist': {
				'x': 460,
				'y': 242
			},
			'audiobar': {
				'x': 625,
				'y': 31
			}
		};
		
		var html = '<img class="wpaudello-container" src="'+url+'" style="display: block; width: '+dimensions[embed_type].x+'px; height: '+dimensions[embed_type].y+'px;" alt="'+audello_code+'" />';
		
		// And insert html
		
		if(typeof tinyMCE != 'undefined' && (ed = tinyMCE.activeEditor) && !ed.isHidden()) {
			try {
				ed.focus();
				if(tinymce.isIE) ed.selection.moveToBookmark(tinymce.EditorManager.activeEditor.windowManager.bookmark);
				ed.execCommand('mceInsertContent', false, html);
			} catch(e){}
		} else {
			edInsertContent(edCanvas, html);
		}
		
		
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(WPAudelloRemoteDialog.init, WPAudelloRemoteDialog);