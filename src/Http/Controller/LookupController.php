<?php namespace Anomaly\MultipleFieldType\Http\Controller;

use Anomaly\MultipleFieldType\Command\GetConfiguration;
use Anomaly\MultipleFieldType\Command\HydrateLookupTable;
use Anomaly\MultipleFieldType\Command\HydrateSelectedTable;
use Anomaly\MultipleFieldType\MultipleFieldType;
use Anomaly\MultipleFieldType\Table\LookupTableBuilder;
use Anomaly\MultipleFieldType\Table\SelectedTableBuilder;
use Anomaly\MultipleFieldType\Table\ValueTableBuilder;
use Anomaly\Streams\Platform\Entry\Contract\EntryInterface;
use Anomaly\Streams\Platform\Http\Controller\AdminController;
use Anomaly\Streams\Platform\Model\EloquentModel;
use Anomaly\Streams\Platform\Support\Collection;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Model;

/**
 * Class LookupController
 *
 * @link          http://pyrocms.com/
 * @author        PyroCMS, Inc. <support@pyrocms.com>
 * @author        Ryan Thompson <ryan@pyrocms.com>
 */
class LookupController extends AdminController
{

    /**
     * Return an index of entries from related stream.
     *
     * @param  MultipleFieldType                          $fieldType
     * @param                                             $key
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(MultipleFieldType $fieldType, $key)
    {
        /* @var Collection $config */
        $config = dispatch_sync(new GetConfiguration($key));

        $fieldType->mergeConfig($config->all());
        $fieldType->setField($config->get('field'));

        $related = $fieldType->getRelatedModel();
        $stream  = $this->entry($config);

        if ($table = $config->get('lookup_table')) {
            $table = $fieldType->makeTable($table, LookupTableBuilder::class);
        } else {
            $table = $related->newMultipleFieldTypeLookupTableBuilder();
        }

        /* @var LookupTableBuilder $table */
        $table->setConfig($config)
            ->setFieldType($stream->getFieldType($config->get('field')))
            ->setModel($related);

        return $table->render();
    }

    /**
     * @param MultipleFieldType $fieldType
     * @param                   $key
     */
    public function json(MultipleFieldType $fieldType, $key)
    {
        /* @var Collection $config */
        $config = dispatch_sync(new GetConfiguration($key));

        $fieldType->mergeConfig($config->all());
        $fieldType->setField($config->get('field'));

        /* @var EloquentModel $model */
        $model = $fieldType->getRelatedModel();

        $data = [];

        /* @var EntryInterface $item */
        foreach ($model->all() as $item) {
            $data[] = (object)[
                'id'   => $item->getId(),
                'text' => $item->getTitle(),
            ];
        }

        return $this->response->json($data);
    }

    /**
     * Return the selected entries.
     *
     * @param  SelectedTableBuilder $table
     * @param  MultipleFieldType    $fieldType
     * @param                       $key
     * @return null|string
     */
    public function selected(MultipleFieldType $fieldType, $key)
    {
        /* @var Collection $config */
        $config = dispatch_sync(new GetConfiguration($key));

        $fieldType->mergeConfig($config->all());
        $fieldType->setField($config->get('field'));
        $fieldType->setEntry($this->entry($config));

        $related = $fieldType->getRelatedModel();

        if ($table = $config->get('selected_table')) {
            $table = $fieldType->makeTable($table, SelectedTableBuilder::class);
        } else {
            $table = $related->newMultipleFieldTypeSelectedTableBuilder();
        }

        $uploaded = $this->request->get('uploaded', []);

        if (is_string($uploaded)) {
            $uploaded = explode(',', $uploaded);
        }

        /* @var SelectedTableBuilder $table */
        $table->setSelected(array_filter(array_map('intval', (array) $uploaded)))
            ->setModel($related)
            ->setFieldType($fieldType)
            ->setConfig($config)
            ->build()
            ->load();

        return $table->getTableContent();
    }

    /**
     * Make the configured parent entry.
     *
     * @param  Collection $config
     * @return Model
     * @throws \Exception
     */
    protected function entry(Collection $config)
    {
        $entry = $config->get('entry');

        if (!is_string($entry) || !is_subclass_of($entry, Model::class)) {
            throw new \Exception('The lookup configuration must name an entry model.');
        }

        return $this->container->make($entry);
    }
}
