(function($) {
    // Run a function when the page is fully loaded including graphics.
    $(window).on('load', function() {

	$('#cancel').click (function () {
	    $('#batch-window', parent.document).css('display', 'none');
	});

	$('#massUpdate').click (function () {
	    $('#batchForm').submit();
	});
    });

    if (jQuery.fn.select2) {
	$('.select2').select2();
    }

})(jQuery);
