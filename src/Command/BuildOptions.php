<?php

namespace Anomaly\MultipleFieldType\Command;

use Illuminate\Support\Str;
use Illuminate\Container\Container;
use Anomaly\MultipleFieldType\Handler\Related;
use Anomaly\MultipleFieldType\MultipleFieldType;

/**
 * Class BuildOptions
 *
 * @link          http://pyrocms.com/
 * @author        PyroCMS, Inc. <support@pyrocms.com>
 * @author        Ryan Thompson <ryan@pyrocms.com>
 */
class BuildOptions
{

    /**
     * The field type instance.
     *
     * @var MultipleFieldType
     */
    protected $fieldType;

    /**
     * Create a new BuildOptions instance.
     *
     * @param MultipleFieldType $fieldType
     */
    public function __construct(MultipleFieldType $fieldType)
    {
        $this->fieldType = $fieldType;
    }

    /**
     * Handle the command.
     *
     * @param Container $container
     */
    public function handle(Container $container)
    {
        if ($options = $this->fieldType->config('options')) {

            $this->fieldType->setOptions($options);

            return;
        }

        $model   = $this->fieldType->getRelatedModel();
        //$handler = $this->fieldType->config('handler', $model->call('get_multiple_field_type_options_handler'));
        $handler = Related::class;

        if (!class_exists($handler) && !Str::contains($handler, '@')) {
            $handler = array_get($this->fieldType->getHandlers(), $handler);
        }

        if (is_string($handler) && !Str::contains($handler, '@')) {
            $handler .= '@handle';
        }

        $container->call($handler, ['fieldType' => $this->fieldType]);
    }
}
