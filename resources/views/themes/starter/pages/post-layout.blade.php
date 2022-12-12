<h1 class="h2"><a href="{{ url($locale.$post->getUrl()) }}">{{ $post->title }}</a></h1>

@php $layoutItems = $post->getLayoutItems($locale); $count = $limit = 0; @endphp

@foreach ($layoutItems as $key => $item)

    @if ($count == 0)
        <div class="row">
    @endif

    @if ($item->type == 'group_start')
        @if (!empty($item->data['groups_in_row']))
           @php $limit = $item->data['groups_in_row']; @endphp
        @endif
        <div class="{{ $item->data['class'] }}">
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
        <h3>{{ $item->text }}</h3>
        @continue
    @endif

    @if ($item->type == 'text_block')
        {!! $item->text !!}
        @continue
    @endif

    @if ($item->type== 'image')
        <img class="rounded img-fluid" src="{{ url('/').$item->data['url'] }}" alt="{{ $item->text }}">
        @continue
    @endif

    @php $count++; @endphp
@endforeach
