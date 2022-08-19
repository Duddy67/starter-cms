@if ($message = Session::get('success'))
    <div class="alert alert-success alert-block flash-message">
	<button type="button" class="btn-close" data-bs-dismiss="alert"></button>	
	<strong>{{ $message }}</strong>
    </div>
@endif 

@if ($message = Session::get('error'))
    <div class="alert alert-danger alert-block flash-message">
	<button type="button" class="btn-close" data-bs-dismiss="alert"></button>	
	<strong>{{ $message }}</strong>
    </div>
@endif

@if ($message = Session::get('warning'))
    <div class="alert alert-warning alert-block flash-message">
	<button type="button" class="btn-close" data-bs-dismiss="alert"></button>	
	<strong>{{ $message }}</strong>
    </div>
@endif

@if ($message = Session::get('info'))
    <div class="alert alert-info alert-block flash-message">
	<button type="button" class="btn-close" data-bs-dismiss="alert"></button>	
	<strong>{{ $message }}</strong>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger flash-message">
	<button type="button" class="btn-close" data-bs-dismiss="alert"></button>	
	Please check the form below for errors
    </div>
@endif

<div class="alert alert-success alert-block d-none" id="ajax-message-alert">
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>	
    <strong id="ajax-message"></strong>
</div>
