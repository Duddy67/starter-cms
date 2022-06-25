(function($) {

  $.fn.runAjax = function() {
      let url = $('#form').attr('action');
      let formData = new FormData($('#form')[0]);

      $('#ajax-progress').removeClass('d-none');

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
            $('#ajax-progress').addClass('d-none');
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
            $('#ajax-progress').addClass('d-none');
            console.log(result);
            $.fn.displayMessage('danger', 'Please check the form below for errors.');
            // Loop through the returned errors and set the messages accordingly.
            for (const [name, message] of Object.entries(result.responseJSON.errors)) {
                $('#'+name+'Error').text(message);
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

      $(window).scrollTop(0);
  }

})(jQuery);

