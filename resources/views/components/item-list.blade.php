<table id="item-list" class="table table-hover table-striped">
    <thead class="table-success">
        @if ($checkable)
            <th scope="col">
                <input type="checkbox" id="toggle-select" class="form-check-input">
            </th>
        @endif

        @foreach ($columns as $key => $column)
            <th scope="col">
                @lang ($column->label)
            </th>
        @endforeach
    </thead>
    <tbody>
        @foreach ($rows as $i => $row)
             @php 
                 $query = $url['query'];
                 $query[$url['item_name']] = $row->item_id;
            @endphp
            <tr>
                @if ($checkable)
                    <td>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input item-checkbox" data-item-id={{ $row->item_id }} data-index="{{ $i }}">

                            @if (isset($row->checked_out))
                                <div class="checked-out">
                                    <p class="mb-0"><small>{{ $row->checked_out }}&nbsp;&nbsp;<i class="fa fa-lock"></i><br>{{ $row->checked_out_time }}</small></p>
                                </div>
                            @endif
                        </div>
                    </td>
                @endif

                @foreach ($columns as $column)
                    @if ($column->name == 'ordering')
                        <td>
                            @if (isset($row->ordering['up']))
                                <a href="{{ $row->ordering['up'] }}"><i class="fa fa-angle-double-up me-2"></i></a>
                            @else
                                <i class="me-3">&nbsp;</i>
                            @endif

                            @if (isset($row->ordering['down']))
                                <a href="{{ $row->ordering['down'] }}"><i class="fa fa-angle-double-down"></i></a>
                            @endif
                        </td>
                    @else
                        @php $indent = (in_array($column->name, ['name', 'title']) && preg_match('#^(-{1,}) #', $row->{$column->name}, $matches)) ? strlen($matches[1]) : 0; @endphp
                        <td>
                            @php $linkable = (isset($column->extra) && in_array('linkable', $column->extra)) ? true : false; @endphp
                            @php echo ($linkable) ? '<a href="'.route($url['route'].'.edit', $query).'">' : ''; @endphp
                            <span class="indent-{{ $indent }}"></span>
                            @if (isset($column->extra) && in_array('raw', $column->extra))
                                {!! $row->{$column->name} !!}
                            @else
                                {{ $row->{$column->name} }}
                            @endif
                            @php echo ($linkable) ? '</a>' : ''; @endphp
                        </td>
                    @endif
                @endforeach
            </tr>
        @endforeach
    </tbody>

</table>
