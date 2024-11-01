var WPAudelloDialog = {
	init : function() {
		this.resize();
	},

	insert : function() {
		var audello_code = document.getElementById('content').value;
		if(audello_code.length <= 0) {
			tinyMCEPopup.close();
			return;
		}

		var url = '../wp-content/plugins/wp-evs/images/placeholder.png?v=2';
		
		audello_code = base64_encode(audello_code);
		
		html = '<img class="wpaudello-container" src="'+url+'" style="display: block; width: 625px; height: 57px;" alt="'+audello_code+'" />';
		
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
	},

	resize : function() {
		//
	}
};

tinyMCEPopup.onInit.add(WPAudelloDialog.init, WPAudelloDialog);