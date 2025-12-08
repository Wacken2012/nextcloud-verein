<?php

declare(strict_types=1);

namespace OCA\Verein\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version20251208AddReminderLevels extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if (!$schema->hasTable('verein_reminder_levels')) {
            $table = $schema->createTable('verein_reminder_levels');
            $table->addColumn('id', Types::BIGINT, [
                'autoincrement' => true,
                'notnull' => true,
                'unsigned' => true,
            ]);
            $table->addColumn('level', Types::INTEGER, [
                'notnull' => true,
                'unsigned' => true,
            ]);
            $table->addColumn('delay_days', Types::INTEGER, [
                'notnull' => true,
                'unsigned' => true,
            ]);
            $table->addColumn('fee_cents', Types::INTEGER, [
                'notnull' => false,
                'unsigned' => true,
            ]);
            $table->addColumn('template_id', Types::STRING, [
                'notnull' => false,
                'length' => 255,
            ]);
            $table->addColumn('active', Types::SMALLINT, [
                'notnull' => true,
                'default' => 1,
            ]);
            $table->addColumn('created_at', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->addColumn('updated_at', Types::DATETIME, [
                'notnull' => false,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['level'], 'vn_rem_level_unique');
        }

        if (!$schema->hasTable('verein_reminder_logs')) {
            $table = $schema->createTable('verein_reminder_logs');
            $table->addColumn('id', Types::BIGINT, [
                'autoincrement' => true,
                'notnull' => true,
                'unsigned' => true,
            ]);
            $table->addColumn('member_id', Types::BIGINT, [
                'notnull' => false,
                'unsigned' => true,
            ]);
            $table->addColumn('fee_id', Types::BIGINT, [
                'notnull' => false,
                'unsigned' => true,
            ]);
            $table->addColumn('reminder_level_id', Types::BIGINT, [
                'notnull' => true,
                'unsigned' => true,
            ]);
            $table->addColumn('sent_at', Types::DATETIME, [
                'notnull' => true,
            ]);
            $table->addColumn('action', Types::STRING, [
                'notnull' => true,
                'length' => 255,
            ]);
            $table->addColumn('payload', Types::TEXT, [
                'notnull' => false,
            ]);
            $table->setPrimaryKey(['id']);
            $table->addIndex(['member_id'], 'vn_rem_log_member_idx');
            $table->addIndex(['reminder_level_id'], 'vn_rem_log_level_idx');
        }

        return $schema;
    }
}
