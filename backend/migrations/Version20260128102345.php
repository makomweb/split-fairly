<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * This migration was previously executed but removed from version control.
 * It is restored here to maintain database migration history integrity.
 * No changes are applied as the database has already been migrated.
 */
final class Version20260128102345 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Legacy migration - already executed in database';
    }

    public function up(Schema $schema): void
    {
        // Migration was already executed - no changes needed
    }

    public function down(Schema $schema): void
    {
        // Not reversible
    }
}
