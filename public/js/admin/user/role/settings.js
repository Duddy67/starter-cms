(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).on('load', function() {

      // A new role is being created.
      if ($('#permissions').length) {
	  $('#role_type').change( function() {
	      $.fn.setCheckboxes(true);
	  });

	  $.fn.setCheckboxes();
      }

      $('input[type="checkbox"]').change( function() {
          if ($(this).prop('checked')) {
	      $.fn.permissionChecked($(this).val());
	  }
	  else {
	      $.fn.permissionUnchecked($(this).val());
	  }
      });
  });

  $.fn.permissionChecked = function(name) {
      let item = name.match(/-([a-z0-9]+)$/)[1];
      
      if (item == 'category') {
	  item = name.match(/-([a-z0-9]+-[a-z0-9]+)$/)[1];
      }

      // A user able to update an item is also able to create one.
      if (/^update-/.test(name)) {
	  $('#create-'+item).prop('checked', true);
	  $('#update-own-'+item).prop('checked', false);
	  $('#delete-own-'+item).prop('checked', false);
	  return;
      }

      // A user able to delete an item is also able to update and create one.
      if (/^delete-/.test(name)) {
	  $('#create-'+item).prop('checked', true);
	  $('#update-'+item).prop('checked', true);
	  return;
      }
  }

  $.fn.permissionUnchecked = function(name) {
      let item = name.match(/-([a-z0-9]+)$/)[1];

      if (item == 'category') {
	  item = name.match(/-([a-z0-9]+-[a-z0-9]+)$/)[1];
      }

      // A user not able to create an item is also not able to update or delete one.
      if (/^create-/.test(name)) {
	  $('#update-'+item).prop('checked', false);
	  $('#delete-'+item).prop('checked', false);
      }
  }

  /*
   * Sets the role permissions according to the permission list.
   */
  $.fn.setCheckboxes = function(hasChanged) {
      let permissions = JSON.parse($('#permissions').val());
      let reloaded = $('#reloaded').val();
      let regex = new RegExp($('#role_type').val());

      $('input[type="checkbox"]').each( function() {
	  let name = $(this).prop('id');
	  let section = $(this).data('section');

	  for (let i = 0; i < permissions[section].length; i++) {
	      if (permissions[section][i].name == name) {
		 
		  if (permissions[section][i].optional !== undefined && regex.test(permissions[section][i].optional)) {
		      $('#'+name).prop('disabled', false);
		  }
		  else {
		      $('#'+name).prop('disabled', true);
		  }

		  // The page has been reloaded due to a validation error.
		  if (permissions[section][i].optional !== undefined && reloaded && hasChanged === undefined) {
		      // Let the old() function handle the checked and unchecked values of the optional checkboxes.
		      break;
		  }

		  if (regex.test(permissions[section][i].roles)) {
		      $('#'+name).prop('checked', true);
		  }
		  else {
		      $('#'+name).prop('checked', false);
		  }

		  break;
	      }
	  }
      });
  }

})(jQuery);

