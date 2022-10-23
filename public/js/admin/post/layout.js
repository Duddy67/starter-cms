document.addEventListener('DOMContentLoaded', () => {

    let layout = new C_Layout.init();
    let url = document.getElementById('postLayout').value;

    if (url.length > 0) {
        let ajax = new C_Ajax.init({
            method: 'GET',
            url: url,
            dataType: 'json'
        });

        ajax.run(getAjaxResult);
    }

    function getAjaxResult(status, result) {
	if(status === 200) {
	    result.forEach(item => {
		layout.createItem(item)
	    });
        }
        else {
	    alert('Error: '+result.response);
        }
    }

    const afterRemoveItem = function(idNb, type) {
        let form = document.getElementById('deleteLayoutItem');

        if (form.length > 0) {
            // Set the id number of the item to delete  
            document.getElementById('_idNb').value = idNb;
            let action = document.getElementById('deleteLayoutItem').action;
            let formData = new FormData(form);

            let ajax = new C_Ajax.init({
                method: 'POST',
                url: action,
                dataType: 'json',
                data: formData,
                headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json'}
            });
        }

        if (type == 'group_start') {
            layout.removeGroupEndItem(idNb);
        }
    };

    const validateFields = function() {

        if (!layout.validateFields()) {
            // Switch to the layout tab.
            $('.nav-tabs a[href="#layout"]').tab('show');

            return false;
        }

        return true;
    }

    // Store the callback function into the global window object.
    window.afterRemoveItem = afterRemoveItem;
    window.validateFields = validateFields;
});
