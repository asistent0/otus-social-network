<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223130337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Добавление диалогов и сообщений';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dialog (id SERIAL NOT NULL, participant1_id UUID NOT NULL, participant2_id UUID NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4561D862B29A9963 ON dialog (participant1_id)');
        $this->addSql('CREATE INDEX IDX_4561D862A02F368D ON dialog (participant2_id)');
        $this->addSql('CREATE UNIQUE INDEX dialog_participants_unique_idx ON dialog (participant1_id, participant2_id)');
        $this->addSql('COMMENT ON COLUMN dialog.participant1_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN dialog.participant2_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE message (id SERIAL NOT NULL, dialog_id INT NOT NULL, sender_id UUID NOT NULL, text TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6BD307F5E46C4E2 ON message (dialog_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FF624B39D ON message (sender_id)');
        $this->addSql('COMMENT ON COLUMN message.sender_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN message.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE dialog ADD CONSTRAINT FK_4561D862B29A9963 FOREIGN KEY (participant1_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dialog ADD CONSTRAINT FK_4561D862A02F368D FOREIGN KEY (participant2_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F5E46C4E2 FOREIGN KEY (dialog_id) REFERENCES dialog (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dialog DROP CONSTRAINT FK_4561D862B29A9963');
        $this->addSql('ALTER TABLE dialog DROP CONSTRAINT FK_4561D862A02F368D');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F5E46C4E2');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307FF624B39D');
        $this->addSql('DROP TABLE dialog');
        $this->addSql('DROP TABLE message');
    }
}
