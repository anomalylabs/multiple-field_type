{{ assets("styles.css", "anomaly.field_type.multiple::css/choices.css", ["as:jshjohnson/Choices.css"]) }}
{{ assets("scripts.js", "anomaly.field_type.multiple::js/choices.js", ["as:jshjohnson/Choices.js"]) }}
{{ assets("styles.css", "anomaly.field_type.multiple::css/tags.css") }}
{{ assets("scripts.js", "anomaly.field_type.multiple::js/tags.js") }}

<select multiple {!! html_attributes($fieldType->attributes(['value' => null])) !!}>

    @foreach ($fieldType->getOptions() as $key => $title)
        <option {{ in_array($key, $fieldType->ids()) ? 'selected' : '' }} value="{{ $key }}">{{ $title }}</option>        
    @endforeach

</select>

<small class="text-muted">
    {{ trans('anomaly.field_type.multiple::input.help') }}
</small>
