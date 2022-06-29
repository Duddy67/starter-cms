@php 
    $dropdown = (count($item->children) > 0) ? 'dropdown' : ''; 
    $dropdownLink = (count($item->children) > 0) ? 'dropdown-toggle' : ''; 
@endphp

<li class="nav-item {{ $item->class }} {{ $dropdown }}">
    <a class="nav-link {{ $dropdownLink }}" href="{{ url($item->url).$item->anchor }}"
    @if ($dropdown)
        id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
    @endif
    >{{ $item->title }}</a>

    @if (count($item->children) > 0)
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
	    @foreach ($item->children as $item)
	        <a class="dropdown-item" href="{{ url($item->url) }}">{{ $item->title }}</a>
	    @endforeach
	</div>
    @endif
</li>
