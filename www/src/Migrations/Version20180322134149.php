<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180322134149 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE watcher CHANGE status status TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE user ADD avatar VARCHAR(30) NOT NULL, CHANGE status status TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE message CHANGE status status TINYINT DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE message CHANGE status status TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE user DROP avatar, CHANGE status status TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE watcher CHANGE status status TINYINT(1) DEFAULT \'1\' NOT NULL');
    }
}
