<h5>@php echo __('labels.title.subcategories'); @endphp</h5>

@if ($category->descendants->count())
    <ul class="list-group">
    @php 
	$traverse = function ($categories, $prefix = '-') use (&$traverse, $segments) {
	    foreach ($categories as $category) {
    @endphp
      <li class="list-group-item"><a href="{{ url('/').'/'.$segments['posts'].$category->getUrl() }}">{{ $prefix.' '.$category->name }}</a> ({{ $category->posts->count() }})</li>
    @php 
		$traverse($category->children, $prefix.'-');
	    }
	};

	$traverse($category->descendants->toTree());
    @endphp
</ul>
@else
    <p>@php echo __('messages.category.no_subcategories'); @endphp</p>
@endif
