<div class="container">
    <h1>@lang ('labels.button.search')</h1>

    <form id="item-filters" method="get">
        <div class="row">
            <div class="col-lg-12">
                <input id="search" type="text" autocomplete="off" class="form-control" value="{{ request('keyword', '') }}" name="keyword" placeholder="Search by name">

                <button type="button" id="search-btn" class="btn btn-space btn-secondary mt-2">@lang ('labels.button.search')</button>
                <button type="button" id="clear-search-btn" class="btn btn-space btn-secondary mt-2">@lang ('labels.button.clear')</button>
            </div>
        </div>
    </form>

    <div class="row mt-5">
        <div class="col-lg-12">
            @if (count($posts))
                <table class="table table-striped table-bordered data-table">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center">#</th>
                            <th>@lang ('labels.generic.title')</th>
                            <th>@lang ('labels.post.content')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts as $post)
                        <tr>
                            <td class="text-center">{{ $post->id }}</td>
                            <td><a href="{{ url('/').$post->getUrl() }}">{{ $post->title }}</a></td>
                            <td>
                                @foreach ($post->search_results as $result)
                                    <span>...{{ $result }}...</span><br />
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="alert alert-warning alert-block">
                    <strong>{{ $message }}</strong>
                </div>
            @endif
        </div>
    </div>
</div>

<x-pagination :items=$posts />

@push ('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-3-typeahead/4.0.1/bootstrap3-typeahead.min.js"></script>
    <script type="text/javascript" src="{{ $public }}/js/post/category.js"></script>
    <script type="text/javascript">
        var path = "{{ route('autocomplete') }}";

        $('#search').typeahead({
            minLength: 3,
            source: function (query, process) {
                return $.get(path, {
                    query: query
                }, function (data) {
                    return process(data);
                });
            }
        });
    </script>
@endpush
