(function($) {
    // Run a function when the page is fully loaded including graphics.
    $(window).on('load', function() {
	// Disables both top and left panels of the editing form.
	$('.navbar-disabled').prepend('<div class="disable-panel top-panel">&nbsp;</div>');
	$('.sidebar-disabled').prepend('<div class="disable-panel">&nbsp;</div>');
    });

})(jQuery);
