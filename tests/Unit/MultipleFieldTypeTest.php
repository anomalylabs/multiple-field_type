<?php

use Anomaly\MultipleFieldType\MultipleFieldType;
use Illuminate\Contracts\View\View;

class MultipleFieldTypeTest extends TestCase
{

    public function testClass()
    {
        $fieldType = app(MultipleFieldType::class)
            ->setField('multiple');

        $this->assertTrue(str_contains($fieldType->class('foo bar'), 'input'));
    }

    public function testAttributes()
    {
        $fieldType = app(MultipleFieldType::class)
            ->setField('multiple');

        $attributes = $fieldType->attributes();

        $this->assertTrue(array_get($attributes, 'name') === $fieldType->getInputName() . '[]');
    }
}
