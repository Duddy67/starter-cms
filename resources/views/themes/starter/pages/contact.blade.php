<div class="position-relative">
@include('themes.starter.layouts.flash-message')
    <form action="{{ route('message') }}" method="post" id="form" role="form" class="php-email-form">
      <div class="row">
        <div class="col-md-6 form-group">
          <input type="text" name="name" class="form-control" id="name" placeholder="Your Name" required>
          <div class="text-danger" id="nameError"></div>
        </div>
        <div class="col-md-6 form-group mt-3 mt-md-0">
          <input type="email" class="form-control" name="email" id="email" placeholder="Your Email" required>
          <div class="text-danger" id="emailError"></div>
        </div>
      </div>
      <div class="form-group mt-3">
        <input type="text" class="form-control" name="object" id="object" placeholder="Object" required>
        <div class="text-danger" id="objectError"></div>
      </div>
      <div class="form-group mt-3">
        <textarea class="form-control" name="message" rows="5" placeholder="Message" required></textarea>
        <div class="text-danger" id="messageError"></div>
      </div>
      <div class="text-center"><button class="btn btn-success" id="submit" type="button">Send Message</button></div>
      <input type="hidden" name="_page" value="{{ $page }}">
    </form>
    <div class="ajax-progress d-none" id="ajax-progress">
        <img src="{{ asset('/images/progress-icon.gif') }}" class="progress-icon" style="top:20%;" />
    </div>
</div>

@push ('scripts')
    <script src="{{ asset('/js/ajax.js') }}"></script>
    <script>
        (function($) {
          // Run a function when the page is fully loaded including graphics.
          $(window).on('load', function() {
              $('#submit').click( function() { $.fn.runAjax(); });
          });
        })(jQuery);
    </script>
@endpush
