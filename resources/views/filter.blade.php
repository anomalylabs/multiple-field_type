<select {!! html_attributes($fieldType->attributes([
    'name' => $fieldType->getInputName(),
    'value' => null,
])) !!}>

    <option value="" disabled {{ $fieldType->getValue() ? null : 'selected' }}>{{ $fieldType->getPlaceholder() }}</option>

    @foreach ($fieldType->getOptions() as $value => $option)
        <option value="{{ $value }}" {{ $value == $fieldType->getValue() ? 'selected' : null }}>{{ $option }}</option>
    @endforeach

</select>
