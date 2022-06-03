(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).on('load', function() {
      let actions = ['save', 'saveClose', 'cancel', 'destroy'];

      actions.forEach(function (action) {
	  $('#'+action).click( function() { $.fn[action](); });
      });

      if ($('#activeTab').length) {
	  $.fn.setActiveTab($('#activeTab').val());
      }

      $('.nav-tabs a').click( function() { $.fn.setLastActiveTab($(this).attr('href')); });
  });

  $.fn.save = function() {
      $.fn.runAjax();
  }

  $.fn.saveClose = function() {
      $('input[name="_close"]').val(1);
      $.fn.runAjax();
  }

  $.fn.cancel = function() {
      window.location.replace($('#cancelEdit').val());
  }

  $.fn.destroy = function() {
      if (window.confirm('Are you sure ?')) {
	  $('#deleteItem').submit();
      }
  }

  $.fn.setActiveTab = function(tab) {
    let link = $('a[href="#'+tab+'"]');
    link.addClass('active');
  }

  $.fn.setLastActiveTab = function(tab) {
    // Remove the # character at the start of the string.
    tab = tab.substring(1);
    $('#activeTab').val(tab);
  }

  if (jQuery.fn.select2) {
      $('.select2').select2();
  }

  $.fn.runAjax = function() {
      let url = $('#itemForm').attr('action');
      let formData = new FormData($('#itemForm')[0]);

      $.ajax({
        url: url,
        // Always use the post method when sending data as FormData doesn't work with the put method.
        // If a different method has to be used, it is set through the "_method" input.
        method: 'post',
        data: formData, 
        contentType: false,
        processData: false,
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        success: function(result) {
            // The item has been successfuly saved.
            if ($('input[name="_close"]').val() == 1) {
                // Redirect to the list view.
                window.location.href = $('#listUrl').val();
            }
            else {
                // Loop through the returned messages (ie: success, warning or info).
                for (const [type, message] of Object.entries(result)) {
                    $.fn.displayMessage(type, message);
                }
            }
        },
        error: function(result) {
            $.fn.displayMessage('danger', 'Please check the form below for errors');
            // Loop through the returned errors and set the messages accordingly.
            for (const [name, message] of Object.entries(result.responseJSON.errors)) {
                $('#'+name+'Error').text(message);
            }
        }
      });
  }

  $.fn.displayMessage = function(type, message) {
      // Empty some possible error messages.
      $('div[id$="Error"]').each( function() {
          $(this).text('');
      });

      // Adapt to Bootstrab alert class names.
      type = (type == 'error') ? 'danger' : type;

      $('#ajax-message-alert').removeClass('d-none alert-success alert-danger alert-warning alert-info');
      $('#ajax-message-alert').addClass('alert-'+type);
      $('#ajax-message').text(message);
  }

})(jQuery);

