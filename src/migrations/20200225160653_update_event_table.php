<?php

use Phinx\Migration\AbstractMigration;

class UpdateEventTable extends AbstractMigration
{
    // Handle events with multiple date
    public function change(): void
    {
        // irreversible migration !!!
        $table = $this->table('events');
        $table
            ->removeColumn('events_date')
            ->save();

        $table = $this->table('dates', ['id' => 'dates_id']);
        $table
            ->addColumn('dates_date', 'datetime')
            ->addColumn('dates_events_id', 'integer')
            ->addForeignKey('dates_events_id', 'events', 'events_id', [
                'delete' => 'NO_ACTION',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_dates_events_id'
            ])
            ->create();
    }
}
