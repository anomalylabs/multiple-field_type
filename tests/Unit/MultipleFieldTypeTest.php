<?php

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
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

        $this->assertTrue(Arr::get($attributes, 'name') === $fieldType->getInputName() . '[]');
    }
}
