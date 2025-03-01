<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223202644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Изменение идентификаторов таблиц';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dialog ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE message ALTER id TYPE BIGINT');
        $this->addSql('ALTER TABLE message ALTER dialog_id TYPE BIGINT');
        $this->addSql('DROP INDEX "idx_4561d862a02f368d";');
        $this->addSql('DROP INDEX "idx_4561d862b29a9963";');
        $this->addSql('ALTER TABLE "dialog" DROP CONSTRAINT "fk_4561d862a02f368d", DROP CONSTRAINT "fk_4561d862b29a9963";');
        $this->addSql('DROP INDEX "friendship_unique_idx";');
        $this->addSql('DROP INDEX "idx_55eeac616a5458e8";');
        $this->addSql('DROP INDEX "idx_55eeac61a76ed395";');
        $this->addSql('ALTER TABLE "friend" DROP CONSTRAINT "fk_55eeac616a5458e8", DROP CONSTRAINT "fk_55eeac61a76ed395";');
        $this->addSql('DROP INDEX "idx_b6bd307f5e46c4e2";');
        $this->addSql('DROP INDEX "idx_b6bd307ff624b39d";');
        $this->addSql('ALTER TABLE "message" DROP CONSTRAINT "fk_b6bd307f5e46c4e2", DROP CONSTRAINT "fk_b6bd307ff624b39d";');
        $this->addSql('DROP INDEX "idx_5a8a6c8da76ed395";');
        $this->addSql('ALTER TABLE "post" DROP CONSTRAINT "fk_5a8a6c8da76ed395";');
        $this->addSql('ALTER TABLE "dialog" DROP CONSTRAINT "dialog_pkey";');
        $this->addSql('ALTER TABLE dialog ADD PRIMARY KEY (participant1_id, id);');
        $this->addSql('ALTER TABLE message ADD COLUMN participant1_id UUID;');
        $this->addSql('UPDATE message m SET participant1_id = d.participant1_id FROM dialog d WHERE m.dialog_id = d.id;');
        $this->addSql('ALTER TABLE message ALTER COLUMN participant1_id SET NOT NULL;');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT fk_message_dialog FOREIGN KEY (dialog_id, participant1_id) REFERENCES dialog (id, participant1_id);');
        $this->addSql('CREATE INDEX idx_message_dialog_composite ON message (dialog_id, participant1_id);');
        $this->addSql('ALTER TABLE "message" DROP CONSTRAINT "message_pkey";');
        $this->addSql('ALTER TABLE "message" ADD PRIMARY KEY ("id", "participant1_id");');
        $this->addSql("SELECT create_distributed_table('dialog', 'participant1_id', 'hash', shard_count => 32);");
        $this->addSql("SELECT create_distributed_table('message', 'participant1_id', 'hash', colocate_with => 'dialog');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message ALTER id TYPE INT');
        $this->addSql('ALTER TABLE message ALTER dialog_id TYPE INT');
        $this->addSql('ALTER TABLE dialog ALTER id TYPE INT');
        $this->addSql('ALTER TABLE "message" DROP CONSTRAINT "fk_message_dialog";');
        $this->addSql('DROP INDEX "idx_message_dialog_composite";');
        $this->addSql('ALTER TABLE "message" DROP CONSTRAINT "message_pkey";');
        $this->addSql('ALTER TABLE "dialog" DROP CONSTRAINT "dialog_pkey";');
        $this->addSql("SELECT undistributed_table('dialog');");
        $this->addSql("SELECT undistributed_table('message');");
        $this->addSql('ALTER TABLE "message" ADD PRIMARY KEY ("id");');
        $this->addSql('ALTER TABLE dialog ADD PRIMARY KEY (id);');
        $this->addSql('CREATE INDEX IDX_4561D862B29A9963 ON dialog (participant1_id)');
        $this->addSql('CREATE INDEX IDX_4561D862A02F368D ON dialog (participant2_id)');
        $this->addSql('ALTER TABLE dialog ADD CONSTRAINT FK_4561D862B29A9963 FOREIGN KEY (participant1_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dialog ADD CONSTRAINT FK_4561D862A02F368D FOREIGN KEY (participant2_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX friendship_unique_idx ON friend (user_id, friend_id)');
        $this->addSql('CREATE INDEX IDX_55EEAC61A76ED395 ON friend (user_id)');
        $this->addSql('CREATE INDEX IDX_55EEAC616A5458E8 ON friend (friend_id)');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC61A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC616A5458E8 FOREIGN KEY (friend_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_B6BD307F5E46C4E2 ON message (dialog_id)');
        $this->addSql('CREATE INDEX IDX_B6BD307FF624B39D ON message (sender_id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F5E46C4E2 FOREIGN KEY (dialog_id) REFERENCES dialog (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FF624B39D FOREIGN KEY (sender_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DA76ED395 ON post (user_id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message DROP COLUMN participant1_id;');
    }
}
