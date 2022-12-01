<select name="locales" id="locales" class="form-select">
    @foreach (config('app.locales') as $locale)
        @php $selected = ($locale == app()->getLocale()) ? 'selected="selected"' : ''; @endphp
        <option value="{{ url('/').'/'.$locale }}" {{ $selected }}>{{ __('locales.options.'.$locale, [], 'en') }}</option>
    @endforeach
</select>
