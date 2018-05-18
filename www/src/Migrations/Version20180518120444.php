<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180518120444 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE watcher ADD is_deleted TINYINT(1) NOT NULL, CHANGE status status TINYINT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE message ADD is_deleted TINYINT(1) NOT NULL, CHANGE status status TINYINT DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE message DROP is_deleted, CHANGE status status TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE watcher DROP is_deleted, CHANGE status status TINYINT(1) DEFAULT \'1\' NOT NULL');
    }
}
