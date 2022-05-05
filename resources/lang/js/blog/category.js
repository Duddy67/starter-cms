(function($) {
    // Run a function when the page is fully loaded including graphics.
    $(window).on('load', function() {

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

})(jQuery);
