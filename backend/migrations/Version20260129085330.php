<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260129085330 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, summary LONGTEXT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE lesson (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT DEFAULT NULL, number INT NOT NULL, course_id INT NOT NULL, INDEX IDX_F87474F3591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE video (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, lesson_id INT NOT NULL, INDEX IDX_7CC7DA2CCDF80196 (lesson_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE lesson ADD CONSTRAINT FK_F87474F3591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT FK_7CC7DA2CCDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lesson DROP FOREIGN KEY FK_F87474F3591CC992');
        $this->addSql('ALTER TABLE video DROP FOREIGN KEY FK_7CC7DA2CCDF80196');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE lesson');
        $this->addSql('DROP TABLE video');
    }
}
