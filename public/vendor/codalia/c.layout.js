// Anonymous function with namespace.
const C_Layout = (function() {

    // Private methods and properties.

    // Initializes some utility variables
    let _idNbList = [];
    let _removedIdNbs = [];  // Used to keep each id unique during the session (ie: do not reuse the id of a deleted item).
    let _container = null;
    let _addButtonContainer = null;
    let _selectItem = null;

    /**
      * Creates an HTML element of the given type.
      *
      * @param   string   type        The type of the element.
      * @param   object   attributes  The element attributes.
      *
      * @return  object   The HTML element.
    */
    function _createElement(type, attributes) {
	let element = document.createElement(type);
	// Sets the element attributes (if any).
	if (attributes !== undefined) {
	    for (let key in attributes) {
		// Ensures that key is not a method/function.
		if (typeof attributes[key] !== 'function') {
		    element.setAttribute(key, attributes[key]);
		}
	    }
	 }

	 return element;
    }

    /**
      * Computes a new item id number according to the item divs which are already in the
      * container as well as those recently removed.
      *
      * @return  integer   The new id number.
     */
    function _getNewIdNumber() {
	let newIdNb = 0;
	// Loops through the id number list.
	for (let i = 0; i < _idNbList.length; i++) {
	    // If the item id number is greater than the new one, we use it.
	    if (_idNbList[i] > newIdNb) {
		newIdNb = _idNbList[i];
	    }
	}

	// Checks against the recently removed items.
        for (let i = 0; i < _removedIdNbs.length; i++) {
	    if (_removedIdNbs[i] > newIdNb) {
		newIdNb = _removedIdNbs[i];
	    }
	}

	// Returns a valid id number (ie: the highest id number in the container plus 1).
	return newIdNb + 1;
    }

    function _isEmpty(text) {
        return text === null || text.match(/^ *$/) !== null;
    };

    /**
      * Returns the type of the given item. 
      *
      * @return  string   The item type.
     */
    function _getItemType(idNb) {
        return document.getElementById('layout-item-' + idNb).dataset.type;
    }

    /**
      * Creates a new TinyMce editor instance for the given textarea.
      *
      * @param   integer idNb   The id number of the textarea item.
      *
      * @return  Object A TinyMce editor instance.
     */
    function _initTinyMceEditor(idNb) {
        let editor = tinymce.init({
	    selector: '#text_block-'+idNb,
	    plugins: 'code link lists',
	    entity_encoding: 'raw',
	    menubar: false,
	    //forced_root_block : false,
	    toolbar: 'code | bold italic underline | link | numlist',
	    height: 400,
	    convert_urls: false,
            setup: function(editor) {
                editor.on('change', function () {
                    editor.save();
                });
                // Disable the shift + enter key combination.
                /*editor.on('keydown', function (event) {
                    if (event.keyCode == 13 && event.shiftKey)  {
                        console.log(event);
                        event.preventDefault();
                        event.stopPropagation();
                        return false;
                    }
                });*/
            }
	});

        return editor;
    }

    /**
      * Creates a new structure for the item.
      *
      * @param   integer idNb       The id number for the structure.
      * @param   string  itemType   The item type.
      *
      * @return  void
     */
    function _createItemStructure(idNb, itemType) {
        // A new group item actually starts with a group_start item.
        itemType = (itemType == 'group') ? 'group_start' : itemType;
        // Creates the item div then its inner structure.
        let attribs = {id: 'layout-item-'+idNb, class: 'layout-item', 'data-type': itemType};
        let itemContainer = _createElement('div', attribs);

        for (let i = 0; i < 3; i++) {
            cellNb = i + 1;

            let attribs = {
                id: 'layout-item-row-1-cell-'+cellNb+'-'+idNb,
                class: 'layout-item-cells-row-1 layout-item-cell-'+cellNb
            };

            itemContainer.appendChild(_createElement('div', attribs));
        }

        _container.appendChild(itemContainer);

        // Creates first an empty label.
        attribs = {class: 'item-space', id: 'layout-item-delete-label-'+idNb};
        document.getElementById('layout-item-row-1-cell-3-'+idNb).appendChild(_createElement('span', attribs));
        document.getElementById('layout-item-delete-label-'+idNb).innerHTML = '&nbsp;';

        if (itemType == 'group_end') {
            // Create a fake button for group_end items.
            let label = CodaliaLang.action['remove'];
            let attribs = {class: 'btn btn-secondary', title: label, disabled:'disabled'};
            let button = _createElement('button', attribs);
            button.innerHTML = '<span class="icon-remove icon-white"></span> '+label;
            document.getElementById('layout-item-row-1-cell-3-'+idNb).appendChild(button);
        }
        else {
            document.getElementById('layout-item-row-1-cell-3-'+idNb).appendChild(_createButton('remove', idNb));
        }

        // Element label
        attribs = {'title': CodaliaLang.layout[itemType], 'class':'item-label', 'id':'layout-item-label-'+idNb};
        document.getElementById('layout-item-row-1-cell-1-'+idNb).append(_createElement('span', attribs));
        document.getElementById('layout-item-label-'+idNb).textContent = CodaliaLang.layout[itemType];
    }

    /**
      * Inserts an ordering functionality in the given item. This functionality allows the
      * items to go up or down into the item list.
      *
      * @param   integer idNb   The id number of the item.
      *
      * @return  void
     */
    function _setItemOrdering(idNb) {
	// Creates first an empty label.
	let attribs = {class: 'item-space', id: 'layout-item-ordering-label-'+idNb};
	document.getElementById('layout-item-row-1-cell-2-'+idNb).appendChild(_createElement('span', attribs));
	document.getElementById('layout-item-ordering-label-'+idNb).innerHTML = '&nbsp;';

	// Creates a ordering container.
	attribs = {class: 'ordering-div', id: 'layout-item-ordering-div-'+idNb};
	document.getElementById('layout-item-row-1-cell-2-'+idNb).appendChild(_createElement('div', attribs));

	// Creates the element in which the item ordering number is stored.
	attribs = {type: 'hidden', name: 'layout_items[layout_item_ordering_'+idNb+']', id: 'layout-item-ordering-'+idNb};
	document.getElementById('layout-item-ordering-div-'+idNb).appendChild(_createElement('input', attribs));

        // group_start item types can't go down.
        if (_getItemType(idNb) != 'group_start') {
            // Creates the link allowing the item to go down the item ordering.
            attribs = {
                href: 'javascript:void(0);',
                id: 'layout-item-down-ordering-'+idNb,
                class: 'down-ordering'
            };

            let link = _createElement('a', attribs);

            attribs = {class: 'fa fa-angle-double-down'};

            link.appendChild(_createElement('i', attribs));
            document.getElementById('layout-item-ordering-div-'+idNb).appendChild(link);
        }

	// Creates fake element to display the order number.
	attribs = {type: 'text', disabled: 'disabled', id: 'layout-item-order-number-'+idNb, class: 'layout-item-order-number'};
	document.getElementById('layout-item-ordering-div-'+idNb).appendChild(_createElement('input', attribs));

        // group_end item types can't go up.
        if (_getItemType(idNb) != 'group_end') {
            // Creates the link allowing the item to go up the item ordering.
            attribs = {
                 href: 'javascript:void(0);',
                 id: 'layout-item-up-ordering-'+idNb,
                 class: 'up-ordering'
            };

            link = _createElement('a', attribs);

            attribs = {class: 'fa fa-angle-double-up'};

            link.appendChild(_createElement('i', attribs));
            document.getElementById('layout-item-ordering-div-'+idNb).appendChild(link);
        }

        _assignOrderingElements(idNb);

	_itemReordering();
    }

    /**
     * Assign the _reverseOrder function to the ordering elements on the click event.
     *
     * @param   integer   idNb  The item id number.
     *
     * @return  void
    */
    function _assignOrderingElements(idNb) {
        let directions = ['up', 'down'];

        for (let i = 0; i < directions.length; ++i){
            // _reverseOrder function is partialy needed in group item types.
            if ((directions[i] == 'up' && _getItemType(idNb) == 'group_end') || (directions[i] == 'down' && _getItemType(idNb) == 'group_start')) {
                continue;
            } 

            // Assign the _reverseOrder function to the newly created up and down elements.
            document.getElementById('layout-item-'+directions[i]+'-ordering-'+idNb).addEventListener('click', function() {
                _reverseOrder(directions[i], idNb);
            }, true);
        }
    }

    /**
     * Switches the order of 2 items in the DOM.
     *
     * @param   string  direction  The direction to go when switching (up/down).
     * @param   integer idNb       The id number of the item to switch from.
     *
     * @return  void
    */
    function _reverseOrder(direction, idNb) {
        // 
        const referenceItem = document.getElementById('layout-item-' + idNb);

        if (referenceItem.dataset.type.startsWith('group_')) {
            _reverseGroup(referenceItem);
            return;
        }

        // Loops through the item id number order.
        for (let i = 0; i < _idNbList.length; i++) {
            // Checks in which order the 2 items have to be reversed.
            if (_idNbList[i] == idNb) {
                const index = (direction == 'up') ? i - 1 : i + 1;
                // Momentarily withdraws the item to switch from the DOM.
                const itemToSwitch = _container.removeChild(document.getElementById('layout-item-' + _idNbList[index]));
                const position = (direction == 'up') ? 'afterend' : 'beforebegin';
                // Switch the item according to the position.
                referenceItem.insertAdjacentElement(position, itemToSwitch);

                if (itemToSwitch.dataset.type == 'text_block') {
                    // Remove then reinstanciate a brand new TinyMce editor.
                    tinymce.get('text_block-'+_idNbList[index]).remove();
                    _initTinyMceEditor(_idNbList[index]);
                }

                /*
                // Sets the item indexes according to the direction.
                // Proceed the down direction by default.

                // Switch the selected item with the item below.
                let index1 = i; 
                let index2 = i + 1;

                if (direction == 'up') {
                    // Switch the selected item with the item above.
                    index1 = i - 1;  
                    index2 = i; 
                }

                if (!_checkGroupItemOverlapping(index1, index2)) {
                    return;
                }

                // Gets the reference item before which the other item will be inserted.
                let refItem = document.getElementById('layout-item-'+_idNbList[index1]);
                // Momentarily withdraws the other item from the DOM.
                let oldChild = _container.removeChild(document.getElementById('layout-item-'+_idNbList[index2]));


                // Switches the 2 items.
                _container.insertBefore(oldChild, refItem);

                if (oldChild.dataset.type === 'text_block') {
                    // Remove then reinstanciate a brand new TinyMce editor.
                    tinymce.get('text_block-'+_idNbList[index2]).remove();
                    _initTinyMceEditor(_idNbList[index2]);
                }*/

                break;
            }
        }

        _itemReordering();
        // The "odd" and "even" classes need to be reset.
        _setOddEven();
    }

    /**
     * Updates the order value of the items according to their position into the item 
     * container.
     *
     * @return  void
    */
    function _itemReordering() {
        // Collects all the layout item divs (ie: divs with a layout-item class) in the container.
        let divs = _container.querySelectorAll('div.layout-item');
        // Empties the id number list.
        _idNbList = [];

        // Loops through the item divs.
        for (let i = 0; i < divs.length; i++) {
	    let ordering = i + 1;
	    // Extracts the id number of the item from the end of its id value and convert it into an integer.
	    let idNb = parseInt(divs[i].id.replace(/.+-(\d+)$/, '$1'));
	    // Updates the ordering of the id number.
	    _idNbList.push(idNb);
            const itemType = _getItemType(idNb);

	    // Updates the item ordering.
	    document.getElementById('layout-item-ordering-'+idNb).value = ordering;
	    document.getElementById('layout-item-order-number-'+idNb).value = ordering;

            if (itemType != 'group_end') {
                // Displays the up/down links of the item.
                document.getElementById('layout-item-up-ordering-'+idNb).style.display = 'inline';
            }

            if (itemType != 'group_start') {
                document.getElementById('layout-item-down-ordering-'+idNb).style.display = 'inline';
            }

	    // Resets first and last item classes.
	    document.getElementById('layout-item-order-number-'+idNb).classList.remove('first-item', 'last-item');

	    if (ordering == 1 && itemType != 'group_end') {
                // The first item cannot go any higher.
                document.getElementById('layout-item-up-ordering-'+idNb).style.display = 'none';
                document.getElementById('layout-item-order-number-'+idNb).classList.add('first-item');
	    }

	    if (ordering == divs.length && itemType != 'group_start') {
                // The last item cannot go any lower.
                document.getElementById('layout-item-down-ordering-'+idNb).style.display = 'none';
                document.getElementById('layout-item-order-number-'+idNb).classList.add('last-item');
	    }
        }
    }

    /**
      * Make sure the couples of group items don't overlap each others when reversing the item order.
      *
      * @param   integer index1   The first item id number 
      * @param   integer index2   The second item id number 
      *
      * @return  boolean
     */
    function _checkGroupItemOverlapping(index1, index2) {
        const item1Type = _getItemType(_idNbList[index1]);
        const item2Type = _getItemType(_idNbList[index2]);

        if (item1Type == 'group_start') {
//console.log(_idNbList);
        }

        if (item2Type == 'group_start' && item1Type == 'group_end' || item2Type == 'group_end' && item1Type == 'group_start') {
            alert(CodaliaLang.message['alert_overlapping']);
            return false;
        }

        return true;
    }

    function _reverseGroup(item) {

    }

    /**
      * Creates a button then binds it to a function according to the action.
      *
      * @param   string  action The action that the button triggers.
      * @param   integer idNb   The item id number (for remove action).
      * @param   string  modal  The url to the modal window (for select action).
      *
      * @return  object         The created button.
     */
    function _createButton(action, idNb, modal) {
	// Creates a basic button.
	let label = CodaliaLang.action[action];
	let attribs = {class: 'btn', title: label};
	let button = _createElement('button', attribs);
	let classes = {add: 'btn-primary', remove: 'btn-danger', clear: 'btn'};
	let icons = {add: 'plus-circle', remove: 'times-circle', clear: 'remove'};

	if (action == 'add') {
	    button.addEventListener('click', (e) => { e.preventDefault(); C_Layout.init.prototype.createItem(); } );
	}

	if (action == 'remove') {
	    button.addEventListener('click', (e) => { e.preventDefault(); _removeItem(idNb, true); } );
	}

	if (action == 'clear') {
	    button.addEventListener('click', (e) => { e.preventDefault(); } );
	    button.classList.add('clear-btn');
	    // No label on the clear button.
	    label = '';
	}

	button.classList.add(classes[action]);
	button.innerHTML = '<span class="icon-'+icons[action]+' icon-white"></span> '+label;

	return button;
    }

    /**
      * Removes the item corresponding to the given id number.
      *
      * @param   string   idNb     The id number of the item to remove.
      * @param   string   warning  If true a confirmation window is shown before deletion.
      *
      * @return  void
     */
    function _removeItem(idNb, warning) {

	if (warning) {
	    // Asks the user to confirm deletion.
	    if (confirm(CodaliaLang.message.warning_remove_dynamic_item) === false) {
		return;
	    }
	}

        let itemType = document.getElementById('layout-item-'+idNb).dataset.type;

	// Calls a callback function to execute possible tasks before the item deletion.
	// N.B: Check first that the function has been defined.
	if (typeof window['beforeRemoveItem'] === 'function') {
	    window['beforeRemoveItem'](idNb, itemType);
	}

	// Removes the item from its div id.
	_container.removeChild(document.getElementById('layout-item-'+idNb));
	// Stores the removed id number.
	_removedIdNbs.push(idNb);

	_itemReordering();

	_setOddEven();

	// Calls a callback function to execute possible tasks after the item deletion.
	// N.B: Check first that the function has been defined.
	if (typeof window['afterRemoveItem'] === 'function') {
	    window['afterRemoveItem'](idNb, itemType);
	}
    }
  
    /**
     * Adds the odd or even class to the items according to their position into the list.
     *
     * @return  void
    */
    function _setOddEven() {
        let group = '';
        let j = 0;

        // Loops through the id number list.
        for (let i = 0; i < _idNbList.length; i++) {
            // Gets the item.
            let itemContainer = document.getElementById('layout-item-'+_idNbList[i]);
            // First removes the current class.
            itemContainer.classList.remove('layout-item-odd');
            itemContainer.classList.remove('layout-item-even');
            itemContainer.classList.remove('layout-item-a-odd');
            itemContainer.classList.remove('layout-item-a-even');
            itemContainer.classList.remove('layout-item-b-odd');
            itemContainer.classList.remove('layout-item-b-even');
            itemContainer.classList.remove('layout-item-group-start');
            itemContainer.classList.remove('layout-item-group-end');
            itemContainer.classList.remove('layout-item-in-group');

            if (itemContainer.dataset.type == 'group_start') {
                j++;
                group = 'b-';
                if ((j + 1) % 2) {
                    group = 'a-';
                }

                itemContainer.classList.add('layout-item-group-start');
            }

            // Uses the modulo operator to add the proper class.
            if ((i + 1) % 2) {
                itemContainer.classList.add('layout-item-'+group+'odd');
            }
            else {
                itemContainer.classList.add('layout-item-'+group+'even');
            }

            if (itemContainer.dataset.type != 'group_start' && itemContainer.dataset.type != 'group_end' && group != '') {
                itemContainer.classList.add('layout-item-in-group');
            }

            if (itemContainer.dataset.type == 'group_end') {
                itemContainer.classList.add('layout-item-group-end');
                group = '';
            }
        }
    }

    // Functions that create layout items.

    function _createTitle(idNb, data) {
        let value = (data !== undefined) ? data.text : '';

	let attribs = {'type':'text', 'name':'layout_items[title_'+idNb+']', 'id':'title-'+idNb, 'class':'form-control', 'value':value};
	document.getElementById('layout-item-row-1-cell-1-'+idNb).append(_createElement('input', attribs));
    }

    function _createTextBlock(idNb, data) {
        let value = (data !== undefined) ? data.text : '';

	let attribs = {'name':'layout_items[text_block_'+idNb+']', 'id':'text_block-'+idNb, 'class':'form-control'};
	document.getElementById('layout-item-row-1-cell-1-'+idNb).append(_createElement('textarea', attribs));
	document.getElementById('text_block-'+idNb).value = value;
        _initTinyMceEditor(idNb);
    }

    function _createImage(idNb, data) {
        let thumbnail = (data !== undefined) ? data.data.thumbnail : '/images/camera.png';
        let altText = (data !== undefined) ? data.text : '';
        let status = (data !== undefined) ? 'update' : 'new';
        let siteUrl = document.getElementById('siteUrl').value;

	// Create a global row to wrap the fields related to the image.
	let attribs = {id: 'layout-item-row-image-'+idNb, class: 'row'};
	document.getElementById('layout-item-row-1-cell-1-'+idNb).append(_createElement('div', attribs));

	// Create a div to wrap both the upload and alt fields.
	attribs = {id: 'layout-item-image-'+idNb, class: 'col-lg-10'};
	document.getElementById('layout-item-row-image-'+idNb).append(_createElement('div', attribs));

	attribs = {'type':'file', 'name':'layout_items[upload_'+idNb+']', 'id':'layout-item-upload-'+idNb, 'class':'form-control layout-item-upload'};
	document.getElementById('layout-item-image-'+idNb).append(_createElement('input', attribs));
	attribs = {'type':'text', 'name':'layout_items[alt_text_'+idNb+']', 'id':'alt-text-'+idNb, 'class':'form-control layout-item-alt-text', 'value':altText};
	document.getElementById('layout-item-image-'+idNb).append(_createElement('input', attribs));

	// Create a div to wrap the image field.
	attribs = {id: 'layout-item-thumbnail-div-'+idNb, class: 'col-lg-2 text-center'};
	document.getElementById('layout-item-row-image-'+idNb).append(_createElement('div', attribs));

	attribs = {id: 'layout-item-thumbnail-'+idNb, src: siteUrl+thumbnail, class:'rounded'};
	document.getElementById('layout-item-thumbnail-div-'+idNb).append(_createElement('img', attribs));
	attribs = {'type':'hidden', 'id':'layout-item-image-status-'+idNb, 'value':status};
	document.getElementById('layout-item-thumbnail-div-'+idNb).append(_createElement('input', attribs));
    }

    function _createGroup(idNb, data) {
        let value = '';
        //
        let type = (data !== undefined) ? data.type : 'group_start';
        let separator = ' ------------------------------------------ ';

        if (data !== undefined) {
            if (type == 'group_start') {
               value = data.data.class !== undefined ? data.data.class : '';
               // Check for the groups_in_row attribute.
               value = data.data.groups_in_row !== undefined && data.data.groups_in_row != '' ? value+'|'+data.data.groups_in_row : value;
            }
            else {
               value = data.data.parent_id;
            }
        }

	let attribs = {'type':'text', 'name':'layout_items['+type+'_'+idNb+']', 'id':type+'-'+idNb, 'class':'form-control', 'value':value};

        if (type == 'group_end') {
            // Create a fake field to use it as "separator".
            attribs = {'type':'text', 'id':'group-'+idNb, 'disabled':'disabled', 'class':'form-control', 'value':separator+CodaliaLang.layout['group_end']+separator};
            document.getElementById('layout-item-row-1-cell-1-'+idNb).append(_createElement('input', attribs));
            attribs = {'type':'hidden', 'name':'layout_items[group_end_'+idNb+']', 'id':'group_end-'+idNb, 'value':value};
        }

        document.getElementById('layout-item-row-1-cell-1-'+idNb).append(_createElement('input', attribs));

        // Group items go by two (start and end). 
        if (data === undefined) {
            // Store the id number of the group_start item previously created.
	    _setItemOrdering(idNb);
	    _setOddEven();

            // Create a brand new group_end item.
            let newIdNb = _getNewIdNumber();
            _createItemStructure(newIdNb, 'group_end');

            // Create a fake field to use it as "separator".
            attribs = {'type':'text', 'id':'group-'+newIdNb, 'disabled':'disabled', 'class':'form-control', 'value':separator+CodaliaLang.layout['group_end']+separator};
            document.getElementById('layout-item-row-1-cell-1-'+newIdNb).append(_createElement('input', attribs));
            // Link the 2 group items by setting the value with the id number of the corresponding group_start item.
            attribs = {'type':'hidden', 'name':'layout_items[group_end_'+newIdNb+']', 'id':'group_end-'+newIdNb, 'value':idNb};
            document.getElementById('layout-item-row-1-cell-1-'+newIdNb).append(_createElement('input', attribs));
            // Return the new id number to the createItem function.
            return newIdNb;
        }

        return idNb;
    }

    // Function used as a class constructor.
    const _Layout = function() {

	// Creates the item container as well as the add button container.
	let attribs = {'id':'layout-item-container', 'class':'layout-item-container'};
	_container = _createElement('div', attribs);
	attribs = {'id':'layout-item-add-button-container', 'class':'add-button-container'};
	_addButtonContainer = _createElement('div', attribs);
	attribs = {name: 'select_layout_item', id:'select-layout-item', class:'form-control custom-select'};
	let _selectItem = _createElement('select', attribs);

	// Builds the select options.
	let items = ['title', 'text_block', 'image', 'group'];
	let options = '';

	for (let i = 0; i < items.length; i++) {
	    let value = items[i];
	    let text = CodaliaLang.layout[items[i]];
	    let selected = '';

	    options += '<option value="'+value+'" '+selected+'>'+text+'</option>';
	}

	// Adds both the div and add button containers to the DOM.
	document.getElementById('layout-items').appendChild(_container);
	document.getElementById('layout-item-container').appendChild(_addButtonContainer);
	document.getElementById('layout-item-container').appendChild(_selectItem);
	document.getElementById('select-layout-item').innerHTML = options;

	// Inserts the add button.
	let button = _createButton('add');
	_addButtonContainer.appendChild(button);
    }

    _Layout.prototype = {
        // Public methods.

        createItem: function(data) {
            // Set the item type to create according to the data value.
	    let itemType = (data !== undefined) ? data.type : document.getElementById('select-layout-item').value;

	    // Sets the id number for the item.
	    let idNb = null;

	    if (data !== undefined && data.id_nb !== undefined) {
		// Uses the given item id.
		idNb = data.id_nb;
	    }
	    else {
		// Gets a brand new id number for the item.
		idNb = _getNewIdNumber();
	    }

            _createItemStructure(idNb, itemType);

	    switch (itemType) {
	        case 'title':
		    _createTitle(idNb, data);
		    break;
	        case 'text_block':
		    _createTextBlock(idNb, data);
		    break;
	        case 'image':
		    _createImage(idNb, data);
		    break;
                // group, group_start, group_end
                default:
		   idNb = _createGroup(idNb, data);
	    }

	    _setItemOrdering(idNb);
	    _setOddEven();

            if (data === undefined) {
                window.scrollTo(0, document.body.scrollHeight);
            }
        },

        removeGroupEndItem: function(groupStartId) {
            // Loops through the id number list.
            for (let i = 0; i < _idNbList.length; i++) {
                let itemType = document.getElementById('layout-item-'+_idNbList[i]).dataset.type;

                if (itemType == 'group_end' && document.getElementById('group_end-'+_idNbList[i]).value == groupStartId) {
                    _removeItem(_idNbList[i]);
                    return;
                }
            }
        },

        validateFields: function() {
            // Loops through the id number list.
            for (let i = 0; i < _idNbList.length; i++) {
                // Gets the item.
                let itemContainer = document.getElementById('layout-item-'+_idNbList[i]);
                let itemType = itemContainer.dataset.type;

                if (itemType == 'title') {
                    if (_isEmpty(document.getElementById('title-'+_idNbList[i]).value)) {
                        document.getElementById('title-'+_idNbList[i]).classList.add('mandatory');
                        alert(CodaliaLang.message['empty_title']);

                        return false;
                    }
                    else {
                        // Remove possible previous alert. 
                        document.getElementById('title-'+_idNbList[i]).classList.remove('mandatory');
                    }
                }

                if (itemType == 'group_start') {
                    if (_isEmpty(document.getElementById('group_start-'+_idNbList[i]).value)) {
                        document.getElementById('group_start-'+_idNbList[i]).classList.add('mandatory');
                        alert(CodaliaLang.message['empty_group_class']);

                        return false;
                    }
                    else {
                        // Remove possible previous alert. 
                        document.getElementById('group_start-'+_idNbList[i]).classList.remove('mandatory');
                    }
                }

                if (itemType == 'text_block') {
                    if (_isEmpty(tinymce.get('text_block-'+_idNbList[i]).getContent())) {
                        document.getElementById('layout-item-row-1-cell-1-'+_idNbList[i]).classList.add('mandatory');
                        alert(CodaliaLang.message['empty_text_block']);

                        return false;
                    }
                    else {
                        // Remove possible previous alert. 
                        document.getElementById('layout-item-row-1-cell-1-'+_idNbList[i]).classList.remove('mandatory');
                    }
                }

                if (itemType == 'image') {
                    let maxSize = 2; // In MB
                    let types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg'];
                    let upload = document.getElementById('layout-item-upload-'+_idNbList[i]);
                    let status = document.getElementById('layout-item-image-status-'+_idNbList[i]).value;
                    // Remove possible previous alert. 
                    upload.classList.remove('mandatory');

                    if (status == 'new' && upload.files.length == 0) {
                        upload.classList.add('mandatory');
                        alert(CodaliaLang.message['no_file_selected']);
                        return false;
                    }

                    // Check for file size and type.
                    if (upload.files.length > 0) {
                        const fileSize = upload.files[0].size / 1024 / 1024; // In MB

                        if (fileSize > maxSize) {
                            upload.classList.add('mandatory');
                            alert(CodaliaLang.message['file_too_big']+maxSize+' MB.');
                            return false;
                        }

                        if (!types.includes(upload.files[0].type)) {
                            upload.classList.add('mandatory');

                            let allowedTypes = '';
                            types.forEach(function(type) {
                                allowedTypes += type+' ';
                            });

                            alert(CodaliaLang.message['file_type_not_allowed']+allowedTypes);
                            return false;
                        }
                    }
                }
            }

            return true;
        }
    };

    // Returns a init property that returns the "constructor" function.
    return {
        init: _Layout
    }

})();

