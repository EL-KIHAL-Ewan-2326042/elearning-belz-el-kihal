<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Consolidated initial migration — full schema from scratch.
 * Replaces all previous incremental migrations.
 */
final class Version20260210120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema: user (STI), course, video, document, quiz, question, answer, quiz_attempt';
    }

    public function up(Schema $schema): void
    {
        // User table (Single Table Inheritance: user, teacher, student)
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, user_type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');

        // Course
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, summary LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, thumbnail VARCHAR(255) DEFAULT NULL, teacher_id INT DEFAULT NULL, INDEX IDX_169E6FB941807E1D (teacher_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB941807E1D FOREIGN KEY (teacher_id) REFERENCES user (id)');

        // Video
        $this->addSql('CREATE TABLE video (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, transcription LONGTEXT DEFAULT NULL, file_name VARCHAR(255) DEFAULT NULL, duration INT DEFAULT NULL, uploaded_at DATETIME DEFAULT NULL, transcription_updated_at DATETIME DEFAULT NULL, course_id INT NOT NULL, INDEX IDX_7CC7DA2C591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT FK_7CC7DA2C591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');

        // Document
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, file_name VARCHAR(255) DEFAULT NULL, uploaded_at DATETIME DEFAULT NULL, course_id INT NOT NULL, INDEX IDX_D8698A76591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');

        // Quiz
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, is_generated_by_ai TINYINT(1) NOT NULL, updated_at DATETIME DEFAULT NULL, course_id INT NOT NULL, INDEX IDX_A412FA92591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');

        // Question
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, order_number INT NOT NULL, points INT NOT NULL, quiz_id INT NOT NULL, INDEX IDX_B6F7494E853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');

        // Answer
        $this->addSql('CREATE TABLE answer (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, is_correct TINYINT(1) NOT NULL, question_id INT NOT NULL, INDEX IDX_DADD4A251E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');

        // Quiz Attempt
        $this->addSql('CREATE TABLE quiz_attempt (id INT AUTO_INCREMENT NOT NULL, score INT NOT NULL, max_score INT NOT NULL, answers JSON NOT NULL, submitted_at DATETIME NOT NULL, time_spent_seconds INT NOT NULL, student_id INT NOT NULL, quiz_id INT NOT NULL, INDEX IDX_AB6AFC6CB944F1A (student_id), INDEX IDX_AB6AFC6853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE quiz_attempt ADD CONSTRAINT FK_AB6AFC6CB944F1A FOREIGN KEY (student_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE quiz_attempt ADD CONSTRAINT FK_AB6AFC6853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quiz_attempt DROP FOREIGN KEY FK_AB6AFC6CB944F1A');
        $this->addSql('ALTER TABLE quiz_attempt DROP FOREIGN KEY FK_AB6AFC6853CD175');
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A251E27F6BF');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92591CC992');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A76591CC992');
        $this->addSql('ALTER TABLE video DROP FOREIGN KEY FK_7CC7DA2C591CC992');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB941807E1D');
        $this->addSql('DROP TABLE quiz_attempt');
        $this->addSql('DROP TABLE answer');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE video');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE user');
    }
}
