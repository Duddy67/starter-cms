@if (preg_match('#^image\/#', $documents[$key]->content_type))
    <a href="#" onmouseover="document.getElementById('place-holder-{{ $documents[$key]->id }}').src='{{ $documents[$key]->thumbnail }}';"
		onmouseout="document.getElementById('place-holder-{{ $documents[$key]->id }}').src='{{ url('/') }}/images/transparent.png';">
    <i class="nav-icon fas fa-eye"></i></a>
    <img src="{{ url('/') }}/images/transparent.png" id="place-holder-{{ $documents[$key]->id }}" style="zindex: 100; position: absolute;" />
@else
    <i class="nav-icon fas fa-eye-slash"></i>
@endif
