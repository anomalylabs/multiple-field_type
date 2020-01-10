<?php

use Anomaly\MultipleFieldType\MultipleFieldType;
use Anomaly\MultipleFieldType\MultipleFieldTypeServiceProvider;

class MultipleFieldTypeServiceProviderTest extends TestCase
{

    public function testProvides()
    {
        $provider = app(MultipleFieldTypeServiceProvider::class, ['app' => app()]);

        $provides = $provider->provides();

        $this->assertTrue(in_array(MultipleFieldType::class, $provides));
        $this->assertTrue(in_array('anomaly.field_type.multiple', $provides));
    }
}
