// Anonymous function with namespace.
const C_Select = (function() {

    document.addEventListener('DOMContentLoaded', () => {

        // Get all the initial select tags with a cselect class.
        const selects = document.getElementsByClassName('cselect');

        // Create a CSelect drop down list item for each actual select tag.
        for (let i = 0; i < selects.length; i++) {
            const select = selects[i];
            // Use i as cselect id.
            createCSelect(select, i);
        }

        document.addEventListener('click', function(evt) {
            // Don't close the drop down list when selected or disabled items are clicked.
            if (evt.target.classList.contains('cselect-item-selected') || evt.target.classList.contains('cselect-item-disabled')) {
                return;
            }

            // Close the drop down list if the user clicks outside of it
            closeAllLists(evt.target);

            // Check for button closure in a multiple drop down list. 
            if (evt.target.classList.contains('cselect-button-close')) {
                // Get the button (the closure span parent).
                const button = evt.target.parentElement;
                // Get the actual select.
                const select = getSelect(button);

                // Check first if the actual select or the corresponding option of the button is disabled.
                if (select.disabled || select.options[button.dataset.idNumber].disabled) {
                    return;
                }

                // Get the cselect container (ie: the parent's parent of the button).
                const cselect = button.parentElement.parentElement;

                // Get the cselect id number from the cselect container.
                const cselectId = cselect.dataset.idNumber;

                // Set the attribute and class of the unselected item.
                const unselectedItem = document.getElementById('cselect-item-selected-' + cselectId + '-' + button.dataset.idNumber);
                unselectedItem.classList.remove('cselect-item-selected');
                unselectedItem.classList.add('cselect-item');
                unselectedItem.removeAttribute('id');

                // Unselect the corresponding option in the actual select.
                select.options[button.dataset.idNumber].removeAttribute('selected');

                // Then remove the button from the selected area.
                button.remove();

                // Trigger a change event.
                // Note: Use var instead of const or a "Cannot access before initialization" error will be raised.
                var evt = new Event('change', {'bubbles': true});
                select.dispatchEvent(evt);
            }
        });
    });

    // Private functions.

    /*
     * Create a CSelect drop down list.
     */
    function createCSelect(select, cselectId) {
        // Make sure the tag is a select and has a name attribute. 
        if (select.tagName == 'SELECT' && select.hasAttribute('name')) {
            // Get a new id number for the select tags created dynamically (ie: after the initial page loading).
            cselectId = cselectId === undefined ? getNewIdNumber() : cselectId;

            // Build the CSelect drop down list container.
            const cselect = document.createElement('div');
            cselect.setAttribute('class', 'cselect-container');
            cselect.setAttribute('id', 'cselect-' + cselectId);
            // The name of the actual select.
            cselect.setAttribute('data-select-name', select.name);
            cselect.setAttribute('data-id-number', cselectId);

            // Add the id of the newly created CSelect element to the actual select.
            select.setAttribute('data-cselect-id', 'cselect-' + cselectId);

            // Check if the actual select is disabled.
            if (select.disabled) {
                cselect.setAttribute('class', 'cselect-disabled');
            }

            // Build the selection area.
            const selection = document.createElement('div');
            selection.setAttribute('id', 'cselect-selection-' + cselectId);
            selection.setAttribute('class', 'cselect-selection');

            if (select.hasAttribute('multiple')) {
                // Create a button for each selected option (if any) and put them into the selection area.
                for (let i = 0; i < select.options.length; i++) {
                    if (select.options[i].selected) {
                        const buttonItem = createButtonItem(select, i);
                        selection.appendChild(buttonItem);
                    }   
                }

                selection.classList.add('cselect-multiple');
            }
            else {
                const text = document.createTextNode(select.options[select.selectedIndex].text);
                selection.appendChild(text);
                // Regular drop down lists need a selector icon.
                selection.classList.add('cselect-selector');
            }

            cselect.appendChild(selection);

            const itemContainer = document.createElement('div');
            itemContainer.setAttribute('class', 'cselect-item-container');
            itemContainer.setAttribute('id', 'cselect-item-container-' + cselectId);

            // Create and insert the search input into the item container.
            const search = document.createElement('input');
            search.setAttribute('class', 'cselect-search');
            search.setAttribute('id', 'cselect-search-' + cselectId);
            search.setAttribute('type', 'text');
            itemContainer.appendChild(search);

            const optionNb = select.options.length;

            // Loop through the options of the actual select and create the corresponding items.
            for (let j = 0; j < optionNb; j++) {

                const optionItem = document.createElement('div');
                optionItem.setAttribute('data-value', select.options[j].value);
                optionItem.setAttribute('data-id-number', j);
                optionItem.setAttribute('class', 'cselect-item');

                // Check for selected options.
                if (select.options[j].selected) {
                    optionItem.classList.add('cselect-item-selected');
                    // Use 2 ids in case of multiple select.
                    optionItem.setAttribute('id', 'cselect-item-selected-' + cselectId + '-' + j);
                }

                // Check if the option is disabled.
                if (select.options[j].disabled) {
                    optionItem.setAttribute('class', 'cselect-item-disabled');
                }

                const text = document.createTextNode(select.options[j].text);
                optionItem.appendChild(text);
                itemContainer.appendChild(optionItem);

                // Set the selected value and close the dropdown when an option item is clicked
                optionItem.addEventListener('click', function() {
                    // First clear the current search (if any).
                    search.value = '';
                    const items = itemContainer.childNodes;

                    for (let i = 0; i < items.length; i++) {
                        // Display again the possible item options hidden during the search. 
                        items[i].removeAttribute('style');
                    }

                    // Don't treat the items already selected or disabled.
                    if (this.classList.contains('cselect-item-selected') || this.classList.contains('cselect-item-disabled')) {
                        return;
                    }

                    if (select.hasAttribute('multiple')) {
                        updateSelectedMultiple(cselectId, this);
                    }
                    // Standard drop down list.
                    else {
                        updateSelected(cselectId, this);
                    }

                    // Close the drop down list.
                    itemContainer.style.display = 'none';
                });
            }

            cselect.appendChild(itemContainer);

            // Filter option items based on user input (search).
            search.addEventListener('input', function() {
                const filter = this.value.toUpperCase();

                // Get the option items in the item container.
                const items = itemContainer.childNodes;

                for (let i = 0; i < items.length; i++) {
                    // Skip the search input element which is the first item in the item container.
                    if (i == 0) {
                        continue;
                    }

                    const itemText = items[i].innerHTML.toUpperCase();

                    // Compare the text to the user input and hide the item accordingly.
                    if (itemText.indexOf(filter) > -1) {
                        items[i].style.display = '';
                    }
                    else {
                        items[i].style.display = 'none';
                    }
                }
            });

            // Toggle the drop down list when the selection is clicked
            selection.addEventListener('click', function() {
                // Don't open the list when the select is disabled.
                if (cselect.classList.contains('cselect-disabled')) {
                    return;
                }

                if (itemContainer.style.display === 'block') {
                    itemContainer.style.display = 'none';
                }
                else {
                    itemContainer.style.display = 'block';
                }
            });

            // Insert the newly created CSelect after the actual select.
            select.insertAdjacentElement('afterend', cselect);
        }
    }

    /*
     * Create a button for a given selected option. Use with multiple selection.
     */
    function createButtonItem(select, idNumber) {
        // Create a button for the selected item.
        const buttonItem = document.createElement('div');
        buttonItem.setAttribute('class', 'cselect-button');
        buttonItem.setAttribute('id', 'cselect-button-' + idNumber);
        buttonItem.setAttribute('data-id-number', idNumber);
        buttonItem.setAttribute('data-value', select.options[idNumber].value);
        buttonItem.setAttribute('data-cselect-id', select.dataset.cselectId);

        if (select.options[idNumber].disabled) {
            buttonItem.classList.add('cselect-button-disabled');
        }

        // Create the button label.
        const label = document.createElement('span');
        label.setAttribute('class', 'cselect-button-label');
        let text = document.createTextNode(select.options[idNumber].text);
        label.appendChild(text);

        // Create the button closure.
        const close = document.createElement('span');
        close.setAttribute('class', 'cselect-button-close');
        text = document.createTextNode('x');
        close.appendChild(text);
        buttonItem.appendChild(close);
        buttonItem.appendChild(label);

        return buttonItem;
    }

    /*
     * Updates the selection in a regular drop down list.
     */
    function updateSelected(cselectId, newSelectedItem) {
        // Set the text of the newly selected option in the selection area.
        const selection = document.getElementById('cselect-selection-' + cselectId);
        selection.innerHTML = newSelectedItem.innerHTML;

        // Switch the class and attribute setting between the previously selected item to the newly selected one.

        const oldSelectedItem = document.querySelectorAll('[id^="cselect-item-selected-'+ cselectId +'"]')[0];
        oldSelectedItem.classList.remove('cselect-item-selected');
        oldSelectedItem.removeAttribute('id');
        newSelectedItem.classList.add('cselect-item-selected');
        newSelectedItem.setAttribute('id', 'cselect-item-selected-' + cselectId + '-' + newSelectedItem.dataset.idNumber);

        // Update the selected option in the actual select.
        const select = getSelect(newSelectedItem);
        select.options[select.selectedIndex].removeAttribute('selected');
        select.options[newSelectedItem.dataset.idNumber].setAttribute('selected', 'selected');

        // Trigger a change event.
        const evt = new Event('change', {'bubbles': true});
        select.dispatchEvent(evt);
    }

    /*
     * Updates the selection in a multiple drop down list.
     */
    function updateSelectedMultiple(cselectId, newSelectedItem) {
        // Update the selection in the actual select multiple.
        const select = getSelect(newSelectedItem);
        select.options[newSelectedItem.dataset.idNumber].setAttribute('selected', 'selected');

        // Add the corresponding button item in the selection area.
        const selection = document.getElementById('cselect-selection-' + cselectId);
        const buttonItem = createButtonItem(select, newSelectedItem.dataset.idNumber);
        selection.appendChild(buttonItem);

        // Get the items in the item container.
        const items = document.getElementById('cselect-item-container-' + cselectId).childNodes;

        // Loop through the items
        for (let i = 0; i < items.length; i++) {
            // Set the attributes of the newly selected item.
            if (items[i].dataset.idNumber == newSelectedItem.dataset.idNumber) {
                items[i].classList.add('cselect-item-selected');
                items[i].setAttribute('id', 'cselect-item-selected-' + cselectId + '-' + newSelectedItem.dataset.idNumber);
            }
        }

        // Trigger a change event.
        const evt = new Event('change', {'bubbles': true});
        select.dispatchEvent(evt);
    }

    /*
     *  Returns the name of the actual select for a given element.
     */
    function getSelectName(elem) {
        if (elem.classList.contains('cselect-selection') || elem.classList.contains('cselect-item-container')) {
            // The CSelect main container is the parent of the element.
            return elem.parentElement.dataset.selectName;
        }

        if (elem.classList.contains('cselect-button') || elem.classList.contains('cselect-item')) {
            // The CSelect main container is the parent's parent of the element.
            return elem.parentElement.parentElement.dataset.selectName;
        }

        return null;
    }

    /*
     *  Returns the actual select element for a given element.
     */
    function getSelect(elem) {
        const selectName = getSelectName(elem);

        if (selectName !== null) {
            return document.getElementsByName(selectName)[0];
        }

        return null;
    }

    function closeAllLists(elem) {
        // Get all the custom selects.
        const cselects = document.getElementsByClassName('cselect-container');

        for (let i = 0; i < cselects.length; i++) {
            const idNb = cselects[i].dataset.idNumber;
            const selection = document.getElementById('cselect-selection-' + idNb);
            const search = document.getElementById('cselect-search-' + idNb);
            const itemContainer = document.getElementById('cselect-item-container-' + idNb);

            if (elem != selection && elem != itemContainer && elem != search) {
                itemContainer.style.display = 'none';
            }
        }
    }

    /*
     * Compute a brand new CSelect id number. 
     */
    function getNewIdNumber() {
        // Get all the select tags that are already managed by CSelect (ie: containing a cselect-id data attribute).
        const selects = document.querySelectorAll('select[data-cselect-id]');

        let newIdNumber = 0;

        // Loop through the selects. 
        for (let i = 0; i < selects.length; i++) {
            // Retrieve the cselect item through the cselect id value (stored in the actual select), 
            // then get the cselect id number stored in the cselect item.
            const idNumber = document.getElementById(selects[i].dataset.cselectId).dataset.idNumber;
            // Get the highest id number.
            newIdNumber = idNumber > newIdNumber ? idNumber : newIdNumber;
        }

        // Increase the highest existing id number to create a brand new id number. 
        return parseInt(newIdNumber) + 1;
    }


    // Function used as constructor.
    const _CSelect = function() {};

    // Public functions.

    _CSelect.prototype = {
        setCSelect: function(select) {
            // Check for the cselect class. Add it if needed.
            if (!select.classList.contains('cselect')) {
                select.classList.add('cselect');
            }

            createCSelect(select);
        },

        rebuildCSelect: function(select) {
            const cselect = document.getElementById(select.dataset.cselectId);
            const idNumber = cselect.dataset.idNumber;

            // Remove the current CSelect item.
            cselect.remove();

            createCSelect(select, idNumber);
        }
    };

    return {
        init: _CSelect
    }

})();
