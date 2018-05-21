<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180521131107 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE error (id INT AUTO_INCREMENT NOT NULL, message VARCHAR(1000) NOT NULL, type SMALLINT NOT NULL, is_deleted TINYINT(1) NOT NULL, add_data VARCHAR(1000) NOT NULL, created_at INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE watcher CHANGE status status SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE status status SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE message CHANGE status status SMALLINT NOT NULL');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE error');
        $this->addSql('ALTER TABLE message CHANGE status status TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE status status TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE watcher CHANGE status status TINYINT(1) DEFAULT \'1\' NOT NULL');
    }
}
