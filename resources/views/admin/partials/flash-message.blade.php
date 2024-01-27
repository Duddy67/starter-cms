@if ($message = Session::get('success'))
    <div class="alert alert-success alert-block flash-message">
	<strong>{{ $message }}</strong>
	<button type="button" class="btn-close float-end" data-dismiss="alert"></button>	
    </div>
@endif 

@if ($message = Session::get('error'))
    <div class="alert alert-danger alert-block flash-message">
	<strong>{{ $message }}</strong>
	<button type="button" class="btn-close float-end" data-dismiss="alert"></button>	
    </div>
@endif

@if ($message = Session::get('warning'))
    <div class="alert alert-warning alert-block flash-message">
	<strong>{{ $message }}</strong>
	<button type="button" class="btn-close float-end" data-dismiss="alert"></button>	
    </div>
@endif

@if ($message = Session::get('info'))
    <div class="alert alert-info alert-block flash-message">
	<strong>{{ $message }}</strong>
	<button type="button" class="btn-close float-end" data-dismiss="alert"></button>	
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger flash-message">
	Please check the form below for errors
	<button type="button" class="btn-close float-end" data-dismiss="alert"></button>	
    </div>
@endif

<div class="alert alert-success alert-block d-none" id="ajax-message-alert">
    <strong id="ajax-message"></strong>
    <button type="button" class="btn-close float-end" data-dismiss="alert"></button>	
</div>
