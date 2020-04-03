<?php

use Phinx\Migration\AbstractMigration;

class UpdateEventTimeForMultiUrl extends AbstractMigration
{
    // Handle events with multiple url
    public function change(): void
    {
        // irreversible migration !!!
        $events = $this->table('events');
        $events
            ->removeColumn('events_url');

        $urls = $this->table('urls', ['id' => 'urls_id']);
        $urls
            ->addColumn('urls_url', 'string', ['limit' => 250])
            ->addColumn('urls_events_id', 'integer')
            ->addForeignKey('urls_events_id', 'events', 'events_id', [
                'delete' => 'NO_ACTION',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_urls_events_id'
            ])
            ->create();

        $events->save();
    }
}
