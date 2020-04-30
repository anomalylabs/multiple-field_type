<select {!! html_attributes($fieldType->attributes([
    'name' => $fieldType->getInputName(),
    'value' => null,
])) !!}>

    <option value="" disabled {{ $fieldType->value ? null : 'selected' }}>{{ $fieldType->placeholder }}</option>

    @foreach ($fieldType->getOptions() as $value => $option)
        <option value="{{ $value }}" {{ $value == $fieldType->value ? 'selected' : null }}>{{ $option }}</option>
    @endforeach

</select>
