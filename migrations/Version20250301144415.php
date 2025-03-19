<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301144415 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_feed (user_id UUID NOT NULL, post_id UUID NOT NULL, created_at TIMESTAMP NOT NULL, PRIMARY KEY (user_id, post_id))');
        $this->addSql('COMMENT ON COLUMN user_feed.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_feed.post_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE INDEX IDX_4561D862B29A9964 ON user_feed (user_id)');
        $this->addSql('CREATE INDEX IDX_4561D862A02F368E ON user_feed (post_id)');
        $this->addSql('ALTER TABLE user_feed ADD CONSTRAINT FK_55EEAC61A76ED396 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_feed ADD CONSTRAINT FK_55EEAC616A5458E9 FOREIGN KEY (post_id) REFERENCES "post" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX user_feeds_created_at_idx ON user_feed (user_id, created_at);');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_feed DROP CONSTRAINT FK_55EEAC61A76ED396');
        $this->addSql('ALTER TABLE user_feed DROP CONSTRAINT FK_55EEAC616A5458E9');
        $this->addSql('DROP INDEX IDX_4561D862B29A9964');
        $this->addSql('DROP INDEX IDX_4561D862A02F368E');
        $this->addSql('DROP INDEX user_feeds_created_at_idx');
        $this->addSql('DROP TABLE user_feed');
    }
}
