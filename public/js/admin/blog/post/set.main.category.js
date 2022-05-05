(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).on('load', function() {

      if ($('#main-cat-id').val()) {
	  $.fn.refreshMainCategoryBox();
      }

      $.fn.setLockedCategories();

      // The main category can't be selected if the dropdown list is disabled.
      if ($('#categories').attr('disabled') === undefined) {

	  $('#categories').on('select2:unselect', function(e) {
	      let nbSelectedOptions = $(this).select2('data').length;

	      if (nbSelectedOptions == 0) {
		  $('#main-cat-id').val('');
		  // Prevents the dropdown from opening.
		  e.params.originalEvent.stopPropagation();

		  return;
	      }

	      let data = e.params.data;
		
	      // The unselected option is the current main category. 
	      if (data.id == $('#main-cat-id').val()) {
		  // Set the first selected category as the main category.
		  $.fn.selectAsMainCategory($(this).select2('data')[0].id);
	      }

	      $.fn.setLockedCategories();
	      $.fn.refreshMainCategoryBox();
	      // Prevents the dropdown from opening.
	      e.params.originalEvent.stopPropagation();
	  });

	  $('#categories').on('select2:select', function(e) {

	      let nbSelectedOptions = $(this).select2('data').length;
	      let data = e.params.data;

	      if (nbSelectedOptions == 1) {
		  $.fn.selectAsMainCategory(data.id);

		  return;
	      }

	      $.fn.setLockedCategories();
	      $.fn.refreshMainCategoryBox();
	  });

	  $('#categories').next('span').find('ul').on('click', '.select2-selection__choice', function (e) {
	     // Get the correspondig category id from the title attribut (ie: categories-[0-9]+).
	     let catId = $(this).attr('title').substr(11);

	     // Check target is actually a li tag, not an embedded span tag (used for unselect boxes).
	     if (e.target == this) {
		 $.fn.selectAsMainCategory(catId);
		 // Prevents the dropdown from opening.
		 e.stopPropagation();
	     }
	  });
      }
  });

  $.fn.selectAsMainCategory = function(catId) {
      let oldCatId = $('#main-cat-id').val();

      if (oldCatId == catId) {
	  return;
      }

      // Set the new main category for this post.
      $('#main-cat-id').val(catId);

      // Loop through the selected boxes.
      $('#categories').next('span').find('ul li').each(function() {
	  // Unselect the previous main category.
	  if ($(this).attr('title') !== undefined && $(this).attr('title').substr(11) == oldCatId) {
	      $(this).css('background-color', '#e4e4e4');
	  }

	  // Select the new main category.
	  if ($(this).attr('title') !== undefined && $(this).attr('title').substr(11) == catId) {
	      $(this).css('background-color', '#aedef4');
	  }
      });
  }

  $.fn.refreshMainCategoryBox = function() {
      let catId = $('#main-cat-id').val();

      // Loop through the selected boxes.
      $('#categories').next('span').find('ul li').each(function() {
	    if ($(this).attr('title') !== undefined && $(this).attr('title').substr(11) == catId) {
		$(this).css('background-color', '#aedef4');
	    }
      });
  }

  $.fn.setLockedCategories = function() {
      // Get the selected options.
      let categories = $('#categories').select2('data');

      for (let i = 0; i < categories.length; i++) {
	  // The options that are selected and disabled cannot be unselected (ie: removed).
	  if (categories[i].disabled) {
	      $('#categories').next('span').find('ul li').each(function() {
		  if ($(this).attr('title') !== undefined && $(this).attr('title') == categories[i].title) {
		      // Add the 'locked-tag' class to be able to style element in select.
		      $(this).addClass('locked-tag');
		  }
	      });
	  }
      }
  }

})(jQuery);
