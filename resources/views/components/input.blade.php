
@if (isset($field->label) && $field->type != 'checkbox')
    <label for="{{ $field->id }}" class="fw-bold mt-3 mb-2">@lang ($field->label)</label>
@endif

@php $disabled = (isset($field->extra) && in_array('disabled', $field->extra)) ? 'disabled' : '';
     $readonly = (isset($field->extra) && in_array('readonly', $field->extra)) ? 'readonly' : '';
     $class = (isset($field->class)) ? $field->class : '';
     $name = (isset($field->name)) ? $field->name : null;
     $name = ($name && isset($field->group)) ? $field->group.'['.$name.']' : $name;
     $multiple = (isset($field->extra) && in_array('multiple', $field->extra)) ? 'multiple' : '';
     $divider = (isset($field->extra) && in_array('divider', $field->extra)) ? true : false;
     $titleAsId = (isset($field->extra) && in_array('title_as_id', $field->extra)) ? true : false;
     $multi = ($multiple) ? '[]' : '';
@endphp

@if ($field->type == 'text' || $field->type == 'password' || $field->type == 'date' || $field->type == 'file')
    <input  id="{{ $field->id }}"

    @if ($field->type == 'date')
	type="text" class="form-control date {{ $class }}"
    @else 
	type="{{ $field->type }}" class="form-control {{ $class }}" 
    @endif

    @if ($name)
	name="{{ $name }}"
    @endif

    @if (isset($field->placeholder))
	placeholder="@lang ($field->placeholder)"
    @endif

    @if (isset($field->autocomplete))
	autocomplete="{{ $field->autocomplete }}"
    @endif

    @if ($value && $field->type != 'password')
	value="{{ $value }}"
    @endif

    @if (isset($field->dataset))
        @foreach ($field->dataset as $key => $val) 
            data-{{ $key }}="{{ $val }}" 
        @endforeach
    @endif

     {{ $disabled }} {{ $readonly }}>

@elseif ($field->type == 'select')

    <select id="{{ $field->id }}" class="form-control cselect {{ $class }}" {{ $multiple }} {{ $disabled }} name="{{ $name.$multi }}"
    @if (isset($field->onchange))
	onchange="{{ $field->onchange }}"
    @endif

    @if (isset($field->dataset))
        @foreach ($field->dataset as $key => $val) 
            data-{{ $key }}="{{ $val }}" 
        @endforeach
    @endif
    >
	@if (isset($field->blank))
	    <option value="">@lang ($field->blank)</option>
	@endif

        @foreach ($field->options as $option)
	    @if ($multiple)
		@php $selected = ($value !== null && in_array($option['value'], $value)) ? 'selected=selected' : '' @endphp
	    @else
		@php $selected = ($value !== null && $option['value'] == $value) ? 'selected=selected' : '' @endphp
	    @endif

	    @php $disabled = (isset($option['extra']) && in_array('disabled', $option['extra'])) ? 'disabled=disabled' : '' @endphp

	    <option
	    @if ($titleAsId)
		title="{{ $field->id }}-{{ $option['value'] }}"
	    @endif

	    value="{{ $option['value'] }}" {{ $disabled }} {{ $selected }}>{{ $option['text'] }}</option>

        @endforeach
    </select> 
@elseif ($field->type == 'checkbox')
    @if (!isset($field->position) || $field->position == 'left')
	<label class="form-check-label" for="{{ $field->id }}">{{ $field->label }}</label>
    @endif

    <input type="checkbox" id="{{ $field->id }}" class="form-check-input" 

    @if ($name)
	name="{{ $name }}"
    @endif

    @if (isset($field->disabled) && $field->disabled)
	disabled="disabled"
    @endif

    @if ($value)
	value="{{ $value }}"
    @endif

    @if ($field->checked)
	checked
    @endif

    @if (isset($field->dataset))
        @foreach ($field->dataset as $key => $val) 
            data-{{ $key }}="{{ $val }}" 
        @endforeach
    @endif
    >

    @if (isset($field->position) && $field->position == 'right')
	<label class="form-check-label" for="{{ $field->id }}">{{ $field->label }}</label>
    @endif
@elseif ($field->type == 'textarea')
    <textarea id="{{ $field->id }}" class="form-control {{ $class }}"  

    @if ($name)
	name="{{ $name }}"
    @endif

    @if (isset($field->rows))
        rows="{{ $field->rows }}"
    @endif

    @if (isset($field->cols))
        cols="{{ $field->cols}}"
    @endif

    @if (isset($field->dataset))
        @foreach ($field->dataset as $key => $val) 
            data-{{ $key }}="{{ $val }}" 
        @endforeach
    @endif

     {{ $disabled }}>{{ $value }}</textarea>
    
@elseif ($field->type == 'hidden')
    <input type="hidden" name="{{ $name }}" id="{{ $field->id }}" value="{{ $value }}"> 
@endif

@if ($field->type == 'date' && !$disabled)
    <input type="hidden" id="_{{ $field->name }}" name="_{{ $field->name }}" value="" />
@endif

@if ($name && isset($field->id) && !$disabled && $field->type != 'hidden')
    @php $id = (isset($field->group)) ? $field->group.'.'.$field->id : $field->id; @endphp
    <div class="text-danger" id="{{ $id }}Error"></div>
@endif

@if ($divider)
    <hr class="mt-4 mb-3"/>
@endif
