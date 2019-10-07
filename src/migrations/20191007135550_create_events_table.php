<?php

use Phinx\Migration\AbstractMigration;

class CreateEventsTable extends AbstractMigration
{
    public function change() : void
    {
        $table = $this->table('events', ['id' => 'events_id']);
        $table
            ->addColumn('events_title', 'string', ['limit' => 50])
            ->addColumn('events_subtitle', 'string', ['limit' => 50, 'null' => true])
            ->addColumn('events_date', 'datetime', ['null' => true])
            ->addColumn('events_description', 'string', ['limit' => 350])
            ->addColumn('events_url', 'string', ['limit' => 250, 'null' => true])
            ->addColumn('events_created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('events_updated', 'datetime')
            ->create();

        $this->execute('ALTER TABLE `events` MODIFY COLUMN `events_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }
}
