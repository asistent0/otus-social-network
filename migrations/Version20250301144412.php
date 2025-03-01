<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301144412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('COMMENT ON COLUMN dialog.participant1_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN dialog.participant2_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE INDEX IDX_4561D862B29A9963 ON dialog (participant1_id)');
        $this->addSql('CREATE INDEX IDX_4561D862A02F368D ON dialog (participant2_id)');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC61A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC616A5458E8 FOREIGN KEY (friend_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_55EEAC61A76ED395 ON friend (user_id)');
        $this->addSql('CREATE INDEX IDX_55EEAC616A5458E8 ON friend (friend_id)');
        $this->addSql('CREATE UNIQUE INDEX friendship_unique_idx ON friend (user_id, friend_id)');
        $this->addSql('COMMENT ON COLUMN message.participant1_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE INDEX IDX_B6BD307F5E46C4E2 ON message (dialog_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FF624B39D ON message (sender_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FB29A9963 ON message (participant1_id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DA76ED395 ON post (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dialog DROP CONSTRAINT FK_4561D862B29A9963');
        $this->addSql('ALTER TABLE dialog DROP CONSTRAINT FK_4561D862A02F368D');
        $this->addSql('DROP INDEX IDX_4561D862B29A9963');
        $this->addSql('DROP INDEX IDX_4561D862A02F368D');
        $this->addSql('COMMENT ON COLUMN dialog.participant1_id IS NULL');
        $this->addSql('COMMENT ON COLUMN dialog.participant2_id IS NULL');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F5E46C4E2');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307FF624B39D');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307FB29A9963');
        $this->addSql('DROP INDEX IDX_B6BD307F5E46C4E2');
        $this->addSql('DROP INDEX IDX_B6BD307FF624B39D');
        $this->addSql('DROP INDEX IDX_B6BD307FB29A9963');
        $this->addSql('COMMENT ON COLUMN message.participant1_id IS NULL');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8DA76ED395');
        $this->addSql('DROP INDEX IDX_5A8A6C8DA76ED395');
        $this->addSql('ALTER TABLE friend DROP CONSTRAINT FK_55EEAC61A76ED395');
        $this->addSql('ALTER TABLE friend DROP CONSTRAINT FK_55EEAC616A5458E8');
        $this->addSql('DROP INDEX IDX_55EEAC61A76ED395');
        $this->addSql('DROP INDEX IDX_55EEAC616A5458E8');
        $this->addSql('DROP INDEX friendship_unique_idx');
    }
}
