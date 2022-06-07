(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).on('load', function() {
      $('#deleteDocumentBtn').click( function() { $.fn.deleteImage(); });
  });

  $.fn.deleteImage = function() {
      let url = $('#deleteDocumentUrl').val();

      if (!url) {
          alert('There is no document to delete.');
          return;
      }

      $('#ajax-progress').removeClass('d-none');

      if (window.confirm('Are you sure ?')) {
          $.ajax({
            url: url,
            method: 'delete',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(result) {
                $('#ajax-progress').addClass('d-none');
                // Loop through the returned result.
                for (const [key, value] of Object.entries(result)) {
                    if (key == 'refresh') {
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
                $.fn.displayMessage('danger', 'The document could not be deleted.');
            }
          });
      }
  }


})(jQuery);
