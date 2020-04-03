<?php

use Phinx\Migration\AbstractMigration;

class CreateModelMigration extends AbstractMigration
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
    public function change(): void
    {
        $this->createUsers();
        $this->createOffices();
        $this->createOfficesUsers();
        $this->createContacts();
        $this->createTherapists();
        $this->createPhones();
        $this->createEmails();
        $this->createCategories();
        $this->createTherapistsCategories();
        $this->insertCategories();
    }

    public function createUsers(): void
    {
        $table = $this->table('users', ['id' => 'users_id']);
        $table
            ->addColumn('users_email', 'string', ['limit' => 250])
            ->addColumn('users_hashed_password', 'string', ['limit' => 256])
            ->addColumn('users_roles', 'string', ['limit' => 75])
            ->addColumn('users_firstname', 'string', ['limit' => 45])
            ->addColumn('users_lastname', 'string', ['limit' => 45])
            ->addColumn('users_active', 'boolean')
            ->addColumn('users_cookieValue', 'string', ['limit' => 256])
            ->addColumn('users_reset_jwt', 'string', ['limit' => 500])
            ->addColumn('users_created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('users_updated', 'datetime')
            ->addIndex(['users_email'], ['unique' => true, 'name' => 'users_email_UNIQUE'])
            ->addIndex(['users_cookieValue'], ['unique' => true, 'name' => 'users_cookieValue_UNIQUE'])
            ->addIndex(['users_reset_jwt'], ['unique' => true, 'name' => 'users_reset_jwt_UNIQUE'])
            ->create();

        $this->execute('ALTER TABLE `users` MODIFY COLUMN `users_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }

    public function createOffices(): void
    {
        $table = $this->table('offices', ['id' => 'offices_id']);
        $table
            ->addColumn('offices_name', 'string', ['limit' => 45])
            ->addColumn('offices_email', 'string', ['limit' => 250])
            ->addColumn('offices_created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('offices_updated', 'datetime')
            ->addIndex(['offices_name'], ['unique' => true, 'name' => 'offices_name_UNIQUE'])
            ->addIndex(['offices_email'], ['unique' => true, 'name' => 'offices_email_UNIQUE'])
            ->create();

        $this->execute('ALTER TABLE `offices` MODIFY COLUMN `offices_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }

    public function createOfficesUsers(): void
    {
        $table = $this->table('officesUsers',
            ['id' => false, 'primary_key' => ['officesUsers_users_id', 'officesUsers_offices_id']]);
        $table
            ->addColumn('officesUsers_users_id', 'integer')
            ->addColumn('officesUsers_offices_id', 'integer')
            ->addIndex(['officesUsers_users_id'], ['name' => 'fk_officesUsers_users_id_idx'])
            ->addIndex(['officesUsers_offices_id'], ['name' => 'fk_officesUsers_offices_id_idx'])
            ->addForeignKey('officesUsers_users_id', 'users', 'users_id', [
                'delete' => 'NO_ACTION',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_officesUsers_users_id'
            ])
            ->addForeignKey('officesUsers_offices_id', 'offices', 'offices_id', [
                'delete' => 'NO_ACTION',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_officesUsers_offices_id'
            ])
            ->create();
    }

    public function createContacts(): void
    {
        $table = $this->table('contacts', ['id' => 'contacts_id', 'primary_key' => ['contacts_offices_id']]);
        $table
            ->addColumn('contacts_street', 'string', ['limit' => 80])
            ->addColumn('contacts_city', 'string', ['limit' => 45])
            ->addColumn('contacts_npa', 'string', ['limit' => 10])
            ->addColumn('contacts_cp', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('contacts_phone', 'string', ['limit' => 45, 'null' => true])
            ->addColumn('contacts_fax', 'string', ['limit' => 45, 'null' => true])
            ->addColumn('contacts_created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('contacts_updated', 'datetime')
            ->addColumn('contacts_offices_id', 'integer')
            ->addIndex(['contacts_street'], ['unique' => true, 'name' => 'contacts_street_UNIQUE'])
            ->addForeignKey('contacts_offices_id', 'offices', 'offices_id', [
                'delete' => 'NO_ACTION',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_contacts_offices_id'
            ])
            ->create();

        $this->execute('ALTER TABLE `contacts` MODIFY COLUMN `contacts_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }

    public function createTherapists(): void
    {
        $table = $this->table('therapists', ['id' => 'therapists_id']);
        $table
            ->addColumn('therapists_title', 'string', ['limit' => 10])
            ->addColumn('therapists_firstname', 'string', ['limit' => 45])
            ->addColumn('therapists_lastname', 'string', ['limit' => 45])
            ->addColumn('therapists_home', 'boolean')
            ->addColumn('therapists_created', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('therapists_updated', 'datetime')
            ->addColumn('therapists_offices_id', 'integer')
            ->addForeignKey('therapists_offices_id', 'offices', 'offices_id', [
                'delete' => 'NO_ACTION',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_therapists_offices_id'
            ])
            ->create();

        $this->execute('ALTER TABLE `therapists` MODIFY COLUMN `therapists_updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
    }

    public function createPhones(): void
    {
        $table = $this->table('phones', ['id' => 'phones_id']);
        $table
            ->addColumn('phones_type', 'string', ['limit' => 25])
            ->addColumn('phones_number', 'string', ['limit' => 45])
            ->addColumn('phones_therapists_id', 'integer')
            ->addForeignKey('phones_therapists_id', 'therapists', 'therapists_id', [
                'delete' => 'NO_ACTION',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_phones_therapists_id'
            ])
            ->create();
    }

    public function createEmails(): void
    {
        $table = $this->table('emails', ['id' => 'emails_id']);
        $table
            ->addColumn('emails_address', 'string', ['limit' => '250'])
            ->addColumn('emails_therapists_id', 'integer')
            ->addForeignKey('emails_therapists_id', 'therapists', 'therapists_id', [
                'delete' => 'NO_ACTION',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_emails_therapists_id'
            ])
            ->create();
    }

    public function createCategories(): void
    {
        $table = $this->table('categories', ['id' => 'categories_id']);
        $table
            ->addColumn('categories_name', 'string', ['limit' => 45])
            ->addColumn('categories_description', 'string', ['limit' => 255, 'null' => true])
            ->addIndex(['categories_name'], ['unique' => true, 'name' => 'categories_name_UNIQUE'])
            ->create();
    }

    public function createTherapistsCategories(): void
    {
        $table = $this->table('therapistsCategories', [
            'id' => false,
            'primary_key' => ['therapistsCategories_therapists_id', 'therapistsCategories_categories_id']
        ]);
        $table
            ->addColumn('therapistsCategories_therapists_id', 'integer')
            ->addColumn('therapistsCategories_categories_id', 'integer')
            ->addIndex(['therapistsCategories_therapists_id'], ['name' => 'fk_therapistsCategories_therapists_id_idx'])
            ->addIndex(['therapistsCategories_categories_id'], ['name' => 'fk_therapistsCategories_categories_id_idx'])
            ->addForeignKey('therapistsCategories_therapists_id', 'therapists', 'therapists_id', [
                'delete' => 'NO_ACTION',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_therapistsCategories_therapists_id'
            ])
            ->addForeignKey('therapistsCategories_categories_id', 'categories', 'categories_id', [
                'delete' => 'NO_ACTION',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_therapistsCategories_categories_id'
            ])
            ->create();
    }

    public function insertCategories(): void
    {
        $rows = [
            [
                'categories_id' => 1,
                'categories_name' => 'Gériatrie',
                'categories_description' => 'La médecine gériatrique est la spécialité médicale concernée par les affections physiques, mentales, fonctionnelles et sociales des malades âgés.'
            ],
            [
                'categories_id' => 2,
                'categories_name' => 'Médecine physique',
                'categories_description' => 'La médecine physique et de réadaptation (MPR) est une spécialité médicale orientée vers la récupération de capacités fonctionnelles et de qualité de vie des patients atteints de handicap.'
            ],
            [
                'categories_id' => 3,
                'categories_name' => 'Pathologie membre supérieur',
                'categories_description' => null
            ],
            [
                'categories_id' => 4,
                'categories_name' => 'Pédiatrie',
                'categories_description' => 'La pédiatrie est une branche spécialisée de la médecine qui étudie le développement psycho-moteur et physiologique normal de l\'enfant, ainsi que toute la pathologie qui y a trait.'
            ],
            [
                'categories_id' => 5,
                'categories_name' => 'Santé mentale',
                'categories_description' => 'La santé mentale définit le bien-être psychique, émotionnel et cognitif ou une absence de trouble mental.'
            ]
        ];
        $this->table('categories')->insert($rows)->save();
    }
}
