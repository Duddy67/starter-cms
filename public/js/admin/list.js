document.addEventListener('DOMContentLoaded', () => {
    const messages = (document.getElementById('JsMessages')) ? JSON.parse(document.getElementById('JsMessages').value) : {};
    const cselect = new C_Select.init();

    const actions = ['create', 'massDestroy', 'batch', 'checkin', 'publish', 'unpublish'];

    actions.forEach(function (action) {
        if (document.getElementById(action)) {
            document.getElementById(action).addEventListener('click', function() {
                window[action]();
            });
        }
    });

    /** Filters **/

    const filters = document.querySelectorAll('select[id^="filters-"]');

    for (let i = 0; i < filters.length; i++) {
        filters[i].addEventListener('change', function() {
            checkEmptyFilters();
            document.getElementById('item-filters').submit();
        });
    }

    document.getElementById('search-btn').addEventListener('click', function() {
        if (document.getElementById('search').value !== '') {
            checkEmptyFilters();
            document.getElementById('item-filters').submit();
        }
    });

    document.getElementById('clear-search-btn').addEventListener('click', function() {
        document.getElementById('search').value = '';
        checkEmptyFilters();
        document.getElementById('item-filters').submit();
    });

    if (document.getElementById('clear-all-btn')) {
        document.getElementById('clear-all-btn').addEventListener('click', function() {
            document.querySelectorAll('select[id^="filters-"]').forEach(function(elem) {
                while (elem.firstChild) {
                    elem.removeChild(elem.firstChild);
                }
            });

            document.getElementById('search').value = '';

            checkEmptyFilters();

            document.getElementById('item-filters').submit();
        });
    }

    /* Numerical order (if any) */
    if (document.getElementById('canOrderBy')) {
        // Enable or disable the order options accordingly.
        const filterSelect = document.getElementById('filters-sortedBy');
        for (let i = 0; i < filterSelect.options.length; i++) {
            if (filterSelect.options[i].value == 'order_asc' || filterSelect.options[i].value == 'order_desc') {
                if (document.getElementById('canOrderBy').value) {
                    filterSelect.options[i].disabled = false;
                }
                else {
                    filterSelect.options[i].disabled = true;

                    if (filterSelect.selectedIndex != -1 && (filterSelect.value == 'order_asc' || filterSelect.value == 'order_desc')) {
                        filterSelect.options[filterSelect.selectedIndex].removeAttribute('selected');
                    }
                }

                cselect.rebuildCSelect(filterSelect);
            }
        }
    }

    /*
     * Prevents the parameters with empty value to be send in the url query.
     */
    function checkEmptyFilters() {
        const filters = document.querySelectorAll('select[id^="filters-"]');

        for (let i = 0; i < filters.length; i++) {
	    if(filters[i].value === null || filters[i].value === '') {
		filters[i].disabled = true;
	    }

	    // Reinitialize pagination on each request.
	    if (document.getElementById('filters-pagination')) {
		document.getElementById('filters-pagination').disabled = true;
	    }

	    if (document.getElementById('search').value === '') {
		document.getElementById('search').disabled = true;
	    }
        }
    }

    function setSelectedItems() {
	let ids = [];
	let inputs = '';

        const checkBoxes = document.querySelectorAll('.form-check-input');

        for (let i = 0; i < checkBoxes.length; i++) {
            if (checkBoxes[i].checked) {
                ids.push(checkBoxes[i].dataset.itemId);
            }
        }

	if (ids.length === 0) {
	    alert(messages.no_item_selected);
	    return false;
	}

        // Remove a possible previous selection from the selectedItems form.
        let oldInputs = document.querySelectorAll('input[name="ids\\[\\]"]');

        for (let i = 0; i < oldInputs.length; i++) {
            oldInputs[i].remove();
        }

	for (let i = 0; i < ids.length; i++) {
	    inputs += '<input type="hidden" name="ids[]" value="'+ids[i]+'">';
	}
	
        document.getElementById('selectedItems').insertAdjacentHTML('beforeend', inputs);

        // Check for batch iframe.
        const iframe = document.querySelector('iframe[name="batch"]');

	if (iframe) {
	    // Remove a possible previous selection from the batchForm form.
            oldInputs = iframe.querySelectorAll('input[name="ids\\[\\]"]');

            for (let i = 0; i < oldInputs.length; i++) {
                oldInputs[i].remove();
            }

            iframe.contentWindow.document.getElementById('batchForm').insertAdjacentHTML('beforeend', inputs);
	}

	return true;
    }

    create = function() {
	window.location.replace(document.getElementById('createItem').value);
    }

    massDestroy = function() {
	if (setSelectedItems() && window.confirm(messages.confirm_multiple_item_deletion)) {
            const selectedItemForm = document.getElementById('selectedItems');
            selectedItemForm.querySelector('input[name="_method"]').value = 'delete';
            selectedItemForm.setAttribute('action', document.getElementById('destroyItems').value);
	    selectedItemForm.submit();
	}
    }

    checkin = function() {
	if (setSelectedItems()) {
            const selectedItemForm = document.getElementById('selectedItems');
            selectedItemForm.querySelector('input[name="_method"]').value = 'put';
            selectedItemForm.setAttribute('action', document.getElementById('checkinItems').value);
	    selectedItemForm.submit();
	}
    }

    publish = function() {
	if (setSelectedItems()) {
            const selectedItemForm = document.getElementById('selectedItems');
            selectedItemForm.querySelector('input[name="_method"]').value = 'put';
            selectedItemForm.setAttribute('action', document.getElementById('publishItems').value);
	    selectedItemForm.submit();
	}
    }

    unpublish = function() {
	if (setSelectedItems()) {
            const selectedItemForm = document.getElementById('selectedItems');
            selectedItemForm.querySelector('input[name="_method"]').value = 'put';
            selectedItemForm.setAttribute('action', document.getElementById('unpublishItems').value);
	    selectedItemForm.submit();
	}
    }

    batch = function() {
	if (setSelectedItems()) {
	    document.getElementById('batch-window').style.display = 'block';
	}
    }
});

