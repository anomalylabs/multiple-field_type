<?php namespace Anomaly\MultipleFieldType\Table;

/**
 * Class LookupTableColumns
 *
 * @link          http://pyrocms.com/
 * @author        PyroCMS, Inc. <support@pyrocms.com>
 * @author        Ryan Thompson <ryan@pyrocms.com>
 */
class LookupTableColumns
{

    /**
     * Handle the command.
     *
     * @param LookupTableBuilder $builder
     */
    public function handle(LookupTableBuilder $builder)
    {
        $fieldType = $builder->getFieldType();
        $stream    = $builder->getTableStream();
        $column    = $stream->getTitleColumn();

        if ($column == $fieldType->getRelationKeyName()) {

            $builder->setColumns([]);

            return;
        }

        $builder->setColumns(
            [
                $column,
            ]
        );
    }
}
