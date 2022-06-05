(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).on('load', function() {
      let actions = ['save', 'saveClose', 'cancel', 'destroy'];

      actions.forEach(function (action) {
	  $('#'+action).click( function() { $.fn[action](); });
      });
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

  if (jQuery.fn.select2) {
      $('.select2').select2();
  }

  $.fn.runAjax = function() {
      let url = $('#itemForm').attr('action');
      let formData = new FormData($('#itemForm')[0]);

                 for(var pair of formData.entries()) {
                     //console.log(pair[0]+ ', '+ pair[1]);
                  }
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
            // Loop through the returned result.
            for (const [key, value] of Object.entries(result)) {
                if (key == 'redirect') {
                    window.location.href = result.redirect;
                }
                else if (key == 'refresh') {
                    $.fn.refreshFieldValues(result.refresh);
                }
                // messages
                else if (['success', 'warning', 'info'].includes(key)) {
                    $.fn.displayMessage(key, value);
                }
            }
        },
        error: function(result) {
            $.fn.displayMessage('danger', 'Please check the form below for errors');
            // Loop through the returned errors and set the messages accordingly.
            for (const [name, message] of Object.entries(result.responseJSON.errors)) {
                $('#'+name+'Error').text(message);

                // Show the tab (if any) the field is part of.
                if ($("#"+name).data('tab')) {
                    $('.nav-tabs a[href="#'+$("#"+name).data('tab')+'"]').tab('show');
                }
            }
        }
      });
  }

  $.fn.refreshFieldValues = function(values) {
      for (const [index, value] of Object.entries(values)) {
          if ($('#'+index).get(0).tagName == 'IMG') {
              $('#'+index).attr('src', value);
          }
          else {
              $('#'+index).val(value);
          }
      }
  }

  $.fn.displayMessage = function(type, message) {
      // Empty some possible error messages.
      $('div[id$="Error"]').each( function() {
          $(this).text('');
      });

      // Hide the possible displayed flash messages.
      $('.flash-message').each( function() {
          if (!$(this).hasClass('d-none')) {
              $(this).addClass('d-none');
          }
      });

      // Adapt to Bootstrap alert class names.
      type = (type == 'error') ? 'danger' : type;

      $('#ajax-message-alert').removeClass('d-none alert-success alert-danger alert-warning alert-info');
      $('#ajax-message-alert').addClass('alert-'+type);
      $('#ajax-message').text(message);
  }

  $.fn.testFunc = function() {
      alert('testFunc');
  }
})(jQuery);

