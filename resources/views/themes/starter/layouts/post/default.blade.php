<h1 class="h2"><a href="{{ url($post->getUrl()) }}">{{ $post->title }}</a></h1>

@php $count = $limit = 0; @endphp
@foreach ($post->layoutItems as $key => $item)
    @if ($count == 0)
        <div class="row">
    @endif

    @if ($item->type == 'group_start')
        @php $data = explode('|', $item->value); @endphp
        @if (count($data) == 2)
           @php $limit = $data[1]; @endphp
        @endif
        <div class="{{ $data[0] }}">
    @endif

    @if ($item->type == 'group_end')
        </div>

        @if ($count == $limit)
            </div>
            @php $count = 0; @endphp
        @endif

        @continue
    @endif

    @if ($item->type == 'title')
        <h3>{{ $item->value }}</h3>
        @continue
    @endif

    @if ($item->type == 'text_block')
        <p>{!! $item->value !!}</p>
        @continue
    @endif

    @if ($item->type== 'image')
        @php $image = json_decode($item->value, true); @endphp
        <img class="rounded img-fluid" src="{{ url('/').$image['url'] }}" alt="{{ $image['alt_text'] }}">
        @continue
    @endif

    @php $count++; @endphp
@endforeach
