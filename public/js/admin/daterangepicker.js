(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).on('load', function() {
      $('.date').daterangepicker({
          'singleDatePicker': true,
          'timePicker': true,
          'timePicker24Hour': true,
          //'autoUpdateInput': false,
      },
      function(start, end, label) {
        //console.log('New date range selected: ' + start.format('YYYY-MM-DD h:mm A') + ' to ' + end.format('YYYY-MM-DD h:mm A') + ' (predefined range: ' + label + ')');
      });

      $('.date').on('apply.daterangepicker', function(ev, picker) {
          // Convert the selected datetime in MySQL format. 
          let datetime = picker.startDate.format('YYYY-MM-DD HH:mm');

          // Set the hidden field to the selected datetime
          $('#_'+$(this).attr('id')).val(datetime);
      });

      $('.date').on('show.daterangepicker', function(ev, picker) {
          //$('.daterangepicker').hide();
      });

      $.fn.initStartDates();   
  });

  $.fn.initStartDates = function() {
      // The fields to initialized.
      let fields = $('.date');
      
      for (let i = 0; i < fields.length; i++) {
          // Check first whether the element exists.
          if ($('#'+fields[i].id).length) {
              // Check if a date format is available for this field or set it to the default format.
              let format = document.getElementById(fields[i].id).hasAttribute('data-format') ? $('#'+fields[i].id).data('format') : 'DD/MM/YYYY HH:mm';

              // Change the locale date format of that picker. 
              $('#'+fields[i].id).data('daterangepicker').locale.format = format;

              // By defaut set the start date to the current date.
              let startDate = moment().format(format);

              // A datetime has been previously set.
              if ($('#'+fields[i].id).data('date') != 0) {
                  // Concatenate date and time dataset parameters. 
                  let datetime = $('#'+fields[i].id).data('date')+' '+$('#'+fields[i].id).data('time');
                  startDate = moment(datetime).format(format);
                  // Set the hidden field to the datetime previously set.
                  $('#_'+fields[i].id).val(datetime);
              }
              else {
                  // Set the hidden field to the current datetime in MySQL format.
                  $('#_'+fields[i].id).val(moment().format('YYYY-MM-DD HH:mm'));
              }

              // Initialize the date field.
              $('#'+fields[i].id).data('daterangepicker').setStartDate(startDate);
          }
      }
  }

})(jQuery);
