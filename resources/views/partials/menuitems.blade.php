@php 
    $dropdown = (count($menuItem->children) > 0) ? 'dropdown' : ''; 
    $dropdownLink = (count($menuItem->children) > 0) ? 'dropdown-toggle' : ''; 
@endphp

<li class="nav-item {{ $dropdown }}">
    <a class="nav-link {{ $dropdownLink }}" href="{{ url($menuItem->url) }}"
    @if ($dropdown)
        id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
    @endif
    >{{ $menuItem->title }}</a>

    @if (count($menuItem->children) > 0)
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
	    @foreach ($menuItem->children as $menuItem)
	        <a class="dropdown-item" href="{{ url($menuItem->url) }}">{{ $menuItem->title }}</a>
	    @endforeach
	</div>
    @endif
</li>
