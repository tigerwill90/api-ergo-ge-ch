<?php

use Phinx\Migration\AbstractMigration;

class RemoveUniqueIndexOnOfficesAndContacts extends AbstractMigration
{
    public function up(): void {
        $table = $this->table('offices');
        $table->removeIndexByName('offices_email_UNIQUE');
        $table->save();

        $table = $this->table('contacts');
        $table->removeIndexByName('contacts_street_UNIQUE');
        $table->save();
    }

    public function down(): void {

    }
}
