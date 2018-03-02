<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180302204141 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user123 (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(60) NOT NULL, username VARCHAR(30) NOT NULL, nickname VARCHAR(30) NOT NULL, status TINYINT DEFAULT 1 NOT NULL, created_at DATETIME NOT NULL, roles JSON NOT NULL, password VARCHAR(64) NOT NULL, UNIQUE INDEX UNIQ_49677577E7927C74 (email), UNIQUE INDEX UNIQ_49677577F85E0677 (username), UNIQUE INDEX UNIQ_49677577A188FE64 (nickname), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user123');
    }
}
