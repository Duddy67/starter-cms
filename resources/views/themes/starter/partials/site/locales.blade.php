<div class="row">
    <div class="col-3">
        <img src="{{ asset('/images/countries/'.$locale.'.png') }}" class="locale-flag" width="40" height="26" alt="{{ $locale }}">
    </div>
    <div class="col-9">
        <select name="locales" id="locales" class="form-select">
            @foreach (config('app.locales') as $locale)
                @php $selected = ($locale == app()->getLocale()) ? 'selected="selected"' : ''; @endphp
                <option value="{{ url('/').'/'.$locale }}" {{ $selected }}>{{ __('labels.locales.'.$locale) }}</option>
            @endforeach
        </select>
    </div>
</div>
