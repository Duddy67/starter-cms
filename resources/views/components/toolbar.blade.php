<nav class="navbar">
    <div class="row" id="action-btn">
	@foreach ($items as $item)
	    <div class="w-auto">
		@if ($item->type == 'button')
                    <x-button :button="$item" />
		@endif
	    </div>
	@endforeach
    </div>
</nav>
