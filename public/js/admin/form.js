(function() {

    let localNamespace = {};

    // Run a function when the page is fully loaded including graphics.
    document.addEventListener('DOMContentLoaded', () => {
        let actions = ['save', 'saveClose', 'cancel', 'destroy'];

        actions.forEach(function (action) {
            let button = document.getElementById(action);

            if (button) {
                button.onclick = function(e) {
                    localNamespace[action]();
                }
            }
        });

        let button = document.getElementById('deleteDocumentBtn');

        if (button) {
            button.addEventListener('click', function(e) {
                runAjax(button.dataset.formId);
            });
        }
    });

    function save() {
        runAjax('itemForm');
    }

    function saveClose() {
        document.querySelector('input[name="_close"]').value = 1;
        runAjax('itemForm');
    }

    function cancel() {
        window.location.replace(document.getElementById('cancelEdit').value);
    }

    function destroy() {
        if (window.confirm('Are you sure ?')) {
            document.getElementById('deleteItem').submit();
        }
    }

    // Store action functions.
    localNamespace['save'] = save;
    localNamespace['saveClose'] = saveClose;
    localNamespace['cancel'] = cancel;
    localNamespace['destroy'] = destroy;

    function runAjax(formId) {
        let form = document.getElementById(formId);

        // Exit the function in case the document deletion is canceled. 
        if (['deleteImage', 'deletePhoto'].includes(formId) && !window.confirm('Are you sure ?')) {
            return;
        }

        // Check for possible dynamic item field validation such as CReapeter, Layout etc...
        if (typeof validateFields === 'function' && !validateFields()) {
            return;
        }

        let formData = new FormData(form);

        const spinner = document.getElementById('ajax-progress');
        spinner.classList.remove('d-none');

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
        const spinner = document.getElementById('ajax-progress');
        spinner.classList.add('d-none');

        if (status === 200) {
            // Loop through the returned result.
            for (const [key, value] of Object.entries(result)) {
                if (key == 'redirect') {
                    window.location.href = result.redirect;
                }
                else if (key == 'updates') {
                    updateFieldValues(result.updates);
                }
                // messages
                else if (['success', 'warning', 'info'].includes(key)) {
                    displayMessage(key, value);
                }
            }
        }
        else if (status === 422) {
            displayMessage('danger', 'Please check the form below for errors.');
            // Loop through the returned errors and set the messages accordingly.
            for (const [name, message] of Object.entries(result.errors)) {
                document.getElementById(name+'Error').innerHTML = message;
            }
        }
        else {
            displayMessage('danger', 'Error '+status+': '+result.message);
        }
    }

    function updateFieldValues(updates) {
        for (const [id, value] of Object.entries(updates)) {
            if (document.getElementById(id).tagName == 'IMG') {
                document.getElementById(id).setAttribute('src', value);
            }
            else if (document.getElementById(id).tagName == 'A') {
                document.getElementById(id).setAttribute('href', value);
            }
            else {
                document.getElementById(id).value = value;
            }
        }
    }

    function displayMessage(type, message) {
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

        // Adapt to Bootstrap alert class names.
        type = (type == 'error') ? 'danger' : type;

        const messageAlert = document.getElementById('ajax-message-alert');
        messageAlert.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning', 'alert-info');
        messageAlert.classList.add('alert-'+type);
        document.getElementById('ajax-message').innerHTML = message;

        window.scrollTo(0, 0);
    }

})();


/* Used for the Select2 jQuery plugin. */
(function($) {

    if (jQuery.fn.select2) {
        $('.select2').select2();
    }

    // Fixes Select2 bug with Bootstrap tabs: https://github.com/select2/select2/issues/4220
    $('.select2-container--default').attr('style', 'width: 100%');

})(jQuery);

