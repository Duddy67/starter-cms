(function($) {
    // Run a function when the page is fully loaded including graphics.
    $(window).on('load', function() {
        $('#test').click( function() { $.fn.emailTest(); });
    });

    $.fn.emailTest = function() {
	if (window.confirm($('#testEmailMessage').val())) {
            $('#sendTestEmail').submit();
        }
    }

})(jQuery);
