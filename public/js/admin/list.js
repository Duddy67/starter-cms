(function($) {
    const messages = (document.getElementById('JsMessages')) ? JSON.parse(document.getElementById('JsMessages').value) : {};

    // Run a function when the page is fully loaded including graphics.
    $(window).on('load', function() {
	let actions = ['create', 'massDestroy', 'batch', 'checkin', 'publish', 'unpublish'];

	actions.forEach(function (action) {
	    $('#'+action).click( function() { $.fn[action](); });
	});

	$('.clickable-row').click(function(event) {
	    if(!$(event.target).hasClass('form-check-input')) {
		$(this).addClass('active').siblings().removeClass('active');
		window.location = $(this).data('href');
	    }
	});

	$('#toggle-select').click (function () {
	     var checkedStatus = this.checked;
	    $('#item-list tbody tr').find('td:first :checkbox').each(function () {
		$(this).prop('checked', checkedStatus);
	     });
	});

        /** Filters **/

	$('select[id^="filters-"]').change(function() {
	    $.fn.checkEmptyFilters();
	    $('#item-filters').submit();
	});

	$('#search-btn').click(function() {
	    if ($('#search').val() !== '') {
		$.fn.checkEmptyFilters();
		$('#item-filters').submit();
	    }
	});

	$('#clear-search-btn').click(function() {
	    $('#search').val('');
	    $.fn.checkEmptyFilters();
	    $('#item-filters').submit();
	});

	$('#clear-all-btn').click(function() {
	    $('select[id^="filters-"]').each(function(index) {
		$(this).empty();
	    });

	    $('#search').val('');
	    $.fn.checkEmptyFilters();

	    $('#item-filters').submit();
	});

        /* Numerical order (if any) */
        if ($('#canOrderBy').length) {
            // Enable or disable the order options accordingly.
            $('#filters-sortedBy option').each(function(index) {
                if ($(this).val() == 'order_asc' || $(this).val() == 'order_desc') {
                    if ($('#canOrderBy').val()) {
                        $(this).prop('disabled', false);
                    }
                    else {
                        $(this).prop('disabled', true);
                        if ($('#filters-sortedBy option:selected').val() == 'order_asc' || $('#filters-sortedBy option:selected').val() == 'order_desc') {
                            $('option:selected').removeAttr('selected');
                            $('#filters-sortedBy').trigger('change.select2');
                        }
                    }
                }
            });

        }
    });

    /*
     * Prevents the parameters with empty value to be send in the url query.
     */
    $.fn.checkEmptyFilters = function() {
	$('select[id^="filters-"]').each(function(index) {
	    if($(this).val() === null || $(this).val() === '') {
		$(this).prop('disabled', true);
	    }

	    // Reinitialize pagination on each request.
	    if ($('#filters-pagination').length) {
		$('#filters-pagination').prop('disabled', true);
	    }

	    if ($('#search').val() === '') {
		$('#search').prop('disabled', true);
	    }
	});
    }

    $.fn.setSelectedItems = function() {
	let ids = [];
	let inputs = '';

	$('.form-check-input:checkbox:checked').each(function () {
	    ids.push($(this).data('item-id'));
	});

	if (ids.length === 0) {
	    alert(messages.no_item_selected);
	    return false;
	}

        // Remove a possible previous selection from the selectedItems form.
	$('input[name="ids\\[\\]"]').each(function () {
	    $(this).remove();
	});

	for (let i = 0; i < ids.length; i++) {
	    inputs += '<input type="hidden" name="ids[]" value="'+ids[i]+'">';
	}
	
	$('#selectedItems').append(inputs);

        // Check for batch iframe.
        let iframe = $('iframe[name="batch"]');
	if (iframe.length) {
	    // Remove a possible previous selection from the batchForm form.
	    $('input[name="ids\\[\\]"]', iframe.contents()).each(function () {
		$(this).remove();
	    });

	    $('#batchForm', iframe.contents()).append(inputs);
	}

	return true;
    }

    $.fn.create = function() {
	window.location.replace($('#createItem').val());
    }

    $.fn.massDestroy = function() {
	if ($.fn.setSelectedItems() && window.confirm(messages.confirm_multiple_item_deletion)) {
	    $('#selectedItems input[name="_method"]').val('delete');
	    $('#selectedItems').attr('action', $('#destroyItems').val());
	    $('#selectedItems').submit();
	}
    }

    $.fn.checkin = function() {
	if ($.fn.setSelectedItems()) {
	    $('#selectedItems input[name="_method"]').val('put');
	    $('#selectedItems').attr('action', $('#checkinItems').val());
	    $('#selectedItems').submit();
	}
    }

    $.fn.publish = function() {
	if ($.fn.setSelectedItems()) {
	    $('#selectedItems input[name="_method"]').val('put');
	    $('#selectedItems').attr('action', $('#publishItems').val());
	    $('#selectedItems').submit();
	}
    }

    $.fn.unpublish = function() {
	if ($.fn.setSelectedItems()) {
	    $('#selectedItems input[name="_method"]').val('put');
	    $('#selectedItems').attr('action', $('#unpublishItems').val());
	    $('#selectedItems').submit();
	}
    }

    $.fn.batch = function() {
	if ($.fn.setSelectedItems()) {
	    $('#batch-window').css('display', 'block');
	}
    }

    if (jQuery.fn.select2) {
	$('.select2').select2();
    }

})(jQuery);
