document.addEventListener('DOMContentLoaded', () => {
    /*
     * Sets and manages a main category in the category drop down list with multiple selections.
     */

    // Get the current value of the main category id.
    let mainCatId = document.getElementById('main-cat-id').value;
    // Retrieve the cselect id set as data attribute in the actual select.
    const cselectId = document.getElementById('categories').dataset.cselectId;

    // Get the selection area which is the first child of the CSelect item container.
    const selection = document.getElementById(cselectId).firstChild;

    // Loop through the buttons, if any (ie: the selected option items).
    for (const button of selection.children) {
        // Set the corresponding button as the main category.
        if (button.dataset.value == mainCatId) {
            button.classList.add('cselect-main-item');
        }
    }

    document.addEventListener('click', function(evt) {

        // A label button has been clicked.
        if (evt.target.tagName == 'SPAN' && evt.target.classList.contains('cselect-button-label')) {
            const clickedButton = evt.target.parentElement;

            // Do not set a disabled item as main category.
            if (clickedButton.classList.contains('cselect-button-disabled')) {
                return;
            }

            const cselect = document.getElementById(clickedButton.dataset.cselectId);

            // Make sure the option item is part of the CSelect shake drop down list.
            if (cselect.dataset.selectName == 'categories[]') {
                const selection = cselect.firstChild;

                // Loop through the selected items (buttons) in the selection area.
                for (const button of selection.children) {
                    // Set the clicked button as the main category.
                    if (button.dataset.value == clickedButton.dataset.value) {
                        button.classList.add('cselect-main-item');
                        document.getElementById('main-cat-id').value = clickedButton.dataset.value;
                    }
                    else {
                        // Reset the button previously set as main category.
                        button.classList.remove('cselect-main-item');
                    }
                }
            }
        }

        // A button has been closed.
        if (evt.target.tagName == 'SPAN' && evt.target.classList.contains('cselect-button-close')) {
            const closedButton = evt.target.parentElement;
            const cselect = document.getElementById(closedButton.dataset.cselectId);

            // Make sure the option item is part of the CSelect shake drop down list.
            if (cselect.dataset.selectName == 'categories[]') {
                const selection = cselect.firstChild;

                // There is no more button in the selection area.
                if (selection.children.length == 0) {
                    // Set the main category id value as empty.
                    document.getElementById('main-cat-id').value = '';
                    return;
                }

                // The closed button was set as main category.
                if (closedButton.classList.contains('cselect-main-item')) {
                    // Set the next button in the selection area as main category.
                    for (let i = 0; i < selection.childNodes.length; i++) {
                        // Make sure a disabled item is not set as main category.
                        if (!selection.childNodes[i].classList.contains('cselect-button-disabled')) {
                            document.getElementById('main-cat-id').value = selection.childNodes[i].dataset.value;
                            selection.childNodes[i].classList.add('cselect-main-item');
                            // Quit as only one button must be set as main category.
                            return;
                        }

                    }
                }
            }
        }

        // A new option item has been selected.
        if (evt.target.tagName == 'DIV' && evt.target.classList.contains('cselect-item')) {
            const optionItem = evt.target;
            // Get the CSelect dropdownlist (ie: the parent's parent of the option item).
            const cselect = optionItem.parentElement.parentElement;

            // Make sure the option item is part of the CSelect shake drop down list.
            if (cselect.dataset.selectName == 'categories[]') {
                const selection = cselect.firstChild;

                let button = null;
                let isMainItem = true;

                // Loop through the buttons in the selection area.
                for (let i = 0; i < selection.childNodes.length; i++) {
                    // Get the button corresponding to the newly selected option item.
                    if (selection.childNodes[i].dataset.value == optionItem.dataset.value) {
                        button = selection.childNodes[i];
                        continue;
                    }

                    // Another option item is already set as main category.
                    if (selection.childNodes[i].classList.contains('cselect-main-item')) {
                        isMainItem = false;
                    }
                }

                if (isMainItem) {
                    // Set the selected option as the main category.
                    button.classList.add('cselect-main-item');
                    document.getElementById('main-cat-id').value = button.dataset.value;
                }
            }
        }

    }, false);
});

