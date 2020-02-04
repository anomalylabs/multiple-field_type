<?php

namespace Anomaly\MultipleFieldType;

use Anomaly\MultipleFieldType\Handler\Related;
use Anomaly\MultipleFieldType\Table\LookupTableBuilder;
use Anomaly\MultipleFieldType\Table\SelectedTableBuilder;
use Anomaly\MultipleFieldType\Table\ValueTableBuilder;
use Anomaly\Streams\Platform\Addon\AddonServiceProvider;
use Anomaly\Streams\Platform\Entry\Contract\EntryInterface;
use Anomaly\Streams\Platform\Entry\EntryModel;
use Illuminate\Contracts\Support\DeferrableProvider;

/**
 * Class MultipleFieldTypeServiceProvider
 *
 * @link   http://pyrocms.com/
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class MultipleFieldTypeServiceProvider extends AddonServiceProvider implements DeferrableProvider
{

    /**
     * The addon routes.
     *
     * @var array
     */
    public $routes = [
        'streams/multiple-field_type/json/{key}'     => 'Anomaly\MultipleFieldType\Http\Controller\LookupController@json',
        'streams/multiple-field_type/index/{key}'    => 'Anomaly\MultipleFieldType\Http\Controller\LookupController@index',
        'streams/multiple-field_type/selected/{key}' => 'Anomaly\MultipleFieldType\Http\Controller\LookupController@selected',
    ];

    /**
     * Return the provided services.
     */
    public function provides()
    {
        return [MultipleFieldType::class, 'anomaly.field_type.multiple'];
    }

    /**
     * Register the addon.
     *
     * @param EntryModel $model
     */
    public function register()
    {
        parent::register();

        $model = app(EntryModel::class);

        $model->bind(
            'new_multiple_field_type_lookup_table_builder',
            function () {

                /* @var EntryInterface $this */
                $builder = $this->getBoundModelNamespace() . '\\Support\\MultipleFieldType\\LookupTableBuilder';

                if (class_exists($builder)) {
                    return app($builder);
                }

                return app(LookupTableBuilder::class);
            }
        );

        $model->bind(
            'new_multiple_field_type_value_table_builder',
            function () {

                /* @var EntryInterface $this */
                $builder = $this->getBoundModelNamespace() . '\\Support\\MultipleFieldType\\ValueTableBuilder';

                if (class_exists($builder)) {
                    return app($builder);
                }

                return app(ValueTableBuilder::class);
            }
        );

        $model->bind(
            'new_multiple_field_type_selected_table_builder',
            function () {

                /* @var EntryInterface $this */
                $builder = get_class($this) . '\\Support\\MultipleFieldType\\SelectedTableBuilder';

                if (class_exists($builder)) {
                    return app($builder);
                }

                return app(SelectedTableBuilder::class);
            }
        );

        $model->bind(
            'get_multiple_field_type_options_handler',
            function () {

                /* @var EntryInterface $this */
                $handler = get_class($this) . '\\Support\\MultipleFieldType\\OptionsHandler';

                if (class_exists($handler)) {
                    return $handler;
                }

                return Related::class;
            }
        );
    }
}
