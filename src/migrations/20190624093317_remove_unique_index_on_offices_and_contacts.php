<?php

use Phinx\Migration\AbstractMigration;

class RemoveUniqueIndexOnOfficesAndContacts extends AbstractMigration
{
    public function up(): void {
        $table = $this->table('offices');
        $table->changeColumn('offices_email', 'string', ['limit' => 250, 'null' => true]);
        $table->save();

        $table = $this->table('contacts');
        $table->removeIndexByName('contacts_street_UNIQUE');
        $table->save();
    }

    public function down(): void {
        $table = $this->table('offices');
        $table->changeColumn('offices_email', 'string', ['limit' => 250, 'null' => false]);
        $table->save();

        $table = $this->table('contacts');
        $table->addIndex(['contacts_street'], ['unique' => true, 'name' => 'contacts_street_UNIQUE']);
        $table->save();
    }
}
