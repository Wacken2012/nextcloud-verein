<?php

declare(strict_types=1);

namespace OCA\Verein\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Migration for calendar events and RSVP tables (v0.3.0)
 */
class Version020300Date20250701100000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// Events table
		if (!$schema->hasTable('verein_events')) {
			$table = $schema->createTable('verein_events');
			
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('title', Types::STRING, [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('location', Types::STRING, [
				'notnull' => false,
				'length' => 255,
			]);
			$table->addColumn('event_type', Types::STRING, [
				'notnull' => true,
				'length' => 50,
				'default' => 'event',
				// Types: meeting, rehearsal, concert, event, deadline, other
			]);
			$table->addColumn('start_date', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->addColumn('end_date', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('all_day', Types::SMALLINT, [
				'notnull' => true,
				'default' => 0,
			]);
			$table->addColumn('recurring', Types::STRING, [
				'notnull' => false,
				'length' => 50,
				// Values: null, daily, weekly, monthly, yearly
			]);
			$table->addColumn('recurring_until', Types::DATE, [
				'notnull' => false,
			]);
			$table->addColumn('rsvp_enabled', Types::SMALLINT, [
				'notnull' => true,
				'default' => 0,
			]);
			$table->addColumn('rsvp_deadline', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('max_participants', Types::INTEGER, [
				'notnull' => false,
				'unsigned' => true,
			]);
			$table->addColumn('created_by', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->addColumn('updated_at', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('calendar_uri', Types::STRING, [
				'notnull' => false,
				'length' => 255,
				// Reference to Nextcloud Calendar event if synced
			]);
			
			$table->setPrimaryKey(['id']);
			$table->addIndex(['event_type'], 'vn_evt_type_idx');
			$table->addIndex(['start_date'], 'vn_evt_start_idx');
			$table->addIndex(['created_by'], 'vn_evt_creator_idx');
		}

		// RSVP responses table
		if (!$schema->hasTable('verein_event_rsvp')) {
			$table = $schema->createTable('verein_event_rsvp');
			
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('event_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('member_id', Types::BIGINT, [
				'notnull' => false,
				'unsigned' => true,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('response', Types::STRING, [
				'notnull' => true,
				'length' => 20,
				// Values: yes, no, maybe, pending
			]);
			$table->addColumn('comment', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('responded_at', Types::DATETIME, [
				'notnull' => true,
			]);
			
			$table->setPrimaryKey(['id']);
			$table->addIndex(['event_id'], 'vn_rsvp_event_idx');
			$table->addIndex(['user_id'], 'vn_rsvp_user_idx');
			$table->addUniqueIndex(['event_id', 'user_id'], 'vn_rsvp_unique');
		}

		return $schema;
	}
}
