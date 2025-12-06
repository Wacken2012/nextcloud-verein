<?php

declare(strict_types=1);

namespace OCA\Verein\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Migration für DSGVO-Audit-Log und Consent-Tabellen (v0.3.0)
 */
class Version020300Date20250701000000 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        // Audit-Log Tabelle für DSGVO-Aktionen
        if (!$schema->hasTable('verein_audit_log')) {
            $table = $schema->createTable('verein_audit_log');
            
            $table->addColumn('id', Types::BIGINT, [
                'autoincrement' => true,
                'notnull' => true,
                'unsigned' => true,
            ]);
            
            $table->addColumn('member_id', Types::INTEGER, [
                'notnull' => true,
                'unsigned' => true,
            ]);
            
            $table->addColumn('user_id', Types::STRING, [
                'notnull' => false,
                'length' => 64,
            ]);
            
            $table->addColumn('action', Types::STRING, [
                'notnull' => true,
                'length' => 64,
            ]);
            
            $table->addColumn('details', Types::TEXT, [
                'notnull' => false,
            ]);
            
            $table->addColumn('ip_address', Types::STRING, [
                'notnull' => false,
                'length' => 45,
            ]);
            
            $table->addColumn('user_agent', Types::STRING, [
                'notnull' => false,
                'length' => 500,
            ]);
            
            $table->addColumn('created_at', Types::DATETIME, [
                'notnull' => true,
            ]);
            
            $table->setPrimaryKey(['id']);
            $table->addIndex(['member_id'], 'vn_auditlog_mid');
            $table->addIndex(['user_id'], 'vn_auditlog_uid');
            $table->addIndex(['action'], 'vn_auditlog_act');
            $table->addIndex(['created_at'], 'vn_auditlog_dt');
        }

        // Einwilligungen Tabelle (persistiert statt in appconfig)
        if (!$schema->hasTable('verein_consents')) {
            $table = $schema->createTable('verein_consents');
            
            $table->addColumn('id', Types::BIGINT, [
                'autoincrement' => true,
                'notnull' => true,
                'unsigned' => true,
            ]);
            
            $table->addColumn('member_id', Types::INTEGER, [
                'notnull' => true,
                'unsigned' => true,
            ]);
            
            $table->addColumn('consent_type', Types::STRING, [
                'notnull' => true,
                'length' => 64,
            ]);
            
            $table->addColumn('given', Types::SMALLINT, [
                'notnull' => true,
                'default' => 0,
            ]);
            
            $table->addColumn('given_at', Types::DATETIME, [
                'notnull' => false,
            ]);
            
            $table->addColumn('revoked_at', Types::DATETIME, [
                'notnull' => false,
            ]);
            
            $table->addColumn('ip_address', Types::STRING, [
                'notnull' => false,
                'length' => 45,
            ]);
            
            $table->addColumn('source', Types::STRING, [
                'notnull' => false,
                'length' => 64,
            ]);
            
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['member_id', 'consent_type'], 'vn_consent_uniq');
            $table->addIndex(['consent_type'], 'vn_consent_type');
        }

        // Löschanfragen / Retention Policy Tabelle
        if (!$schema->hasTable('verein_deletions')) {
            $table = $schema->createTable('verein_deletions');
            
            $table->addColumn('id', Types::BIGINT, [
                'autoincrement' => true,
                'notnull' => true,
                'unsigned' => true,
            ]);
            
            $table->addColumn('member_id', Types::INTEGER, [
                'notnull' => true,
                'unsigned' => true,
            ]);
            
            $table->addColumn('requested_by', Types::STRING, [
                'notnull' => false,
                'length' => 64,
            ]);
            
            $table->addColumn('deletion_type', Types::STRING, [
                'notnull' => true,
                'length' => 20,
                'default' => 'soft',
            ]);
            
            $table->addColumn('status', Types::STRING, [
                'notnull' => true,
                'length' => 20,
                'default' => 'pending',
            ]);
            
            $table->addColumn('reason', Types::TEXT, [
                'notnull' => false,
            ]);
            
            $table->addColumn('scheduled_for', Types::DATETIME, [
                'notnull' => false,
            ]);
            
            $table->addColumn('executed_at', Types::DATETIME, [
                'notnull' => false,
            ]);
            
            $table->addColumn('executed_by', Types::STRING, [
                'notnull' => false,
                'length' => 64,
            ]);
            
            $table->addColumn('created_at', Types::DATETIME, [
                'notnull' => true,
            ]);
            
            $table->setPrimaryKey(['id']);
            $table->addIndex(['member_id'], 'vn_del_mid');
            $table->addIndex(['status'], 'vn_del_status');
            $table->addIndex(['scheduled_for'], 'vn_del_sched');
        }

        return $schema;
    }
}
