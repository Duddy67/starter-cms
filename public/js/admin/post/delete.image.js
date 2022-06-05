(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).on('load', function() {
      $('#deleteImageBtn').click( function() { $.fn.deleteImage(); });
  });

  $.fn.deleteImage = function() {
      let url = $('#deleteImageUrl').val();

      if (!url) {
          alert('There is no image to delete.');
          return;
      }

      if (window.confirm('Are you sure ?')) {
          $.ajax({
            url: url,
            method: 'delete',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(result) {
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
                $.fn.displayMessage('danger', 'The image could not be deleted.');
            }
          });
      }
  }


})(jQuery);
