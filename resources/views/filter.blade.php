<select {!! html_attributes($fieldType->attributes()) !!}>

    <option value="" disabled {{ $fieldType->getValue() ? null : 'selected' }}>{{ $fieldType->getPlaceholder() }}</option>

    @foreach ($fieldType->getOptions() as $value)
        <option value="{{ $value }}" {{ in_array($value, $fieldType->ids()) ? 'selected' : null }}>{{ $value }}</option>
    @endforeach

</select>
