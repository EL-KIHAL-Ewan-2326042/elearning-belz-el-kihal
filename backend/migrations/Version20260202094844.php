<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260202094844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, is_correct TINYINT NOT NULL, question_id INT NOT NULL, INDEX IDX_DADD4A251E27F6BF (question_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, file_name VARCHAR(255) DEFAULT NULL, uploaded_at DATETIME DEFAULT NULL, course_id INT NOT NULL, INDEX IDX_D8698A76591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, order_number INT NOT NULL, points INT NOT NULL, quiz_id INT NOT NULL, INDEX IDX_B6F7494E853CD175 (quiz_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, is_generated_by_ai TINYINT NOT NULL, course_id INT NOT NULL, INDEX IDX_A412FA92591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE quiz_attempt (id INT AUTO_INCREMENT NOT NULL, score INT NOT NULL, max_score INT NOT NULL, answers JSON NOT NULL, submitted_at DATETIME NOT NULL, time_spent_seconds INT NOT NULL, student_id INT NOT NULL, quiz_id INT NOT NULL, INDEX IDX_AB6AFC6CB944F1A (student_id), INDEX IDX_AB6AFC6853CD175 (quiz_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE quiz_attempt ADD CONSTRAINT FK_AB6AFC6CB944F1A FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quiz_attempt ADD CONSTRAINT FK_AB6AFC6853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE course ADD created_at DATETIME DEFAULT NULL, ADD thumbnail VARCHAR(255) DEFAULT NULL, ADD teacher_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB941807E1D FOREIGN KEY (teacher_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_169E6FB941807E1D ON course (teacher_id)');
        $this->addSql('ALTER TABLE user ADD user_type VARCHAR(255) NOT NULL, ADD specialty VARCHAR(255) DEFAULT NULL, ADD biography LONGTEXT DEFAULT NULL, ADD student_number VARCHAR(100) DEFAULT NULL, ADD enrollment_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE video DROP FOREIGN KEY `FK_7CC7DA2CCDF80196`');
        $this->addSql('DROP INDEX IDX_7CC7DA2CCDF80196 ON video');
        $this->addSql('ALTER TABLE video ADD file_name VARCHAR(255) DEFAULT NULL, ADD duration INT DEFAULT NULL, ADD uploaded_at DATETIME DEFAULT NULL, CHANGE url url VARCHAR(255) DEFAULT NULL, CHANGE lesson_id course_id INT NOT NULL');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT FK_7CC7DA2C591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('CREATE INDEX IDX_7CC7DA2C591CC992 ON video (course_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A251E27F6BF');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76591CC992');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92591CC992');
        $this->addSql('ALTER TABLE quiz_attempt DROP FOREIGN KEY FK_AB6AFC6CB944F1A');
        $this->addSql('ALTER TABLE quiz_attempt DROP FOREIGN KEY FK_AB6AFC6853CD175');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE quiz_attempt');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB941807E1D');
        $this->addSql('DROP INDEX IDX_169E6FB941807E1D ON course');
        $this->addSql('ALTER TABLE course DROP created_at, DROP thumbnail, DROP teacher_id');
        $this->addSql('ALTER TABLE user DROP user_type, DROP specialty, DROP biography, DROP student_number, DROP enrollment_date');
        $this->addSql('ALTER TABLE video DROP FOREIGN KEY FK_7CC7DA2C591CC992');
        $this->addSql('DROP INDEX IDX_7CC7DA2C591CC992 ON video');
        $this->addSql('ALTER TABLE video DROP file_name, DROP duration, DROP uploaded_at, CHANGE url url VARCHAR(255) NOT NULL, CHANGE course_id lesson_id INT NOT NULL');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT `FK_7CC7DA2CCDF80196` FOREIGN KEY (lesson_id) REFERENCES lesson (id)');
        $this->addSql('CREATE INDEX IDX_7CC7DA2CCDF80196 ON video (lesson_id)');
    }
}
