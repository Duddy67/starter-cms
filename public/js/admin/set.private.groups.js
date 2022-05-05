(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).on('load', function() {

      $.fn.setLockedGroups();

      $('#groups').on('select2:select', function(e) {
	  $.fn.setLockedGroups();
      });

      $('#groups').on('select2:unselect', function(e) {
	  $.fn.setLockedGroups();
      });
  });

  $.fn.setLockedGroups = function() {
      // Get the selected options.
      let groups = $('#groups').select2('data');

      for (let i = 0; i < groups.length; i++) {
	  // The options that are selected and disabled cannot be unselected (ie: removed).
	  if (groups[i].disabled) {
	      $('#groups').next('span').find('ul li').each(function() {
		  if ($(this).attr('title') !== undefined && $(this).attr('title') == groups[i].title) {
		      // Add the 'locked-tag' class to be able to style element in select.
		      $(this).addClass('locked-tag private-tag');
		  }
	      });
	  }
      }
  }

})(jQuery);
