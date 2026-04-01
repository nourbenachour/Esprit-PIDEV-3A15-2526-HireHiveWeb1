<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260401150247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificate RENAME INDEX idx_course TO IDX_219CDA4A591CC992');
        $this->addSql('ALTER TABLE claim CHANGE id_claim id_claim BIGINT NOT NULL, CHANGE type type VARCHAR(255) NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE status status VARCHAR(255) NOT NULL, CHANGE priority priority VARCHAR(255) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE resolved_at resolved_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE claim_attachment CHANGE id_attachment id_attachment BIGINT NOT NULL, CHANGE claim_id claim_id BIGINT DEFAULT NULL, CHANGE uploaded_at uploaded_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE claim_attachment RENAME INDEX fk_attachment_claim TO IDX_A423D5077096A49F');
        $this->addSql('ALTER TABLE claim_response CHANGE id_response id_response BIGINT NOT NULL, CHANGE claim_id claim_id BIGINT DEFAULT NULL, CHANGE message message LONGTEXT NOT NULL, CHANGE internal_notes internal_notes LONGTEXT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE claim_response RENAME INDEX fk_response_claim TO IDX_FF8BEDBF7096A49F');
        $this->addSql('DROP INDEX idx_comment_created ON comment');
        $this->addSql('DROP INDEX idx_comment_post ON comment');
        $this->addSql('DROP INDEX idx_comment_user ON comment');
        $this->addSql('ALTER TABLE comment CHANGE id_comment id_comment BIGINT NOT NULL, CHANGE content content LONGTEXT NOT NULL, CHANGE is_edited is_edited TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE image_url image_url VARCHAR(500) NOT NULL');
        $this->addSql('ALTER TABLE comment_reply CHANGE id_reply id_reply BIGINT NOT NULL, CHANGE comment_id comment_id BIGINT DEFAULT NULL, CHANGE id_user id_user BIGINT DEFAULT NULL, CHANGE content content LONGTEXT NOT NULL, CHANGE is_edited is_edited TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE comment_reply RENAME INDEX idx_reply_comment TO IDX_54325E11F8697D13');
        $this->addSql('ALTER TABLE comment_reply RENAME INDEX idx_reply_user TO IDX_54325E116B3CA4B');
        $this->addSql('ALTER TABLE condidat CHANGE id_condidat id_condidat BIGINT NOT NULL, CHANGE user_id user_id BIGINT DEFAULT NULL, CHANGE education education LONGTEXT NOT NULL, CHANGE bio bio LONGTEXT NOT NULL, CHANGE cv cv VARCHAR(500) NOT NULL, CHANGE experience experience LONGTEXT NOT NULL, CHANGE competances competances LONGTEXT NOT NULL, CHANGE formations formations LONGTEXT NOT NULL, CHANGE photo photo VARCHAR(500) NOT NULL');
        $this->addSql('ALTER TABLE condidat RENAME INDEX user_id TO IDX_3A8ACF2CA76ED395');
        $this->addSql('ALTER TABLE contrat ADD id_contrat INT NOT NULL, ADD contract_type VARCHAR(255) NOT NULL, ADD end_date DATE NOT NULL, ADD signed_at DATETIME NOT NULL, ADD candidate_name VARCHAR(255) NOT NULL, ADD company_name VARCHAR(255) NOT NULL, DROP idContrat, DROP contractType, DROP endDate, DROP signedAt, DROP candidateName, DROP companyName, CHANGE salary salary DOUBLE PRECISION NOT NULL, CHANGE status status VARCHAR(255) NOT NULL, CHANGE signature signature LONGTEXT NOT NULL, CHANGE startDate start_date DATE NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_contrat)');
        $this->addSql('DROP INDEX uq_conversation_pair ON conversation_direct');
        $this->addSql('ALTER TABLE conversation_direct CHANGE id_conversation id_conversation BIGINT NOT NULL, CHANGE user_low_id user_low_id BIGINT DEFAULT NULL, CHANGE user_high_id user_high_id BIGINT DEFAULT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE conversation_direct RENAME INDEX idx_conversation_low_user TO IDX_12DBAF8EDF2C1124');
        $this->addSql('ALTER TABLE conversation_direct RENAME INDEX idx_conversation_high_user TO IDX_12DBAF8E1E43F5F7');
        $this->addSql('DROP INDEX idx_recruiter ON course');
        $this->addSql('DROP INDEX idx_difficulty ON course');
        $this->addSql('ALTER TABLE course CHANGE id_course id_course BIGINT NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE difficulty difficulty VARCHAR(255) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE passed_times passed_times INT NOT NULL, CHANGE skills skills VARCHAR(500) NOT NULL, CHANGE image_url image_url VARCHAR(500) NOT NULL, CHANGE pdf_url pdf_url VARCHAR(500) NOT NULL, CHANGE pass_score pass_score INT NOT NULL');
        $this->addSql('DROP INDEX idx_direct_message_receiver_read ON direct_message');
        $this->addSql('DROP INDEX idx_direct_message_conversation_time ON direct_message');
        $this->addSql('ALTER TABLE direct_message CHANGE id_message id_message BIGINT NOT NULL, CHANGE id_conversation id_conversation BIGINT DEFAULT NULL, CHANGE sender_id sender_id BIGINT DEFAULT NULL, CHANGE receiver_id receiver_id BIGINT DEFAULT NULL, CHANGE content content LONGTEXT NOT NULL, CHANGE sent_at sent_at DATETIME NOT NULL, CHANGE is_read is_read TINYINT(1) NOT NULL, CHANGE read_at read_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE direct_message RENAME INDEX sender_id TO IDX_1416AF93F624B39D');
        $this->addSql('ALTER TABLE experience CHANGE id id BIGINT NOT NULL, CHANGE condidat_id condidat_id BIGINT DEFAULT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE company company VARCHAR(255) NOT NULL, CHANGE period period VARCHAR(100) NOT NULL, CHANGE description description LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE experience RENAME INDEX condidat_id TO IDX_590C1031619DB31');
        $this->addSql('DROP INDEX uq_face_email_provider ON face_enrollments');
        $this->addSql('DROP INDEX uq_face_user_provider ON face_enrollments');
        $this->addSql('ALTER TABLE face_enrollments CHANGE id id BIGINT NOT NULL');
        $this->addSql('DROP INDEX uq_feedback_user ON feedbacks');
        $this->addSql('ALTER TABLE feedbacks CHANGE id id BIGINT NOT NULL, CHANGE comment comment LONGTEXT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX idx_candidate ON inscription');
        $this->addSql('ALTER TABLE inscription CHANGE id_inscription id_inscription BIGINT NOT NULL, CHANGE course_id course_id BIGINT DEFAULT NULL, CHANGE inscrit_at inscrit_at DATETIME NOT NULL, CHANGE completed_at completed_at DATETIME NOT NULL, CHANGE certificate_id certificate_id BIGINT NOT NULL, CHANGE badge_id badge_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE inscription RENAME INDEX idx_course TO IDX_5E90F6D6591CC992');
        $this->addSql('DROP INDEX idContract ON interview');
        $this->addSql('ALTER TABLE interview ADD id_interview INT NOT NULL, ADD interview_date DATETIME NOT NULL, ADD request_date DATETIME NOT NULL, ADD decision_date DATETIME NOT NULL, ADD attendance_status VARCHAR(255) NOT NULL, ADD id_contract INT NOT NULL, ADD candidate_name VARCHAR(255) NOT NULL, ADD company_name VARCHAR(255) NOT NULL, ADD heure_debut VARCHAR(255) NOT NULL, ADD heure_fin VARCHAR(255) NOT NULL, DROP idInterview, DROP interviewDate, DROP requestDate, DROP decisionDate, DROP attendanceStatus, DROP idContract, DROP candidateName, DROP companyName, DROP heureDebut, DROP heureFin, CHANGE result result VARCHAR(255) NOT NULL, CHANGE status status VARCHAR(255) NOT NULL, CHANGE meet_link meet_link VARCHAR(1024) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_interview)');
        $this->addSql('ALTER TABLE job_notification DROP FOREIGN KEY job_notification_ibfk_3');
        $this->addSql('ALTER TABLE job_notification DROP FOREIGN KEY job_notification_ibfk_1');
        $this->addSql('ALTER TABLE job_notification DROP FOREIGN KEY job_notification_ibfk_2');
        $this->addSql('ALTER TABLE job_notification CHANGE id_notification id_notification BIGINT NOT NULL, CHANGE candidate_id candidate_id BIGINT DEFAULT NULL, CHANGE job_offer_id job_offer_id BIGINT DEFAULT NULL, CHANGE application_id application_id BIGINT DEFAULT NULL, CHANGE is_read is_read TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE job_notification ADD CONSTRAINT FK_B037E3E591BD8781 FOREIGN KEY (candidate_id) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_notification ADD CONSTRAINT FK_B037E3E53481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id_job_offer) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_notification ADD CONSTRAINT FK_B037E3E53E030ACD FOREIGN KEY (application_id) REFERENCES application (id_condidature) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_notification RENAME INDEX candidate_id TO IDX_B037E3E591BD8781');
        $this->addSql('ALTER TABLE job_notification RENAME INDEX job_offer_id TO IDX_B037E3E53481D195');
        $this->addSql('ALTER TABLE job_notification RENAME INDEX application_id TO IDX_B037E3E53E030ACD');
        $this->addSql('ALTER TABLE job_offer CHANGE id_job_offer id_job_offer BIGINT NOT NULL, CHANGE description description LONGTEXT NOT NULL, CHANGE location location VARCHAR(150) NOT NULL, CHANGE contract_type contract_type VARCHAR(100) NOT NULL, CHANGE status status VARCHAR(50) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE skills skills LONGTEXT NOT NULL, CHANGE soft_skills soft_skills LONGTEXT NOT NULL, CHANGE recruiter_id recruiter_id BIGINT NOT NULL, CHANGE latitude latitude DOUBLE PRECISION NOT NULL, CHANGE longitude longitude DOUBLE PRECISION NOT NULL');
        $this->addSql('DROP INDEX fk_matching_job_offer ON matching_score');
        $this->addSql('DROP INDEX fk_matching_application ON matching_score');
        $this->addSql('ALTER TABLE matching_score CHANGE id_matching id_matching BIGINT NOT NULL, CHANGE niveau_compatibilite niveau_compatibilite INT NOT NULL, CHANGE recommandations recommandations LONGTEXT NOT NULL, CHANGE score_matching score_matching INT NOT NULL');
        $this->addSql('DROP INDEX id_user ON moderation_report');
        $this->addSql('DROP INDEX post_id ON moderation_report');
        $this->addSql('ALTER TABLE moderation_report CHANGE id_report id_report BIGINT NOT NULL, CHANGE comment_id comment_id BIGINT NOT NULL, CHANGE status status VARCHAR(255) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX idx_notif_post ON notification');
        $this->addSql('DROP INDEX idx_notif_recipient ON notification');
        $this->addSql('ALTER TABLE notification CHANGE id_notification id_notification BIGINT NOT NULL, CHANGE type type VARCHAR(255) NOT NULL, CHANGE reaction_type reaction_type VARCHAR(255) NOT NULL, CHANGE comment_preview comment_preview VARCHAR(255) NOT NULL, CHANGE is_read is_read TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX idx_token_hash ON password_reset_tokens');
        $this->addSql('ALTER TABLE password_reset_tokens CHANGE id id BIGINT NOT NULL, CHANGE user_id user_id BIGINT DEFAULT NULL, CHANGE expires_at expires_at DATETIME NOT NULL, CHANGE used_at used_at DATETIME NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE password_reset_tokens RENAME INDEX idx_user_id TO IDX_3967A216A76ED395');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY fk_post_user');
        $this->addSql('DROP INDEX idx_post_visibility ON post');
        $this->addSql('DROP INDEX idx_post_created ON post');
        $this->addSql('ALTER TABLE post CHANGE id_post id_post BIGINT NOT NULL, CHANGE id_user id_user BIGINT DEFAULT NULL, CHANGE visibility visibility VARCHAR(255) NOT NULL, CHANGE type type VARCHAR(255) NOT NULL, CHANGE title title VARCHAR(150) NOT NULL, CHANGE content content LONGTEXT NOT NULL, CHANGE image_url image_url LONGTEXT NOT NULL, CHANGE is_published is_published TINYINT(1) NOT NULL, CHANGE view_count view_count INT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D6B3CA4B FOREIGN KEY (id_user) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post RENAME INDEX idx_post_user TO IDX_5A8A6C8D6B3CA4B');
        $this->addSql('DROP INDEX idx_profile_views_viewer ON profile_views');
        $this->addSql('DROP INDEX idx_profile_views_recruiter ON profile_views');
        $this->addSql('ALTER TABLE profile_views CHANGE id_view id_view BIGINT NOT NULL, CHANGE recruiter_id recruiter_id BIGINT DEFAULT NULL, CHANGE viewer_id viewer_id BIGINT DEFAULT NULL, CHANGE viewed_at viewed_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE questionnaire ADD id_questionnaire INT NOT NULL, ADD question_text LONGTEXT NOT NULL, ADD answer_options LONGTEXT NOT NULL, ADD correct_answer VARCHAR(255) NOT NULL, DROP idQuestionnaire, DROP questionText, DROP answerOptions, DROP correctAnswer, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL, CHANGE jobTitle job_title VARCHAR(255) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id_questionnaire)');
        $this->addSql('DROP INDEX idx_reaction_post ON reaction');
        $this->addSql('DROP INDEX idx_reaction_user ON reaction');
        $this->addSql('DROP INDEX uq_reaction_user_post ON reaction');
        $this->addSql('ALTER TABLE reaction CHANGE id_reaction id_reaction BIGINT NOT NULL, CHANGE reaction_type reaction_type VARCHAR(255) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE recruiter CHANGE id_recruiter id_recruiter BIGINT NOT NULL, CHANGE user_id user_id BIGINT DEFAULT NULL, CHANGE company_id company_id BIGINT NOT NULL, CHANGE company_name company_name VARCHAR(255) NOT NULL, CHANGE company_logo company_logo VARCHAR(500) NOT NULL, CHANGE company_bio company_bio LONGTEXT NOT NULL, CHANGE company_website company_website VARCHAR(255) NOT NULL, CHANGE adresse adresse VARCHAR(150) NOT NULL, CHANGE position position VARCHAR(150) NOT NULL, CHANGE permission permission VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE recruiter RENAME INDEX user_id TO IDX_DE8633D8A76ED395');
        $this->addSql('ALTER TABLE saved_job_offer DROP FOREIGN KEY saved_job_offer_ibfk_1');
        $this->addSql('ALTER TABLE saved_job_offer DROP FOREIGN KEY saved_job_offer_ibfk_2');
        $this->addSql('DROP INDEX unique_save ON saved_job_offer');
        $this->addSql('ALTER TABLE saved_job_offer CHANGE id_saved id_saved BIGINT NOT NULL, CHANGE candidate_id candidate_id BIGINT DEFAULT NULL, CHANGE job_offer_id job_offer_id BIGINT DEFAULT NULL, CHANGE saved_at saved_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE saved_job_offer ADD CONSTRAINT FK_1D5173B391BD8781 FOREIGN KEY (candidate_id) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE saved_job_offer ADD CONSTRAINT FK_1D5173B33481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id_job_offer) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE saved_job_offer RENAME INDEX job_offer_id TO IDX_1D5173B33481D195');
        $this->addSql('DROP INDEX email ON users');
        $this->addSql('DROP INDEX idx_users_search_name_email ON users');
        $this->addSql('ALTER TABLE users CHANGE first_name first_name VARCHAR(100) NOT NULL, CHANGE last_name last_name VARCHAR(100) NOT NULL, CHANGE profile_picture profile_picture VARCHAR(500) NOT NULL, CHANGE phone phone VARCHAR(20) DEFAULT NULL, CHANGE role role VARCHAR(255) NOT NULL, CHANGE is_active is_active TINYINT(1) NOT NULL, CHANGE status status VARCHAR(255) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificate RENAME INDEX idx_219cda4a591cc992 TO idx_course');
        $this->addSql('ALTER TABLE claim CHANGE id_claim id_claim BIGINT AUTO_INCREMENT NOT NULL, CHANGE type type ENUM(\'TECHNICAL\', \'CONTENT\', \'USER_BEHAVIOR\', \'OTHER\', \'PAYMENT\') NOT NULL, CHANGE description description TEXT NOT NULL, CHANGE status status ENUM(\'OPEN\', \'IN_PROGRESS\', \'RESOLVED\', \'CLOSED\') DEFAULT \'\'\'OPEN\'\'\' NOT NULL, CHANGE priority priority ENUM(\'LOW\', \'MEDIUM\', \'HIGH\', \'URGENT\') DEFAULT \'\'\'MEDIUM\'\'\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE resolved_at resolved_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE claim_attachment CHANGE id_attachment id_attachment BIGINT AUTO_INCREMENT NOT NULL, CHANGE uploaded_at uploaded_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE claim_id claim_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE claim_attachment RENAME INDEX idx_a423d5077096a49f TO fk_attachment_claim');
        $this->addSql('ALTER TABLE claim_response CHANGE id_response id_response BIGINT AUTO_INCREMENT NOT NULL, CHANGE message message TEXT NOT NULL, CHANGE internal_notes internal_notes TEXT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE claim_id claim_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE claim_response RENAME INDEX idx_ff8bedbf7096a49f TO fk_response_claim');
        $this->addSql('ALTER TABLE comment CHANGE id_comment id_comment BIGINT AUTO_INCREMENT NOT NULL, CHANGE content content TEXT NOT NULL, CHANGE is_edited is_edited TINYINT(1) DEFAULT 0 NOT NULL, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT \'current_timestamp()\', CHANGE image_url image_url VARCHAR(500) DEFAULT \'NULL\'');
        $this->addSql('CREATE INDEX idx_comment_created ON comment (created_at)');
        $this->addSql('CREATE INDEX idx_comment_post ON comment (post_id)');
        $this->addSql('CREATE INDEX idx_comment_user ON comment (id_user)');
        $this->addSql('ALTER TABLE comment_reply CHANGE id_reply id_reply BIGINT AUTO_INCREMENT NOT NULL, CHANGE content content TEXT NOT NULL, CHANGE is_edited is_edited TINYINT(1) DEFAULT 0, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT \'NULL\', CHANGE comment_id comment_id BIGINT NOT NULL, CHANGE id_user id_user BIGINT NOT NULL');
        $this->addSql('ALTER TABLE comment_reply RENAME INDEX idx_54325e116b3ca4b TO idx_reply_user');
        $this->addSql('ALTER TABLE comment_reply RENAME INDEX idx_54325e11f8697d13 TO idx_reply_comment');
        $this->addSql('ALTER TABLE condidat CHANGE id_condidat id_condidat BIGINT AUTO_INCREMENT NOT NULL, CHANGE education education TEXT DEFAULT NULL, CHANGE bio bio TEXT DEFAULT NULL, CHANGE cv cv VARCHAR(500) DEFAULT \'NULL\', CHANGE experience experience TEXT DEFAULT NULL, CHANGE competances competances TEXT DEFAULT NULL, CHANGE formations formations TEXT DEFAULT NULL, CHANGE photo photo VARCHAR(500) DEFAULT \'NULL\', CHANGE user_id user_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE condidat RENAME INDEX idx_3a8acf2ca76ed395 TO user_id');
        $this->addSql('ALTER TABLE contrat ADD idContrat INT AUTO_INCREMENT NOT NULL, ADD contractType ENUM(\'CDI\', \'FREELANCE\', \'INTERNSHIP\', \'CDD\') NOT NULL, ADD startDate DATE NOT NULL, ADD endDate DATE DEFAULT \'NULL\', ADD signedAt DATETIME DEFAULT \'NULL\', ADD candidateName VARCHAR(255) NOT NULL, ADD companyName VARCHAR(255) NOT NULL, DROP id_contrat, DROP contract_type, DROP start_date, DROP end_date, DROP signed_at, DROP candidate_name, DROP company_name, CHANGE salary salary DOUBLE PRECISION DEFAULT \'NULL\', CHANGE status status ENUM(\'PENDING\', \'SENT\', \'SIGNED\', \'REJECTED\') DEFAULT \'\'\'PENDING\'\'\', CHANGE signature signature LONGTEXT DEFAULT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (idContrat)');
        $this->addSql('ALTER TABLE conversation_direct CHANGE id_conversation id_conversation BIGINT AUTO_INCREMENT NOT NULL, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE user_low_id user_low_id BIGINT NOT NULL, CHANGE user_high_id user_high_id BIGINT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uq_conversation_pair ON conversation_direct (user_low_id, user_high_id)');
        $this->addSql('ALTER TABLE conversation_direct RENAME INDEX idx_12dbaf8edf2c1124 TO idx_conversation_low_user');
        $this->addSql('ALTER TABLE conversation_direct RENAME INDEX idx_12dbaf8e1e43f5f7 TO idx_conversation_high_user');
        $this->addSql('ALTER TABLE course CHANGE id_course id_course BIGINT AUTO_INCREMENT NOT NULL, CHANGE description description TEXT NOT NULL, CHANGE difficulty difficulty ENUM(\'EASY\', \'MEDIUM\', \'HARD\') NOT NULL, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE passed_times passed_times INT DEFAULT 0, CHANGE skills skills VARCHAR(500) DEFAULT \'NULL\', CHANGE image_url image_url VARCHAR(500) DEFAULT \'NULL\', CHANGE pdf_url pdf_url VARCHAR(500) DEFAULT \'NULL\', CHANGE pass_score pass_score INT DEFAULT 80');
        $this->addSql('CREATE INDEX idx_recruiter ON course (recruiter_id)');
        $this->addSql('CREATE INDEX idx_difficulty ON course (difficulty)');
        $this->addSql('ALTER TABLE direct_message CHANGE id_message id_message BIGINT AUTO_INCREMENT NOT NULL, CHANGE content content TEXT NOT NULL, CHANGE sent_at sent_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE is_read is_read TINYINT(1) DEFAULT 0, CHANGE read_at read_at DATETIME DEFAULT \'NULL\', CHANGE id_conversation id_conversation BIGINT NOT NULL, CHANGE sender_id sender_id BIGINT NOT NULL, CHANGE receiver_id receiver_id BIGINT NOT NULL');
        $this->addSql('CREATE INDEX idx_direct_message_receiver_read ON direct_message (receiver_id, is_read)');
        $this->addSql('CREATE INDEX idx_direct_message_conversation_time ON direct_message (id_conversation, sent_at, id_message)');
        $this->addSql('ALTER TABLE direct_message RENAME INDEX idx_1416af93f624b39d TO sender_id');
        $this->addSql('ALTER TABLE experience CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE title title VARCHAR(255) DEFAULT \'NULL\', CHANGE company company VARCHAR(255) DEFAULT \'NULL\', CHANGE period period VARCHAR(100) DEFAULT \'NULL\', CHANGE description description TEXT DEFAULT NULL, CHANGE condidat_id condidat_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE experience RENAME INDEX idx_590c1031619db31 TO condidat_id');
        $this->addSql('ALTER TABLE face_enrollments CHANGE id id BIGINT AUTO_INCREMENT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uq_face_email_provider ON face_enrollments (email, provider)');
        $this->addSql('CREATE UNIQUE INDEX uq_face_user_provider ON face_enrollments (user_id, provider)');
        $this->addSql('ALTER TABLE feedbacks CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE comment comment TEXT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uq_feedback_user ON feedbacks (user_id)');
        $this->addSql('ALTER TABLE inscription CHANGE id_inscription id_inscription BIGINT AUTO_INCREMENT NOT NULL, CHANGE inscrit_at inscrit_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE completed_at completed_at DATETIME DEFAULT \'NULL\', CHANGE certificate_id certificate_id BIGINT DEFAULT NULL, CHANGE badge_id badge_id BIGINT DEFAULT NULL, CHANGE course_id course_id BIGINT NOT NULL');
        $this->addSql('CREATE INDEX idx_candidate ON inscription (condidat_id)');
        $this->addSql('ALTER TABLE inscription RENAME INDEX idx_5e90f6d6591cc992 TO idx_course');
        $this->addSql('ALTER TABLE interview ADD idInterview INT AUTO_INCREMENT NOT NULL, ADD interviewDate DATETIME DEFAULT \'current_timestamp()\' NOT NULL, ADD requestDate DATETIME DEFAULT \'current_timestamp()\' NOT NULL, ADD decisionDate DATETIME DEFAULT \'NULL\', ADD attendanceStatus ENUM(\'PLANNED\', \'COMPLETED\', \'NO_SHOW_CANDIDATE\', \'NO_SHOW_RECRUITER\', \'CANCELLED\') DEFAULT \'\'\'PLANNED\'\'\' NOT NULL, ADD idContract INT DEFAULT NULL, ADD candidateName VARCHAR(255) NOT NULL, ADD companyName VARCHAR(255) NOT NULL, ADD heureDebut TIME NOT NULL, ADD heureFin TIME NOT NULL, DROP id_interview, DROP interview_date, DROP request_date, DROP decision_date, DROP attendance_status, DROP id_contract, DROP candidate_name, DROP company_name, DROP heure_debut, DROP heure_fin, CHANGE result result ENUM(\'SENT\', \'ACCEPTED\', \'REJECTED\') NOT NULL, CHANGE status status ENUM(\'ACCEPTED\', \'REJECTED\', \'PENDING\') NOT NULL, CHANGE meet_link meet_link VARCHAR(1024) DEFAULT \'NULL\', DROP PRIMARY KEY, ADD PRIMARY KEY (idInterview)');
        $this->addSql('CREATE INDEX idContract ON interview (idContract)');
        $this->addSql('ALTER TABLE job_notification DROP FOREIGN KEY FK_B037E3E591BD8781');
        $this->addSql('ALTER TABLE job_notification DROP FOREIGN KEY FK_B037E3E53481D195');
        $this->addSql('ALTER TABLE job_notification DROP FOREIGN KEY FK_B037E3E53E030ACD');
        $this->addSql('ALTER TABLE job_notification CHANGE id_notification id_notification BIGINT AUTO_INCREMENT NOT NULL, CHANGE is_read is_read TINYINT(1) DEFAULT 0, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE candidate_id candidate_id BIGINT NOT NULL, CHANGE job_offer_id job_offer_id BIGINT NOT NULL, CHANGE application_id application_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE job_notification ADD CONSTRAINT job_notification_ibfk_3 FOREIGN KEY (application_id) REFERENCES application (id_condidature)');
        $this->addSql('ALTER TABLE job_notification ADD CONSTRAINT job_notification_ibfk_1 FOREIGN KEY (candidate_id) REFERENCES users (id_user)');
        $this->addSql('ALTER TABLE job_notification ADD CONSTRAINT job_notification_ibfk_2 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id_job_offer)');
        $this->addSql('ALTER TABLE job_notification RENAME INDEX idx_b037e3e53481d195 TO job_offer_id');
        $this->addSql('ALTER TABLE job_notification RENAME INDEX idx_b037e3e53e030acd TO application_id');
        $this->addSql('ALTER TABLE job_notification RENAME INDEX idx_b037e3e591bd8781 TO candidate_id');
        $this->addSql('ALTER TABLE job_offer CHANGE id_job_offer id_job_offer BIGINT AUTO_INCREMENT NOT NULL, CHANGE description description TEXT NOT NULL, CHANGE location location VARCHAR(150) DEFAULT \'NULL\', CHANGE contract_type contract_type VARCHAR(100) DEFAULT \'NULL\', CHANGE status status VARCHAR(50) DEFAULT \'NULL\', CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE skills skills TEXT DEFAULT NULL, CHANGE soft_skills soft_skills TEXT DEFAULT NULL, CHANGE recruiter_id recruiter_id BIGINT DEFAULT NULL, CHANGE latitude latitude NUMERIC(10, 8) NOT NULL, CHANGE longitude longitude NUMERIC(11, 8) NOT NULL');
        $this->addSql('ALTER TABLE matching_score CHANGE id_matching id_matching BIGINT AUTO_INCREMENT NOT NULL, CHANGE niveau_compatibilite niveau_compatibilite INT DEFAULT NULL, CHANGE recommandations recommandations TEXT DEFAULT NULL, CHANGE score_matching score_matching INT DEFAULT NULL');
        $this->addSql('CREATE INDEX fk_matching_job_offer ON matching_score (job_offer_id)');
        $this->addSql('CREATE INDEX fk_matching_application ON matching_score (id_condidature)');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE moderation_report CHANGE id_report id_report BIGINT AUTO_INCREMENT NOT NULL, CHANGE comment_id comment_id BIGINT DEFAULT NULL, CHANGE status status ENUM(\'OPEN\', \'RESOLVED\', \'BANNED\') DEFAULT \'\'\'OPEN\'\'\', CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL');
        $this->addSql('CREATE INDEX id_user ON moderation_report (id_user)');
        $this->addSql('CREATE INDEX post_id ON moderation_report (post_id)');
        $this->addSql('ALTER TABLE notification CHANGE id_notification id_notification BIGINT AUTO_INCREMENT NOT NULL, CHANGE type type ENUM(\'COMMENT\', \'REACTION\', \'MENTION\') NOT NULL, CHANGE reaction_type reaction_type ENUM(\'LOVE\', \'LIKE\', \'DISLIKE\') DEFAULT \'NULL\', CHANGE comment_preview comment_preview VARCHAR(255) DEFAULT \'NULL\', CHANGE is_read is_read TINYINT(1) DEFAULT 0, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL');
        $this->addSql('CREATE INDEX idx_notif_post ON notification (post_id)');
        $this->addSql('CREATE INDEX idx_notif_recipient ON notification (recipient_user_id, is_read, created_at)');
        $this->addSql('ALTER TABLE password_reset_tokens CHANGE id id BIGINT AUTO_INCREMENT NOT NULL, CHANGE expires_at expires_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE used_at used_at DATETIME DEFAULT \'NULL\', CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE user_id user_id BIGINT NOT NULL');
        $this->addSql('CREATE INDEX idx_token_hash ON password_reset_tokens (token_hash)');
        $this->addSql('ALTER TABLE password_reset_tokens RENAME INDEX idx_3967a216a76ed395 TO idx_user_id');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D6B3CA4B');
        $this->addSql('ALTER TABLE post CHANGE id_post id_post BIGINT AUTO_INCREMENT NOT NULL, CHANGE visibility visibility ENUM(\'PUBLIC\', \'CANDIDAT\', \'CANDIDATE\', \'RECRUITER\') NOT NULL, CHANGE type type ENUM(\'IMAGE\', \'TEXT\', \'VIDEO\', \'ARTICLE\') DEFAULT \'\'\'TEXT\'\'\' NOT NULL, CHANGE title title VARCHAR(150) DEFAULT \'NULL\', CHANGE content content TEXT NOT NULL, CHANGE image_url image_url TEXT DEFAULT NULL, CHANGE is_published is_published TINYINT(1) DEFAULT 1 NOT NULL, CHANGE view_count view_count INT DEFAULT 0 NOT NULL, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT \'current_timestamp()\', CHANGE id_user id_user BIGINT NOT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT fk_post_user FOREIGN KEY (id_user) REFERENCES users (id_user) ON UPDATE CASCADE ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_post_visibility ON post (visibility)');
        $this->addSql('CREATE INDEX idx_post_created ON post (created_at)');
        $this->addSql('ALTER TABLE post RENAME INDEX idx_5a8a6c8d6b3ca4b TO idx_post_user');
        $this->addSql('ALTER TABLE profile_views CHANGE id_view id_view BIGINT AUTO_INCREMENT NOT NULL, CHANGE viewed_at viewed_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE recruiter_id recruiter_id BIGINT NOT NULL, CHANGE viewer_id viewer_id BIGINT NOT NULL');
        $this->addSql('CREATE INDEX idx_profile_views_viewer ON profile_views (viewer_id, viewed_at)');
        $this->addSql('CREATE INDEX idx_profile_views_recruiter ON profile_views (recruiter_id, viewed_at)');
        $this->addSql('ALTER TABLE questionnaire ADD idQuestionnaire INT AUTO_INCREMENT NOT NULL, ADD jobTitle VARCHAR(255) NOT NULL, ADD questionText TEXT NOT NULL, ADD answerOptions TEXT DEFAULT NULL, ADD correctAnswer VARCHAR(255) DEFAULT \'NULL\', DROP id_questionnaire, DROP job_title, DROP question_text, DROP answer_options, DROP correct_answer, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (idQuestionnaire)');
        $this->addSql('ALTER TABLE reaction CHANGE id_reaction id_reaction BIGINT AUTO_INCREMENT NOT NULL, CHANGE reaction_type reaction_type ENUM(\'LOVE\', \'LIKE\', \'DISLIKE\') NOT NULL, CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL');
        $this->addSql('CREATE INDEX idx_reaction_post ON reaction (post_id)');
        $this->addSql('CREATE INDEX idx_reaction_user ON reaction (id_user)');
        $this->addSql('CREATE UNIQUE INDEX uq_reaction_user_post ON reaction (id_user, post_id)');
        $this->addSql('ALTER TABLE recruiter CHANGE id_recruiter id_recruiter BIGINT AUTO_INCREMENT NOT NULL, CHANGE company_id company_id BIGINT DEFAULT NULL, CHANGE company_name company_name VARCHAR(255) DEFAULT \'NULL\', CHANGE company_logo company_logo VARCHAR(500) DEFAULT \'NULL\', CHANGE company_bio company_bio TEXT DEFAULT NULL, CHANGE company_website company_website VARCHAR(255) DEFAULT \'NULL\', CHANGE adresse adresse VARCHAR(150) DEFAULT \'NULL\', CHANGE position position VARCHAR(150) DEFAULT \'NULL\', CHANGE permission permission VARCHAR(20) DEFAULT \'NULL\', CHANGE user_id user_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE recruiter RENAME INDEX idx_de8633d8a76ed395 TO user_id');
        $this->addSql('ALTER TABLE saved_job_offer DROP FOREIGN KEY FK_1D5173B391BD8781');
        $this->addSql('ALTER TABLE saved_job_offer DROP FOREIGN KEY FK_1D5173B33481D195');
        $this->addSql('ALTER TABLE saved_job_offer CHANGE id_saved id_saved BIGINT AUTO_INCREMENT NOT NULL, CHANGE saved_at saved_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE candidate_id candidate_id BIGINT NOT NULL, CHANGE job_offer_id job_offer_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE saved_job_offer ADD CONSTRAINT saved_job_offer_ibfk_1 FOREIGN KEY (candidate_id) REFERENCES users (id_user)');
        $this->addSql('ALTER TABLE saved_job_offer ADD CONSTRAINT saved_job_offer_ibfk_2 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id_job_offer)');
        $this->addSql('CREATE UNIQUE INDEX unique_save ON saved_job_offer (candidate_id, job_offer_id)');
        $this->addSql('ALTER TABLE saved_job_offer RENAME INDEX idx_1d5173b33481d195 TO job_offer_id');
        $this->addSql('ALTER TABLE users CHANGE first_name first_name VARCHAR(100) DEFAULT \'NULL\', CHANGE last_name last_name VARCHAR(100) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(500) DEFAULT \'NULL\', CHANGE phone phone VARCHAR(20) DEFAULT \'NULL\', CHANGE role role ENUM(\'CANDIDATE\', \'RECRUITER\', \'ADMIN\') NOT NULL, CHANGE is_active is_active TINYINT(1) DEFAULT 1, CHANGE status status ENUM(\'USER_VERIFIED\', \'USER_NOT_VERIFIED\', \'BLOCKED\', \'USER_BANNED\') DEFAULT \'\'\'USER_VERIFIED\'\'\', CHANGE created_at created_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT \'current_timestamp()\' NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX email ON users (email)');
        $this->addSql('CREATE INDEX idx_users_search_name_email ON users (first_name, last_name, email)');
    }
}
