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
      $('#itemForm').submit();
  }

  $.fn.saveClose = function() {
      $('input[name="_close"]').val(1);
      $('#itemForm').submit();
  }

  $.fn.cancel = function() {
      window.location.replace($('#cancelEdit').val());
  }

  $.fn.destroy = function() {
      if (window.confirm('Are you sure ?')) {
	  $('#deleteItem').submit();
      }

      /*alert($('#itemListUrl').val()+'/2');
      $.ajax({
	  type: 'DELETE',
	  url: $('#itemListUrl').val()+'/2',
	  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
	});*/
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

})(jQuery);

