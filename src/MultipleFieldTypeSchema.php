<?php namespace Anomaly\MultipleFieldType;

use Anomaly\Streams\Platform\Addon\FieldType\FieldType;
use Anomaly\Streams\Platform\Addon\FieldType\FieldTypeSchema;
use Anomaly\Streams\Platform\Assignment\Contract\AssignmentInterface;
use Illuminate\Database\Schema\Blueprint;

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
     * @param Blueprint           $table
     * @param AssignmentInterface $assignment
     */
    public function addColumn(Blueprint $table, AssignmentInterface $assignment)
    {
        if (array_get($this->fieldType->getConfig(), 'mapped', false)) {
            return;
        };

        $table = $table->getTable() . '_' . $this->fieldType->getField();

        $this->schema->dropIfExists($table);

        $this->schema->create(
            $table,
            function (Blueprint $table) {

                $table->increments('id');
                $table->integer('entry_id');
                $table->integer('related_id');
                $table->integer('sort_order')->nullable();

                $table->unique(
                    ['entry_id', 'related_id'],
                    md5($table->getTable() . '_' . $this->fieldType->getField().'-unique-relations')
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
        if (array_get($this->fieldType->getConfig(), 'mapped', false)) {
            return;
        };

        $this->schema->rename(
            $table->getTable() . '_' . $from->getField(),
            $table->getTable() . '_' . $this->fieldType->getField()
        );
    }

    /**
     * Drop the pivot table.
     *
     * @param Blueprint $table
     */
    public function dropColumn(Blueprint $table)
    {
        if (array_get($this->fieldType->getConfig(), 'mapped', false)) {
            return;
        };

        $this->schema->dropIfExists(
            $table->getTable() . '_' . $this->fieldType->getField()
        );
    }

}
