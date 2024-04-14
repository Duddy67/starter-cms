
tinymce.init({
    selector: '.tinymce-texteditor',
    plugins: 'code',
    entity_encoding: 'raw',
    toolbar: 'urldialog|code',
    height: 500,
    convert_urls: false,
    setup: function (editor) {
	editor.ui.registry.addButton('urldialog', {
	    icon: 'browse',
	    onAction: function () {
	        let siteUrl = document.getElementById('siteUrl').value;

		editor.windowManager.openUrl({
		    title: 'File Manager',
		    url: siteUrl+'/cms/filemanager',
		    buttons: [
			{
			    type: "cancel",
			    name: "cancel",
			    text: "Close Dialog"
			}
		    ],
		    height: 740,
		    width: 1240
		});
	    }
	});

        editor.on('change', function () {
            editor.save();
        });

	editor.addCommand("iframeCommand", function(ui, value) {
	    if (value.content_type.startsWith('image')) {
		editor.insertContent(
		    `<img src="${value.file_url}" alt="${value.file_name}">`
		);
	    }
	    else {
		editor.insertContent(
		    `<a target="_blank" href="${value.file_url}">${value.file_name}</a>`
		);
	    }
	});

        if (document.getElementById('canEdit') && !document.getElementById('canEdit').value) {
	    editor.settings.menubar = false;
	    editor.settings.toolbar = false;
	    editor.settings.readonly = 1;
	}
    },

    content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
});

