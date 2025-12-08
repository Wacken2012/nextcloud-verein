<?php
namespace OCA\Verein\Migration;

use OCA\Verein\Db\MigrationStep;

class Version20251208AddReminderLevels extends MigrationStep {
    public function change() {
        $this->addTable('nextcloud_verein_reminder_levels')
            ->addColumn('id','integer')
            ->addColumn('level','integer')
            ->addColumn('delay_days','integer')
            ->addColumn('fee_cents','integer', ['null' => true])
            ->addColumn('template_id','string', ['length' => 255, 'null' => true])
            ->addColumn('active','boolean', ['default' => true])
            ->addColumn('created_at','datetime')
            ->addColumn('updated_at','datetime')
            ->addPrimaryKey(['id'])
            ->execute();

        $this->addTable('nextcloud_verein_reminder_logs')
            ->addColumn('id','integer')
            ->addColumn('member_id','integer')
            ->addColumn('fee_id','integer', ['null' => true])
            ->addColumn('reminder_level_id','integer')
            ->addColumn('sent_at','datetime')
            ->addColumn('action','string', ['length' => 255])
            ->addColumn('payload','text', ['null' => true])
            ->addPrimaryKey(['id'])
            ->execute();
    }
}
