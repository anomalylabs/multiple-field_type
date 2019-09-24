<?php namespace Anomaly\MultipleFieldType\Http\Controller;

use Anomaly\MultipleFieldType\Command\GetConfiguration;
use Anomaly\MultipleFieldType\MultipleFieldType;
use Anomaly\MultipleFieldType\Table\LookupTableBuilder;
use Anomaly\MultipleFieldType\Table\SelectedTableBuilder;
use Anomaly\Streams\Platform\Entry\Contract\EntryInterface;
use Anomaly\Streams\Platform\Http\Controller\AdminController;
use Anomaly\Streams\Platform\Model\EloquentModel;
use Anomaly\Streams\Platform\Support\Collection;

/**
 * Class LookupController
 *
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class LookupController extends AdminController
{

    /**
     * Return an index of entries from related stream.
     *
     * @param $key
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index($key)
    {
        /* @var Collection $config */
        $config = dispatch_now(new GetConfiguration($key));

        $related = app($config->get('related'));

        if ($table = $config->get('lookup_table')) {
            $table = app($table);
        } else {
            $table = $related->newMultipleFieldTypeLookupTableBuilder();
        }

        /* @var LookupTableBuilder $table */
        $table->setConfig($config)
            ->setModel($related);

        return $table->render();
    }

    /**
     * Return JSON.
     *
     * @param MultipleFieldType $fieldType
     * @param $key
     * @return \Illuminate\Http\JsonResponse
     */
    public function json(MultipleFieldType $fieldType, $key)
    {
        /* @var Collection $config */
        $config = dispatch_now(new GetConfiguration($key));

        $fieldType->mergeConfig($config->all());

        /* @var EloquentModel $model */
        $model = app($config->get('related'));

        $data = [];

        /* @var EntryInterface $item */
        foreach ($model->all() as $item) {
            $data[] = (object)[
                'id'   => $item->getId(),
                'text' => $item->getTitle(),
            ];
        }

        return response()->json($data);
    }

    /**
     * Return the selected entries.
     *
     * @param  SelectedTableBuilder $table
     * @param  MultipleFieldType $fieldType
     * @param                       $key
     * @return null|string
     */
    public function selected(MultipleFieldType $fieldType, $key)
    {
        /* @var Collection $config */
        $config = dispatch_now(new GetConfiguration($key));

        $fieldType->mergeConfig($config->all());
        $fieldType->setField($config->get('field'));
        $fieldType->setEntry(app($config->get('entry')));

        $related = app($config->get('related'));

        if ($table = $config->get('selected_table')) {
            $table = app($table);
        } else {
            $table = $related->call('new_multiple_field_type_selected_table_builder');
        }

        /* @var SelectedTableBuilder $table */
        $table->setSelected(array_filter(explode(',', request('uploaded'))))
            ->setModel($config->get('related'))
            ->setFieldType($fieldType)
            ->setConfig($config)
            ->build()
            ->load();

        return $table->getTableContent();
    }
}
