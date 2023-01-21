document.addEventListener('DOMContentLoaded', () => {

    // Initialize a new editor for each textarea.  
    document.querySelectorAll('[id^="tiny-comment-"]').forEach(function(item) { 
        initTinyMceEditor(item.dataset.commentId);
    });

    document.getElementById('create-btn').onclick = function() { 
        runAjax(document.getElementById('createComment'));
    }

    document.addEventListener('click', function(e) {
        const deleteBtn = e.target.closest('[id^="delete-btn-"]');
        const editBtn = e.target.closest('[id^="edit-btn-"]');
        const updateBtn = e.target.closest('[id^="update-btn-"]');
        const cancelBtn = e.target.closest('[id^="cancel-btn-"]');

        // Delete the given comment.
        if (deleteBtn) {
            if (window.confirm('Are you sure ?')) {
                runAjax(document.getElementById('deleteComment-'+deleteBtn.dataset.commentId));
            }
        }

        if (editBtn) {
            toggleEditor(editBtn.dataset.commentId);
        }

        if (updateBtn) {
            runAjax(document.getElementById('updateComment-'+updateBtn.dataset.commentId));
        }

        if (cancelBtn) {
            deleteMessages();
            // Reset the editor to the original content.
            tinyMCE.get('tiny-comment-'+cancelBtn.dataset.commentId).setContent(document.getElementById('comment-'+cancelBtn.dataset.commentId).innerHTML);
            toggleEditor(cancelBtn.dataset.commentId);
        }
    });

    function runAjax(form) {
        let formData = new FormData(form);

        let ajax = new C_Ajax.init({
            method: 'post',
            url: form.action,
            dataType: 'json',
            data: formData,
            headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json'}
        });

        ajax.run(getAjaxResult);
    }

    function getAjaxResult(status, result) {
        //const spinner = document.getElementById('ajax-progress');
        //spinner.classList.add('d-none');

        if (status === 200) {
            deleteMessages();

            if (result.action == 'create') {
                document.getElementById('createComment').insertAdjacentHTML('afterend', result.render);
                initTinyMceEditor(result.id);
                tinyMCE.get('tiny-comment-'+result.id).setContent(result.text);
                tinyMCE.get('tiny-comment-0').setContent('');
                displayMessage('success', result.message, result.id);
            }

            if (result.action == 'update') {
                // Update the readonly comment.
                document.getElementById('comment-'+result.id).innerHTML = tinyMCE.get('tiny-comment-'+result.id).getContent();
                toggleEditor(result.id);
                displayMessage('success', result.message, result.id);
            }

            if (result.action == 'delete') {
                document.getElementById('card-comment-'+result.id).remove();
                displayMessage('success', result.message, 0);
            }

        }
        else if (status === 422) {
            displayMessage('danger', result.message, result.commentId);
            // Loop through the returned errors and set the messages accordingly.
            for (const [name, message] of Object.entries(result.errors)) {
                document.getElementById(name+'Error').innerHTML = message;
            }
        }
        else {
            displayMessage('danger', 'Error '+status+': '+result.message, result.commentId);
        }
    }

    function deleteMessages() {
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
        deleteMessages();

        // Adapt to Bootstrap alert class names.
        type = (type == 'error') ? 'danger' : type;

        const messageAlert = document.getElementById('ajax-message-alert-'+idNb);
        messageAlert.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning', 'alert-info');
        messageAlert.classList.add('alert-'+type);
        document.getElementById('ajax-message-'+idNb).innerHTML = message;
        document.getElementById('ajax-message-alert-'+idNb).scrollIntoView({behavior: 'smooth', block: 'start', inline: 'nearest'});
    }

    function initTinyMceEditor(idNb) {
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

    // Hide or show the given editor.
    function toggleEditor(idNb) {
        if (document.getElementById('updateComment-'+idNb).style.display == 'none') {
            document.getElementById('updateComment-'+idNb).style.display = 'block'; 
            document.getElementById('comment-'+idNb).style.display = 'none'; 
        }
        else {
            document.getElementById('updateComment-'+idNb).style.display = 'none'; 
            document.getElementById('comment-'+idNb).style.display = 'block'; 
        }
    }
});
