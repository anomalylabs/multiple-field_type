<?php namespace Anomaly\MultipleFieldType;

use Anomaly\Streams\Platform\Addon\FieldType\FieldTypeQuery;
use Anomaly\Streams\Platform\Ui\Table\Component\Filter\Contract\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class MultipleFieldTypeQuery
 *
 * @link          http://pyrocms.com/
 * @author        PyroCMS, Inc. <support@pyrocms.com>
 * @author        Ryan Thompson <ryan@pyrocms.com>
 */
class MultipleFieldTypeQuery extends FieldTypeQuery
{

    /**
     * Handle the filter query.
     *
     * @param Builder         $query
     * @param FilterInterface $filter
     */
    public function filter(Builder $query, FilterInterface $filter)
    {
        $mapped  = array_get($this->fieldType->getConfig(), 'mapped', false);
        $stream  = $filter->getStream();
        $related =  $this->fieldType->getRelatedModel();

        $relation = $stream->getSlug();
        $table    = $related->{$relation}()->getTable();

        $query->leftJoin(
            $table . ' AS filter_' . $filter->getField(),
            $stream->getEntryTableName()  . '.id',
            '=',
            'filter_' . $filter->getField() . ($mapped ? '.related_id' : '.entry_id')
        )->where('filter_' . $filter->getField() . ($mapped ? '.entry_id' : '.related_id'), $filter->getValue());
    }
}
