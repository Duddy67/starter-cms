(function($) {
    // Run a function when the page is fully loaded including graphics.
    $(window).on('load', function() {
	let actions = ['update', 'rebuild'];

	actions.forEach(function (action) {
	    $('#'+action).click( function() { $.fn[action](); });
	});

	$('.clickable-row').click(function(event) {
	    if(!$(event.target).hasClass('form-check-input')) {
		$(this).addClass('active').siblings().removeClass('active');
		window.location = $(this).data('href');
	    }
	});
    });

    $.fn.update = function() {
	$('#updateItems').submit();
    }

    $.fn.rebuild = function() {
	$('input[name="_method"]').val('put');
	$('#updateItems').submit();
    }
})(jQuery);
