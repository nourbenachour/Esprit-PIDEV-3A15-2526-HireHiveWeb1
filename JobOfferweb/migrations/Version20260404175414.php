<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260404175414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE application (id_condidature BIGINT NOT NULL, condidat_id BIGINT NOT NULL, job_offer_id BIGINT NOT NULL, cv_file_path VARCHAR(255) NOT NULL, application_date DATETIME NOT NULL, last_update DATETIME NOT NULL, status VARCHAR(255) NOT NULL, lettre VARCHAR(2000) NOT NULL, PRIMARY KEY(id_condidature)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE badge (id_badge BIGINT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, icon_url VARCHAR(500) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id_badge)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE certificate (id_certificate BIGINT NOT NULL, issued_at DATETIME NOT NULL, condidat_id BIGINT NOT NULL, shareable_link VARCHAR(500) NOT NULL, certified_url VARCHAR(500) NOT NULL, course_id BIGINT DEFAULT NULL, INDEX IDX_219CDA4A591CC992 (course_id), PRIMARY KEY(id_certificate)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE claim (id_claim BIGINT NOT NULL, user_id BIGINT NOT NULL, type VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, status VARCHAR(255) NOT NULL, priority VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, resolved_at DATETIME NOT NULL, PRIMARY KEY(id_claim)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE claim_attachment (id_attachment BIGINT NOT NULL, file_path VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, uploaded_at DATETIME NOT NULL, claim_id BIGINT DEFAULT NULL, INDEX IDX_A423D5077096A49F (claim_id), PRIMARY KEY(id_attachment)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE claim_response (id_response BIGINT NOT NULL, responder_id BIGINT NOT NULL, message LONGTEXT NOT NULL, internal_notes LONGTEXT NOT NULL, created_at DATETIME NOT NULL, claim_id BIGINT DEFAULT NULL, INDEX IDX_FF8BEDBF7096A49F (claim_id), PRIMARY KEY(id_response)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE comment (id_comment BIGINT NOT NULL, id_user BIGINT NOT NULL, post_id BIGINT NOT NULL, content LONGTEXT NOT NULL, is_edited TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, image_url VARCHAR(500) NOT NULL, PRIMARY KEY(id_comment)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE comment_reply (id_reply BIGINT NOT NULL, content LONGTEXT NOT NULL, is_edited TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, comment_id BIGINT DEFAULT NULL, id_user BIGINT DEFAULT NULL, INDEX IDX_54325E11F8697D13 (comment_id), INDEX IDX_54325E116B3CA4B (id_user), PRIMARY KEY(id_reply)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE condidat (id_condidat BIGINT NOT NULL, education LONGTEXT NOT NULL, bio LONGTEXT NOT NULL, cv VARCHAR(500) NOT NULL, experience LONGTEXT NOT NULL, competances LONGTEXT NOT NULL, formations LONGTEXT NOT NULL, photo VARCHAR(500) NOT NULL, user_id BIGINT DEFAULT NULL, INDEX IDX_3A8ACF2CA76ED395 (user_id), PRIMARY KEY(id_condidat)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contrat (id_contrat INT NOT NULL, salary DOUBLE PRECISION NOT NULL, contract_type VARCHAR(255) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, status VARCHAR(255) NOT NULL, signed_at DATETIME NOT NULL, signature LONGTEXT NOT NULL, candidate_name VARCHAR(255) NOT NULL, company_name VARCHAR(255) NOT NULL, PRIMARY KEY(id_contrat)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE conversation_direct (id_conversation BIGINT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_low_id BIGINT DEFAULT NULL, user_high_id BIGINT DEFAULT NULL, INDEX IDX_12DBAF8EDF2C1124 (user_low_id), INDEX IDX_12DBAF8E1E43F5F7 (user_high_id), PRIMARY KEY(id_conversation)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE course (id_course BIGINT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, difficulty VARCHAR(255) NOT NULL, recruiter_id BIGINT NOT NULL, created_at DATETIME NOT NULL, passed_times INT NOT NULL, skills VARCHAR(500) NOT NULL, image_url VARCHAR(500) NOT NULL, pdf_url VARCHAR(500) NOT NULL, pass_score INT NOT NULL, PRIMARY KEY(id_course)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE direct_message (id_message BIGINT NOT NULL, content LONGTEXT NOT NULL, sent_at DATETIME NOT NULL, is_read TINYINT(1) NOT NULL, read_at DATETIME NOT NULL, id_conversation BIGINT DEFAULT NULL, sender_id BIGINT DEFAULT NULL, receiver_id BIGINT DEFAULT NULL, INDEX IDX_1416AF93A94F539B (id_conversation), INDEX IDX_1416AF93F624B39D (sender_id), INDEX IDX_1416AF93CD53EDB6 (receiver_id), PRIMARY KEY(id_message)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE experience (id BIGINT NOT NULL, title VARCHAR(255) NOT NULL, company VARCHAR(255) NOT NULL, period VARCHAR(100) NOT NULL, description LONGTEXT NOT NULL, condidat_id BIGINT DEFAULT NULL, INDEX IDX_590C1031619DB31 (condidat_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE face_enrollments (id BIGINT NOT NULL, user_id BIGINT NOT NULL, email VARCHAR(255) NOT NULL, provider VARCHAR(64) NOT NULL, person_id VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE feedbacks (id BIGINT NOT NULL, user_id BIGINT NOT NULL, rating INT NOT NULL, comment LONGTEXT NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE inscription (id_inscription BIGINT NOT NULL, condidat_id BIGINT NOT NULL, inscrit_at DATETIME NOT NULL, completed_at DATETIME NOT NULL, certificate_id BIGINT NOT NULL, badge_id BIGINT NOT NULL, course_id BIGINT DEFAULT NULL, INDEX IDX_5E90F6D6591CC992 (course_id), PRIMARY KEY(id_inscription)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE interview (id_interview INT NOT NULL, interview_date DATETIME NOT NULL, result VARCHAR(255) NOT NULL, request_date DATETIME NOT NULL, decision_date DATETIME NOT NULL, status VARCHAR(255) NOT NULL, attendance_status VARCHAR(255) NOT NULL, id_contract INT NOT NULL, candidate_name VARCHAR(255) NOT NULL, company_name VARCHAR(255) NOT NULL, heure_debut VARCHAR(255) NOT NULL, heure_fin VARCHAR(255) NOT NULL, meet_link VARCHAR(1024) NOT NULL, PRIMARY KEY(id_interview)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE job_notification (id_notification BIGINT NOT NULL, message VARCHAR(500) NOT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, candidate_id BIGINT DEFAULT NULL, job_offer_id BIGINT DEFAULT NULL, application_id BIGINT DEFAULT NULL, INDEX IDX_B037E3E591BD8781 (candidate_id), INDEX IDX_B037E3E53481D195 (job_offer_id), INDEX IDX_B037E3E53E030ACD (application_id), PRIMARY KEY(id_notification)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE job_offer (id_job_offer BIGINT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, location VARCHAR(150) NOT NULL, contract_type VARCHAR(100) NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, skills LONGTEXT NOT NULL, soft_skills LONGTEXT NOT NULL, recruiter_id BIGINT NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, PRIMARY KEY(id_job_offer)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE matching_score (id_matching BIGINT NOT NULL, job_offer_id BIGINT NOT NULL, condidat_id BIGINT NOT NULL, id_condidature BIGINT NOT NULL, niveau_compatibilite INT NOT NULL, recommandations LONGTEXT NOT NULL, score_matching INT NOT NULL, PRIMARY KEY(id_matching)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE moderation_report (id_report BIGINT NOT NULL, id_user BIGINT NOT NULL, post_id BIGINT NOT NULL, comment_id BIGINT NOT NULL, comment_text VARCHAR(500) NOT NULL, toxicity_score DOUBLE PRECISION NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id_report)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notification (id_notification BIGINT NOT NULL, recipient_user_id BIGINT NOT NULL, actor_user_id BIGINT NOT NULL, post_id BIGINT NOT NULL, type VARCHAR(255) NOT NULL, reaction_type VARCHAR(255) NOT NULL, comment_preview VARCHAR(255) NOT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id_notification)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE password_reset_tokens (id BIGINT NOT NULL, token_hash VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL, used_at DATETIME NOT NULL, created_at DATETIME NOT NULL, user_id BIGINT DEFAULT NULL, INDEX IDX_3967A216A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE post (id_post BIGINT NOT NULL, visibility VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, title VARCHAR(150) NOT NULL, content LONGTEXT NOT NULL, image_url LONGTEXT NOT NULL, is_published TINYINT(1) NOT NULL, view_count INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, id_user BIGINT DEFAULT NULL, INDEX IDX_5A8A6C8D6B3CA4B (id_user), PRIMARY KEY(id_post)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE profile_views (id_view BIGINT NOT NULL, viewed_at DATETIME NOT NULL, recruiter_id BIGINT DEFAULT NULL, viewer_id BIGINT DEFAULT NULL, INDEX IDX_E6240355156BE243 (recruiter_id), INDEX IDX_E62403556C59C752 (viewer_id), PRIMARY KEY(id_view)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE questionnaire (id_questionnaire INT NOT NULL, job_title VARCHAR(255) NOT NULL, question_text LONGTEXT NOT NULL, answer_options LONGTEXT NOT NULL, correct_answer VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id_questionnaire)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reaction (id_reaction BIGINT NOT NULL, id_user BIGINT NOT NULL, post_id BIGINT NOT NULL, reaction_type VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id_reaction)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE recruiter (id_recruiter BIGINT NOT NULL, company_id BIGINT NOT NULL, company_name VARCHAR(255) NOT NULL, company_logo VARCHAR(500) NOT NULL, company_bio LONGTEXT NOT NULL, company_website VARCHAR(255) NOT NULL, secteur_activite VARCHAR(150) NOT NULL, email_contact_entreprise VARCHAR(150) NOT NULL, telephone_service_client VARCHAR(20) NOT NULL, salaires INT NOT NULL, adresse VARCHAR(150) NOT NULL, position VARCHAR(150) NOT NULL, permission VARCHAR(20) NOT NULL, user_id BIGINT DEFAULT NULL, INDEX IDX_DE8633D8A76ED395 (user_id), PRIMARY KEY(id_recruiter)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE saved_job_offer (id_saved BIGINT NOT NULL, saved_at DATETIME NOT NULL, candidate_id BIGINT DEFAULT NULL, job_offer_id BIGINT DEFAULT NULL, INDEX IDX_1D5173B391BD8781 (candidate_id), INDEX IDX_1D5173B33481D195 (job_offer_id), PRIMARY KEY(id_saved)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id_user BIGINT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, profile_picture VARCHAR(500) NOT NULL, phone VARCHAR(20) DEFAULT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, status VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id_user)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE certificate ADD CONSTRAINT FK_219CDA4A591CC992 FOREIGN KEY (course_id) REFERENCES course (id_course) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE claim_attachment ADD CONSTRAINT FK_A423D5077096A49F FOREIGN KEY (claim_id) REFERENCES claim (id_claim) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE claim_response ADD CONSTRAINT FK_FF8BEDBF7096A49F FOREIGN KEY (claim_id) REFERENCES claim (id_claim) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment_reply ADD CONSTRAINT FK_54325E11F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id_comment) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment_reply ADD CONSTRAINT FK_54325E116B3CA4B FOREIGN KEY (id_user) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE condidat ADD CONSTRAINT FK_3A8ACF2CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation_direct ADD CONSTRAINT FK_12DBAF8EDF2C1124 FOREIGN KEY (user_low_id) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE conversation_direct ADD CONSTRAINT FK_12DBAF8E1E43F5F7 FOREIGN KEY (user_high_id) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE direct_message ADD CONSTRAINT FK_1416AF93A94F539B FOREIGN KEY (id_conversation) REFERENCES conversation_direct (id_conversation) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE direct_message ADD CONSTRAINT FK_1416AF93F624B39D FOREIGN KEY (sender_id) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE direct_message ADD CONSTRAINT FK_1416AF93CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE experience ADD CONSTRAINT FK_590C1031619DB31 FOREIGN KEY (condidat_id) REFERENCES condidat (id_condidat) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inscription ADD CONSTRAINT FK_5E90F6D6591CC992 FOREIGN KEY (course_id) REFERENCES course (id_course) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_notification ADD CONSTRAINT FK_B037E3E591BD8781 FOREIGN KEY (candidate_id) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_notification ADD CONSTRAINT FK_B037E3E53481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id_job_offer) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE job_notification ADD CONSTRAINT FK_B037E3E53E030ACD FOREIGN KEY (application_id) REFERENCES application (id_condidature) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE password_reset_tokens ADD CONSTRAINT FK_3967A216A76ED395 FOREIGN KEY (user_id) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D6B3CA4B FOREIGN KEY (id_user) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile_views ADD CONSTRAINT FK_E6240355156BE243 FOREIGN KEY (recruiter_id) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE profile_views ADD CONSTRAINT FK_E62403556C59C752 FOREIGN KEY (viewer_id) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recruiter ADD CONSTRAINT FK_DE8633D8A76ED395 FOREIGN KEY (user_id) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE saved_job_offer ADD CONSTRAINT FK_1D5173B391BD8781 FOREIGN KEY (candidate_id) REFERENCES users (id_user) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE saved_job_offer ADD CONSTRAINT FK_1D5173B33481D195 FOREIGN KEY (job_offer_id) REFERENCES job_offer (id_job_offer) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE certificate DROP FOREIGN KEY FK_219CDA4A591CC992');
        $this->addSql('ALTER TABLE claim_attachment DROP FOREIGN KEY FK_A423D5077096A49F');
        $this->addSql('ALTER TABLE claim_response DROP FOREIGN KEY FK_FF8BEDBF7096A49F');
        $this->addSql('ALTER TABLE comment_reply DROP FOREIGN KEY FK_54325E11F8697D13');
        $this->addSql('ALTER TABLE comment_reply DROP FOREIGN KEY FK_54325E116B3CA4B');
        $this->addSql('ALTER TABLE condidat DROP FOREIGN KEY FK_3A8ACF2CA76ED395');
        $this->addSql('ALTER TABLE conversation_direct DROP FOREIGN KEY FK_12DBAF8EDF2C1124');
        $this->addSql('ALTER TABLE conversation_direct DROP FOREIGN KEY FK_12DBAF8E1E43F5F7');
        $this->addSql('ALTER TABLE direct_message DROP FOREIGN KEY FK_1416AF93A94F539B');
        $this->addSql('ALTER TABLE direct_message DROP FOREIGN KEY FK_1416AF93F624B39D');
        $this->addSql('ALTER TABLE direct_message DROP FOREIGN KEY FK_1416AF93CD53EDB6');
        $this->addSql('ALTER TABLE experience DROP FOREIGN KEY FK_590C1031619DB31');
        $this->addSql('ALTER TABLE inscription DROP FOREIGN KEY FK_5E90F6D6591CC992');
        $this->addSql('ALTER TABLE job_notification DROP FOREIGN KEY FK_B037E3E591BD8781');
        $this->addSql('ALTER TABLE job_notification DROP FOREIGN KEY FK_B037E3E53481D195');
        $this->addSql('ALTER TABLE job_notification DROP FOREIGN KEY FK_B037E3E53E030ACD');
        $this->addSql('ALTER TABLE password_reset_tokens DROP FOREIGN KEY FK_3967A216A76ED395');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D6B3CA4B');
        $this->addSql('ALTER TABLE profile_views DROP FOREIGN KEY FK_E6240355156BE243');
        $this->addSql('ALTER TABLE profile_views DROP FOREIGN KEY FK_E62403556C59C752');
        $this->addSql('ALTER TABLE recruiter DROP FOREIGN KEY FK_DE8633D8A76ED395');
        $this->addSql('ALTER TABLE saved_job_offer DROP FOREIGN KEY FK_1D5173B391BD8781');
        $this->addSql('ALTER TABLE saved_job_offer DROP FOREIGN KEY FK_1D5173B33481D195');
        $this->addSql('DROP TABLE application');
        $this->addSql('DROP TABLE badge');
        $this->addSql('DROP TABLE certificate');
        $this->addSql('DROP TABLE claim');
        $this->addSql('DROP TABLE claim_attachment');
        $this->addSql('DROP TABLE claim_response');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE comment_reply');
        $this->addSql('DROP TABLE condidat');
        $this->addSql('DROP TABLE contrat');
        $this->addSql('DROP TABLE conversation_direct');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE direct_message');
        $this->addSql('DROP TABLE experience');
        $this->addSql('DROP TABLE face_enrollments');
        $this->addSql('DROP TABLE feedbacks');
        $this->addSql('DROP TABLE inscription');
        $this->addSql('DROP TABLE interview');
        $this->addSql('DROP TABLE job_notification');
        $this->addSql('DROP TABLE job_offer');
        $this->addSql('DROP TABLE matching_score');
        $this->addSql('DROP TABLE moderation_report');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE password_reset_tokens');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE profile_views');
        $this->addSql('DROP TABLE questionnaire');
        $this->addSql('DROP TABLE reaction');
        $this->addSql('DROP TABLE recruiter');
        $this->addSql('DROP TABLE saved_job_offer');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
