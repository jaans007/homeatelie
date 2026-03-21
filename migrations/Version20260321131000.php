<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260321131000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index for post_view lookup';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX post_view_lookup_idx ON post_view (post_id, viewed_at)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX post_view_lookup_idx ON post_view');
    }
}
