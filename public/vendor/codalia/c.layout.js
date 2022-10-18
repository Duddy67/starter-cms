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

    /**
      * Creates a new TinyMce editor instance for the given textarea.
      *
      * @param   integer idNb   The id number of the textarea item.
      *
      * @return  Object A TinyMce editor instance.
     */
    function _initTinyMceEditor(idNb) {
        let editor = tinymce.init({
	    selector: '#paragraph-'+idNb,
	    plugins: 'code link lists',
	    entity_encoding: 'raw',
	    menubar: false,
	    forced_root_block : false,
	    toolbar: 'code | bold italic underline | link | numlist',
	    //height: 500,
	    convert_urls: false,
            setup: function(editor) {
                editor.on('change', function () {
                    editor.save();
                });
            }
	});

        return editor;
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

	// Creates fake element to display the order number.
	attribs = {type: 'text', disabled: 'disabled', id: 'layout-item-order-number-'+idNb, class: 'layout-item-order-number'};
	document.getElementById('layout-item-ordering-div-'+idNb).appendChild(_createElement('input', attribs));

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
        // Loops through the item id number order.
        for (let i = 0; i < _idNbList.length; i++) {
            // Checks for the which order has to be reversed.
            if (_idNbList[i] == idNb) {
              // Sets the item indexes according to the direction.
              let index1 = i;
              let index2 = i + 1;

              if (direction == 'up') {
                  index1 = i - 1;
                  index2 = i;
              }

              // Gets the reference item before which the other item will be inserted.
              let refItem = document.getElementById('layout-item-'+_idNbList[index1]);
              // Momentarily withdraws the other items from the DOM.
              let oldChild = _container.removeChild(document.getElementById('layout-item-'+_idNbList[index2]));

              // Switches the 2 items.
              _container.insertBefore(oldChild, refItem);

              if (oldChild.dataset.type === 'paragraph') {
                  // Remove then reinstanciate a brand new TinyMce editor.
                  tinymce.get('paragraph-'+_idNbList[index2]).remove();
                  _initTinyMceEditor(_idNbList[index2]);
              }

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

	    // Updates the item ordering.
	    document.getElementById('layout-item-ordering-'+idNb).value = ordering;
	    document.getElementById('layout-item-order-number-'+idNb).value = ordering;
	    // Displays the up/down links of the item.
	    document.getElementById('layout-item-up-ordering-'+idNb).style.display = 'inline';
	    document.getElementById('layout-item-down-ordering-'+idNb).style.display = 'inline';
	    // Resets first and last item classes.
	    document.getElementById('layout-item-order-number-'+idNb).classList.remove('first-item', 'last-item');

	    if (ordering == 1) {
	      // The first item cannot go any higher.
	      document.getElementById('layout-item-up-ordering-'+idNb).style.display = 'none';
	      document.getElementById('layout-item-order-number-'+idNb).classList.add('first-item');
	    }

	    if (ordering == divs.length) {
	      // The last item cannot go any lower.
	      document.getElementById('layout-item-down-ordering-'+idNb).style.display = 'none';
	      document.getElementById('layout-item-order-number-'+idNb).classList.add('last-item');
	    }
        }
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
        // Loops through the id number list.
        for (let i = 0; i < _idNbList.length; i++) {
            // Gets the item.
            let itemContainer = document.getElementById('layout-item-'+_idNbList[i]);
            // First removes the current class.
            itemContainer.classList.remove('layout-item-odd');
            itemContainer.classList.remove('layout-item-even');

            // Uses the modulo operator to add the proper class.
            if ((i + 1) % 2) {
                itemContainer.classList.add('layout-item-odd');
            }
            else {
                itemContainer.classList.add('layout-item-even');
            }
        }
    }

    // Functions that create layout items.

    function _createTitle(idNb, data) {
        let value = (data !== undefined) ? data.value : '';

	let attribs = {'type':'text', 'name':'layout_items[title_'+idNb+']', 'id':'title-'+idNb, 'class':'form-control', 'value':value};
	document.getElementById('layout-item-row-1-cell-1-'+idNb).append(_createElement('input', attribs));
    }

    function _createParagraph(idNb, data) {
        let value = (data !== undefined) ? data.value : '';

	let attribs = {'name':'layout_items[paragraph_'+idNb+']', 'id':'paragraph-'+idNb, 'class':'form-control'};
	document.getElementById('layout-item-row-1-cell-1-'+idNb).append(_createElement('textarea', attribs));
	document.getElementById('paragraph-'+idNb).value = value;
        _initTinyMceEditor(idNb);
    }

    function _createImage(idNb, data) {
        let thumbnail = (data !== undefined) ? data.value.thumbnail : '/images/camera.png';
        let altText = (data !== undefined) ? data.value.alt_text : '';
        let siteUrl = document.getElementById('siteUrl').value;

	let attribs = {'type':'file', 'name':'layout_items[upload_'+idNb+']', 'id':'layout-item-upload-'+idNb, 'class':'form-control'};
	document.getElementById('layout-item-row-1-cell-1-'+idNb).append(_createElement('input', attribs));

	// Create the second row.
	attribs = {id: 'layout-item-row-2-cell-1-'+idNb, class: 'layout-item-cells-row-2 layout-item-cell-1-row-2'};
	document.getElementById('layout-item-'+idNb).appendChild(_createElement('span', {class: 'layout-item-row-separator'}));
	document.getElementById('layout-item-'+idNb).append(_createElement('div', attribs));

	attribs = {'title': CodaliaLang.layout['alt'], 'class':'item-label', 'id':'alt-label-'+idNb};
	document.getElementById('layout-item-row-2-cell-1-'+idNb).append(_createElement('span', attribs));
	document.getElementById('alt-label-'+idNb).textContent = CodaliaLang.layout['alt'];

	attribs = {'type':'text', 'name':'layout_items[alt_text_'+idNb+']', 'id':'alt-text-'+idNb, 'class':'form-control', 'value':altText};
	document.getElementById('layout-item-row-2-cell-1-'+idNb).append(_createElement('input', attribs));

	attribs = {id: 'layout-item-row-2-cell-2-'+idNb, class: 'layout-item-cells-row-2 layout-item-cell-2-row-2'};
	document.getElementById('layout-item-'+idNb).appendChild(_createElement('span', {class: 'layout-item-row-separator'}));
	document.getElementById('layout-item-'+idNb).append(_createElement('div', attribs));

	attribs = {id: 'layout-item-thumbnail-'+idNb, src: siteUrl+thumbnail};
	document.getElementById('layout-item-row-2-cell-2-'+idNb).append(_createElement('img', attribs));
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
	let items = ['title', 'paragraph', 'image'];
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

	    // Creates the item div then its inner structure.
	    let attribs = {id: 'layout-item-'+idNb, class: 'layout-item', 'data-type': itemType};
	    let itemContainer = _createElement('div', attribs);

	    for (let i = 0; i < 3; i++) {
		cellNb = i + 1;

		let attribs = {
		    id: 'layout-item-row-1-cell-'+cellNb+'-'+idNb,
		    class: 'layout-item-cells-row-1'
		};

		itemContainer.appendChild(_createElement('div', attribs));
	    }

	    _container.appendChild(itemContainer);

	    // Creates first an empty label.
	    attribs = {class: 'item-space', id: 'layout-item-delete-label-'+idNb};
	    document.getElementById('layout-item-row-1-cell-3-'+idNb).appendChild(_createElement('span', attribs));
	    document.getElementById('layout-item-delete-label-'+idNb).innerHTML = '&nbsp;';

	    document.getElementById('layout-item-row-1-cell-3-'+idNb).appendChild(_createButton('remove', idNb));
	    // Element label
	    attribs = {'title': CodaliaLang.layout[itemType], 'class':'item-label', 'id':'layout-item-label-'+idNb};
	    document.getElementById('layout-item-row-1-cell-1-'+idNb).append(_createElement('span', attribs));
	    document.getElementById('layout-item-label-'+idNb).textContent = CodaliaLang.layout[itemType];

	    switch (itemType) {
	        case 'title':
		    _createTitle(idNb, data);
		    break;
	        case 'paragraph':
		    _createParagraph(idNb, data);
		    break;
	        case 'image':
		    _createImage(idNb, data);
		    break;
	    }

	    _setItemOrdering(idNb);
	    _setOddEven();
        }
    };

    // Returns a init property that returns the "constructor" function.
    return {
        init: _Layout
    }

})();

