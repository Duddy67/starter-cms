<select name="locales" id="locales" class="form-select">
    @foreach (config('app.locales') as $locale)
        @php $selected = ($locale == app()->getLocale()) ? 'selected="selected"' : ''; @endphp
        <option value="{{ url('/').'/'.$locale }}" {{ $selected }}>{{ __('labels.locale.'.$locale) }}</option>
    @endforeach
</select>
