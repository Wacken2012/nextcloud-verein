<?php

declare(strict_types=1);

namespace OCA\NextcloudVerein\Migration;

use Closure;
use OCP\DB\ISchemaTools;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration
 *
 * Please note that not all valid tables/columns were automatically detected.
 * Please review the migration and add missing tables/columns as needed.
 */
class Version0020000Date20260101000000 extends SimpleMigrationStep {
	public function __construct(
		private ISchemaTools $schemaTools,
	) {
	}

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?string {
		/** @var ISchemaTools $schemaTools */
		$schema = $schemaClosure();

		if (!$schema->hasTable('nextcloud_verein_reminders')) {
			$table = $schema->createTable('nextcloud_verein_reminders');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('member_id', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('fee_id', 'integer', [
				'notnull' => false,
			]);
			$table->addColumn('reminder_level', 'integer', [
				'notnull' => true,
				'default' => 1,
			]);
			$table->addColumn('status', 'string', [
				'notnull' => true,
				'length' => 20,
				'default' => 'pending',
			]);
			$table->addColumn('sent_date', 'datetime', [
				'notnull' => false,
			]);
			$table->addColumn('due_date', 'datetime', [
				'notnull' => true,
			]);
			$table->addColumn('next_reminder_date', 'datetime', [
				'notnull' => false,
			]);
			$table->addColumn('created_at', 'datetime', [
				'notnull' => true,
				'default' => 'CURRENT_TIMESTAMP',
			]);
			$table->addColumn('updated_at', 'datetime', [
				'notnull' => true,
				'default' => 'CURRENT_TIMESTAMP',
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['member_id'], 'reminders_member_id_idx');
			$table->addIndex(['fee_id'], 'reminders_fee_id_idx');
			$table->addIndex(['status'], 'reminders_status_idx');
			$table->addIndex(['next_reminder_date'], 'reminders_next_date_idx');
		}

		if (!$schema->hasTable('nextcloud_verein_reminder_log')) {
			$table = $schema->createTable('nextcloud_verein_reminder_log');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('reminder_id', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('member_id', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('action', 'string', [
				'notnull' => true,
				'length' => 50,
			]);
			$table->addColumn('email_sent', 'boolean', [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('email_error', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('created_at', 'datetime', [
				'notnull' => true,
				'default' => 'CURRENT_TIMESTAMP',
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['reminder_id'], 'reminder_log_reminder_id_idx');
			$table->addIndex(['member_id'], 'reminder_log_member_id_idx');
			$table->addIndex(['created_at'], 'reminder_log_created_idx');
		}

		if (!$schema->hasTable('nextcloud_verein_reminder_settings')) {
			$table = $schema->createTable('nextcloud_verein_reminder_settings');
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('setting_key', 'string', [
				'notnull' => true,
				'length' => 100,
			]);
			$table->addColumn('setting_value', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('updated_at', 'datetime', [
				'notnull' => true,
				'default' => 'CURRENT_TIMESTAMP',
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['setting_key'], 'reminder_settings_key_idx');
		}

		return null;
	}
}
