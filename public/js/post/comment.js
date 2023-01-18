document.addEventListener('DOMContentLoaded', () => {

    // Initialize a new editor for each textarea.  
    document.querySelectorAll('[id^="tiny-comment-"]').forEach(function(item) { 
        _initTinyMceEditor(item.dataset.commentId);
    });

    // Show the editor and hide the readonly comment.
    document.querySelectorAll('[id^="edit-btn-"]').forEach(button => button.onclick = function() { 
        //alert(this.id+' '+this.dataset.commentId);
        document.getElementById('updateComment-'+this.dataset.commentId).style.display = 'block'; 
        document.getElementById('comment-'+this.dataset.commentId).style.display = 'none'; 
    });

    // Hide the editor and show the readonly comment.
    document.querySelectorAll('[id^="cancel-btn-"]').forEach(button => button.onclick = function() { 
        document.getElementById('updateComment-'+this.dataset.commentId).style.display = 'none'; 
        document.getElementById('comment-'+this.dataset.commentId).style.display = 'block'; 
    });

    // Update the given comment.
    document.querySelectorAll('[id^="update-btn-"]').forEach(button => button.onclick = function() { 
        //alert(tinyMCE.get('comment-'+this.dataset.commentId).getContent());
            //console.log(tinyMCE.get('comment-'+this.dataset.commentId).getContent());

        let formData = new FormData(document.getElementById('updateComment-'+this.dataset.commentId));

        let ajax = new C_Ajax.init({
            method: 'post',
            url: document.getElementById('updateComment-'+this.dataset.commentId).action,
            dataType: 'json',
            data: formData,
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json'}
        });

        ajax.run(getAjaxResult);
    });

    // Delete the given comment.
    document.querySelectorAll('[id^="delete-btn-"]').forEach(button => button.onclick = function() { 
        alert(this.id+' '+this.dataset.commentId);
        document.getElementById('deleteComment-'+this.dataset.commentId).submit();
    });

    function getAjaxResult(status, result) {
        //const spinner = document.getElementById('ajax-progress');
        //spinner.classList.add('d-none');

        if (status === 200) {
            deleteMessage();
            // Update the readonly comment.
            document.getElementById('comment-'+result.id).innerHTML = tinyMCE.get('tiny-comment-'+result.id).getContent();
            // Hide the editor and show the readonly comment.
            document.getElementById('updateComment-'+result.id).style.display = 'none'; 
            document.getElementById('comment-'+result.id).style.display = 'block'; 
            displayMessage('success', 'Comment successfuly updated.', result.id);
            //window.location.reload();
        }
        else if (status === 422) {
      console.log(result);
            displayMessage('danger', 'Please check the form for errors.', result.commentId);
            // Loop through the returned errors and set the messages accordingly.
            for (const [name, message] of Object.entries(result.errors)) {
                // Extract the comment id from the name.
                const matches = name.match(/-([0-9]*)$/);
                const commentId = matches[1];
      //console.log(matches[1]);
                document.getElementById(name+'Error').innerHTML = message;
            }
        }
        else {
            displayMessage('danger', 'Error '+status+': '+result.message);
        }
    }

    function deleteMessage() {
        // Empty some possible error messages.
        document.querySelectorAll('div[id$="Error"]').forEach(elem => {
            elem.innerHTML = '';
        });

        // Hide the possible displayed flash messages.
        document.querySelectorAll('.flash-message').forEach(elem => {
            if (!elem.classList.contains('d-none')) {
                elem.classList.add('d-none');
            }
        });
    }

    function displayMessage(type, message, idNb) {
        // First delete possible displayed messages.
        deleteMessage();

        // Adapt to Bootstrap alert class names.
        type = (type == 'error') ? 'danger' : type;

        const messageAlert = document.getElementById('ajax-message-alert-'+idNb);
        messageAlert.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning', 'alert-info');
        messageAlert.classList.add('alert-'+type);
        document.getElementById('ajax-message-'+idNb).innerHTML = message;
    }

    function _initTinyMceEditor(idNb) {
        let editor = tinymce.init({
	    selector: '#tiny-comment-'+idNb,
            entity_encoding: 'raw',
            toolbar: 'urldialog',
            height: 200,
            convert_urls: false,
            setup: function(editor) {
                editor.on('change', function () {
                    editor.save();
                });

            }
	});

        return editor;
    }
});
