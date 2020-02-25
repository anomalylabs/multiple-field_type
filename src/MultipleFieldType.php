<?php

namespace Anomaly\MultipleFieldType;

use Anomaly\MultipleFieldType\Command\BuildOptions;
use Anomaly\Streams\Platform\Addon\FieldType\FieldType;
use Anomaly\Streams\Platform\Entry\EntryCollection;
use Anomaly\Streams\Platform\Model\EloquentModel;
use Anomaly\Streams\Platform\Stream\Command\GetStream;
use Anomaly\Streams\Platform\Support\Collection;
use Anomaly\Streams\Platform\Ui\Form\FormBuilder;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class MultipleFieldType
 *
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class MultipleFieldType extends FieldType
{

    /**
     * No database column.
     *
     * @var bool
     */
    protected $columnType = false;

    /**
     * The field type schema.
     *
     * @var string
     */
    protected $schema = MultipleFieldTypeSchema::class;

    /**
     * The input view.
     *
     * @var string
     */
    protected $inputView = null;

    /**
     * The filter view.
     *
     * @var string
     */
    protected $filterView = 'anomaly.field_type.multiple::filter';

    /**
     * The pre-defined handlers.
     *
     * @var array
     */
    protected $handlers = [
        'related' => 'Anomaly\MultipleFieldType\Handler\Related@handle',
        //'fields'      => 'Anomaly\MultipleFieldType\Handler\Fields@handle',
        //'assignments' => 'Anomaly\MultipleFieldType\Handler\Assignments@handle'
    ];

    /**
     * The field type rules.
     *
     * @var array
     */
    protected $rules = [
        'array',
    ];

    /**
     * The field type config.
     *
     * @var array
     */
    protected $config = [
        'mode' => 'tags',
    ];

    /**
     * The select input options.
     *
     * @var null|array
     */
    protected $options = null;

    /**
     * The cache repository.
     *
     * @var Repository
     */
    protected $cache;

    /**
     * The service container.
     *
     * @var Container
     */
    protected $container;

    /**
     * Create a new MultipleFieldType instance.
     *
     * @param Repository $cache
     * @param Container $container
     */
    public function __construct(Repository $cache, Container $container)
    {
        $this->cache     = $cache;
        $this->container = $container;
    }

    /**
     * Return the ids.
     *
     * @return array|mixed|static
     */
    public function ids()
    {
        $value = $this->getValue();

        if ($value instanceof \Illuminate\Support\Collection) {
            $value = $value
                ->pluck('id')
                ->all();
        }

        return array_filter((array) $value);
    }

    /**
     * Get the rules.
     *
     * @return array
     */
    public function getRules()
    {
        $rules = parent::getRules();

        if ($min = array_get($this->getConfig(), 'min')) {
            $rules[] = 'min:' . $min;
        }

        if ($max = array_get($this->getConfig(), 'max')) {
            $rules[] = 'max:' . $max;
        }

        return $rules;
    }

    /**
     * Return the config key.
     *
     * @return string
     */
    public function key()
    {
        $this->cache->put(
            'anomaly/multiple-field_type::' . ($key = md5(json_encode($this->getConfig()))),
            array_merge(
                $this->getConfig(),
                [
                    'field' => $this->getField(),
                    'entry' => get_class($this->getEntry()),
                ]
            ),
            30
        );

        return $key;
    }

    /**
     * Value table.
     *
     * @return string
     */
    public function table()
    {
        $value   = $this->getValue();
        $related = $this->getRelatedModel();

        if ($table = $this->config('value_table')) {
            $table = app($table);
        } else {
            $table = $related->call('new_multiple_field_type_value_table_builder');
        }

        /* @var ValueTableBuilder $table */
        $table->setConfig(new Collection($this->getConfig()))
            ->setFieldType($this)
            ->setModel($related);

        if (!$value instanceof EntryCollection) {
            $table->setSelected((array) $value);
        }

        if ($value instanceof EntryCollection) {
            $table->setSelected($value->ids());
        }

        return $table
            ->build()
            ->load()
            ->getTableContent();
    }

    /**
     * Get the related model.
     *
     * @return EloquentModel
     */
    public function getRelatedModel()
    {
        if (!$model = $this->config('related')) {
            throw new Exception('Config [related] is required.');
        }

        if (strpos($model, '.')) {

            /* @var StreamInterface $stream */
            $stream = dispatch_now(new GetStream($model));

            return $stream->getEntryModel();
        }

        return app($model);
    }

    /**
     * Get the relation.
     *
     * @return BelongsToMany
     */
    public function getRelation()
    {
        $entry = $this->getEntry();
        $model = $this->getRelatedModel();

        return $entry->belongsToMany(
            get_class($model),
            $this->getPivotTableName(),
            'entry_id',
            'related_id'
        )->orderBy($this->getPivotTableName() . '.sort_order', 'ASC');
    }

    /**
     * Get the pivot table.
     *
     * @return string
     */
    public function getPivotTableName()
    {
        return $this->entry->getTable() . '_' . $this->getField();
    }

    /**
     * Get the options.
     *
     * @return array
     */
    public function getOptions()
    {
        if ($this->options === null) {
            dispatch_now(new BuildOptions($this));
        }

        return $this->options;
    }

    /**
     * Set the options.
     *
     * @param  array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get the pre-defined handlers.
     *
     * @return array
     */
    public function getHandlers()
    {
        return $this->handlers;
    }

    /**
     * Return the input view.
     *
     * @return string
     */
    public function getInputView()
    {
        return $this->inputView ?: 'anomaly.field_type.multiple::' . $this->config('mode');
    }

    /**
     * Get the class.
     *
     * @return null|string
     */
    public function getClass()
    {
        if ($class = parent::getClass()) {
            return $class;
        }

        return $this->config('mode') == 'dropdown' ? 'custom-select form-control' : null;
    }

    /**
     * Handle saving the form data ourselves.
     *
     * @param FormBuilder $builder
     */
    public function handle(FormBuilder $builder)
    {
        $entry = $builder->getFormEntry();

        // See the accessor for how IDs are handled.
        $entry->{$this->getField()} = $this->getPostValue();
    }

    /**
     * Get the post value.
     *
     * @param  null $default
     * @return array
     */
    public function getPostValue($default = null)
    {
        if (is_array($value = parent::getPostValue($default))) {
            return array_filter($value);
        }

        return array_filter(explode(',', $value));
    }

    /**
     * Get the attributes.
     *
     * @param array $attributes
     * @return array
     */
    public function attributes(array $attributes = [])
    {
        return array_filter(
            array_merge(
                parent::attributes(),
                [
                    'data-key' => $this->key(),
                    'name' => $this->getInputName() . '[]',
                    'data-placeholder' => $this->getPlaceholder(),
                ],
                $attributes
            )
        );
    }

    /**
     * Fired just before version comparison.
     *
     * @param Collection $related
     */
    public function toArrayForComparison(Collection $related)
    {
        return $related->map(
            function (EloquentModel $model) {
                return array_diff_key(
                    $model->toArrayWithRelations(),
                    array_flip(
                        [
                            'id',
                            'sort_order',
                            'created_at',
                            'created_by_id',
                            'updated_at',
                            'updated_by_id',
                            'deleted_at',
                            'deleted_by_id',
                        ]
                    )
                );
            }
        )->toArray();
    }
}
