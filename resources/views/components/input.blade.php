
@if (isset($field->label) && $field->type != 'checkbox')
    <label for="{{ $field->id }}">@lang ($field->label)</label>
@endif

@php $disabled = (isset($field->extra) && in_array('disabled', $field->extra)) ? 'disabled' : '';
     $class = (isset($field->class)) ? $field->class : '';
     $name = (isset($field->name)) ? $field->name : null;
     $name = ($name && isset($field->group)) ? $field->group.'['.$name.']' : $name;
     $multiple = (isset($field->extra) && in_array('multiple', $field->extra)) ? 'multiple' : '';
     $titleAsId = (isset($field->extra) && in_array('title_as_id', $field->extra)) ? true : false;
     $multi = ($multiple) ? '[]' : '';

      $dataset = ''; 
      if (isset($field->dataset)) {
          foreach($field->dataset as $key => $val) {
              $dataset .= 'data-'.$key.'='.$val.' ';
          }
      }
@endphp

@if ($field->type == 'text' || $field->type == 'password' || $field->type == 'date' || $field->type == 'file')
    <input  id="{{ $field->id }}" {{ $disabled }} 

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

    @if ($disabled)
        readonly
    @endif

    @if (isset($field->autocomplete))
        autocomplete="{{ $field->autocomplete }}"
    @endif

    @if ($value && $field->type != 'password')
        value="{{ $value }}"
    @endif

    @if ($dataset)
        {{ $dataset }}
    @endif

    >
@elseif ($field->type == 'select')

    <select id="{{ $field->id }}" class="form-control select2 {{ $class }}" {{ $multiple }} {{ $disabled }} name="{{ $name.$multi }}"
    @if (isset($field->onchange))
        onchange="{{ $field->onchange }}"
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

            @php $disabled = (isset($option['extra']) && in_array('disabled', $option['extra'])) ? 'disabled=disabled locked=locked' : '' @endphp

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

    <input type="checkbox" id="{{ $field->id }}" class="form-check-input" {{ $dataset }}

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
    >{{ $value }}</textarea>
    
@elseif ($field->type == 'hidden')
    <input type="hidden" name="{{ $name }}" id="{{ $field->id }}" value="{{ $value }}"> 
@endif

@if ($field->type == 'date' && !$disabled)
    <input type="hidden" id="_{{ $field->name }}" name="_{{ $field->name }}" value="" />
@endif

@if ($name)
    @error($name)
        <div class="text-danger">{{ $message }}</div>
    @enderror
@endif
