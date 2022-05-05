(function($) {
    // Run a function when the page is fully loaded including graphics.
    $(window).on('load', function() {
	// Disables both top and left panels of the editing form.
	$('#layout-navbar').prepend('<div class="disable-panel top-panel">&nbsp;</div>');
	$('#layout-sidebar').prepend('<div class="disable-panel">&nbsp;</div>');
    });

})(jQuery);
