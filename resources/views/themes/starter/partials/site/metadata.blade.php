@foreach ($metaData as $key => $value)
    @if (!empty($value) && str_starts_with($key, 'meta_name_'))
        <meta name="{{ substr($key, 10) }}" content="{{ $value }}">   
    @elseif (!empty($value) && str_starts_with($key, 'meta_og_'))
        @php $key = substr($key, 5) @endphp
        <meta property="{{ str_replace('_', ':', $key) }}" content="{{ $value }}">   
    @elseif (!empty($value) && $key == 'canonical_link')
        <link rel="canonical" href="{{ $value }}">
    @endif
@endforeach 
