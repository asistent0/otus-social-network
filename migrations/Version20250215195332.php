<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250215195332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление таблицы друзей';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE friend (id SERIAL NOT NULL, user_id UUID NOT NULL, friend_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_55EEAC61A76ED395 ON friend (user_id)');
        $this->addSql('CREATE INDEX IDX_55EEAC616A5458E8 ON friend (friend_id)');
        $this->addSql('COMMENT ON COLUMN friend.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN friend.friend_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN friend.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC61A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC616A5458E8 FOREIGN KEY (friend_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX friendship_unique_idx ON friend (user_id, friend_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE friend DROP CONSTRAINT FK_55EEAC61A76ED395');
        $this->addSql('ALTER TABLE friend DROP CONSTRAINT FK_55EEAC616A5458E8');
        $this->addSql('DROP TABLE friend');
    }
}
