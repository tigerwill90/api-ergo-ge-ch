<?php

use Phinx\Migration\AbstractMigration;

class IncreaseOfficeNameLimit extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up(): void
    {
        $table = $this->table('offices');
        $table->changeColumn('offices_name', 'string', ['limit' => 60, 'null' => true]);
        $table->save();
    }

    public function down(): void
    {
        $table = $this->table('offices');
        $table->changeColumn('offices_name', 'string', ['limit' => 45, 'null' => true]);
        $table->save();
    }
}
