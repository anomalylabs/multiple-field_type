<?php

namespace Anomaly\MultipleFieldType;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Anomaly\Streams\Platform\Addon\FieldType\FieldType;
use Anomaly\Streams\Platform\Field\Contract\FieldInterface;
use Anomaly\Streams\Platform\Addon\FieldType\FieldTypeSchema;
use Anomaly\Streams\Platform\Assignment\Contract\AssignmentInterface;

/**
 * Class MultipleFieldTypeSchema
 *
 * @link          http://pyrocms.com/
 * @author        PyroCMS, Inc. <support@pyrocms.com>
 * @author        Ryan Thompson <ryan@pyrocms.com>
 */
class MultipleFieldTypeSchema extends FieldTypeSchema
{

    /**
     * Add the field type's pivot table.
     *
     * @param Blueprint $table
     * @param FieldInterface $field
     */
    public function addColumn(Blueprint $table, FieldInterface $field)
    {
        $table = $table->getTable() . '_' . $field->slug;

        /**
         * @var $schema Builder
         */
        $schema = Schema::connection(config('database.default'));

        $schema->dropIfExists($table);

        $schema->create(
            $table,
            function (Blueprint $table) use ($field) {

                $table->increments('id');
                $table->integer('entry_id');
                $table->integer('related_id');
                $table->integer('sort_order')->nullable();

                $table->unique(
                    ['entry_id', 'related_id'],
                    md5($table->getTable() . '_' . $field->slug . '-unique-relations')
                );
            }
        );
    }

    /**
     * Rename the pivot table.
     *
     * @param Blueprint $table
     * @param FieldType $from
     */
    public function renameColumn(Blueprint $table, FieldType $from)
    {
        $schema->rename(
            $table->getTable() . '_' . $from->field,
            $table->getTable() . '_' . $this->fieldType->field
        );
    }

    /**
     * Drop the pivot table.
     *
     * @param Blueprint $table
     */
    public function dropColumn(Blueprint $table)
    {
        $schema->dropIfExists(
            $table->getTable() . '_' . $this->fieldType->field
        );
    }
}
