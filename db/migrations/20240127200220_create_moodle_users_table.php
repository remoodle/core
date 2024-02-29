<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMoodleUsersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('moodle_users', ["id" => false, 'primary_key' => 'moodle_id']);
        $table
            ->addColumn('moodle_id', 'integer', ['signed' => false, 'null' => false])            
            
            ->addColumn('barcode', 'string', ['null' => false])
            ->addColumn('name', 'string', ['null' => false])
            ->addColumn('moodle_token', 'string', ['null' => false])
            ->addColumn('email', 'string', ['null' => true])
            ->addColumn('email_verified_at', 'timestamp', ['null' => true])
            
            ->addColumn('grades_notification', 'boolean', ['default' => false])
            ->addColumn('deadlines_notification', 'boolean', ['default' => false])
            
            ->addColumn('password_hash', 'string', ['null' => true])
            ->addColumn('name_alias', 'string', ['null' => true])
            
            ->addIndex(['email'], ['unique' => true])
            ->addIndex(['name_alias'], ['unique' => true])
            ->addIndex(['barcode'], ['unique' => true])
            ->addIndex(['moodle_token'], ['unique' => true])
            ->create();
    }
}
