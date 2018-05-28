<?php declare(strict_types = 1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180528145417 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE host (id INT AUTO_INCREMENT NOT NULL, host VARCHAR(255) NOT NULL, is_deleted TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_CF2713FDCF2713FD (host), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, host_id INT DEFAULT NULL, url VARCHAR(500) NOT NULL, current_price NUMERIC(10, 2) NOT NULL, last_tracked_date INT NOT NULL, status SMALLINT NOT NULL, UNIQUE INDEX UNIQ_D34A04ADF47645AE (url), INDEX IDX_D34A04AD1FB8D185 (host_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, user_id INT NOT NULL, type INT NOT NULL, entity_id INT NOT NULL, is_deleted TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE price_tracker (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, date INT NOT NULL, INDEX IDX_C745E2F34584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE watcher (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, product_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, start_price NUMERIC(10, 2) NOT NULL, percent INT NOT NULL, is_deleted TINYINT(1) NOT NULL, created_at INT NOT NULL, success_date INT NOT NULL, status SMALLINT NOT NULL, INDEX IDX_B28FE6ACA76ED395 (user_id), INDEX IDX_B28FE6AC4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE error (id INT AUTO_INCREMENT NOT NULL, message VARCHAR(1000) NOT NULL, type SMALLINT NOT NULL, is_deleted TINYINT(1) NOT NULL, add_data VARCHAR(1000) NOT NULL, created_at INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(60) NOT NULL, nick_name VARCHAR(30) NOT NULL, status SMALLINT NOT NULL, created_at DATETIME NOT NULL, avatar VARCHAR(30) NOT NULL, roles JSON NOT NULL, password VARCHAR(64) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649A045A5E9 (nick_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, message VARCHAR(500) NOT NULL, title VARCHAR(100) NOT NULL, from_user VARCHAR(50) NOT NULL, status SMALLINT NOT NULL, add_data VARCHAR(500) NOT NULL, type SMALLINT NOT NULL, is_deleted TINYINT(1) NOT NULL, created_at INT NOT NULL, INDEX IDX_B6BD307FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD1FB8D185 FOREIGN KEY (host_id) REFERENCES host (id)');
        $this->addSql('ALTER TABLE price_tracker ADD CONSTRAINT FK_C745E2F34584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE watcher ADD CONSTRAINT FK_B28FE6ACA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE watcher ADD CONSTRAINT FK_B28FE6AC4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD1FB8D185');
        $this->addSql('ALTER TABLE price_tracker DROP FOREIGN KEY FK_C745E2F34584665A');
        $this->addSql('ALTER TABLE watcher DROP FOREIGN KEY FK_B28FE6AC4584665A');
        $this->addSql('ALTER TABLE watcher DROP FOREIGN KEY FK_B28FE6ACA76ED395');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FA76ED395');
        $this->addSql('DROP TABLE host');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE price_tracker');
        $this->addSql('DROP TABLE watcher');
        $this->addSql('DROP TABLE error');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE message');
    }
}
