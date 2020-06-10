<?php

use Illuminate\Support\Str;
use Illuminate\Contracts\View\View;
use Anomaly\MultipleFieldType\MultipleFieldType;

class MultipleFieldTypeTest extends TestCase
{

    public function testClass()
    {
        $fieldType = app(MultipleFieldType::class)
            ->setField('multiple');

        $this->assertTrue(Str::contains($fieldType->class('foo bar'), 'input'));
    }

    public function testAttributes()
    {
        $fieldType = app(MultipleFieldType::class)
            ->setField('multiple');

        $attributes = $fieldType->attributes();

        $this->assertTrue(array_get($attributes, 'name') === $fieldType->getInputName() . '[]');
    }
}
