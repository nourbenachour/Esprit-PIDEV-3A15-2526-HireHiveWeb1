<?php

namespace App\Service\interview;

use App\Entity\Questionnaire;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service de gestion de la logique métier pour Questionnaire.
 */
class QuestionnaireService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Crée un nouveau Questionnaire avec les défauts.
     */
    public function createQuestionnaire(
        string $jobTitle,
        string $questionText,
        string $answerOptions = '',
        string $correctAnswer = ''
    ): Questionnaire {
        $questionnaire = new Questionnaire();
        $questionnaire->setJobTitle($jobTitle);
        $questionnaire->setQuestionText($questionText);
        $questionnaire->setAnswerOptions($answerOptions);
        $questionnaire->setCorrectAnswer($correctAnswer);
        $questionnaire->setCreated_at(new \DateTime());
        $questionnaire->setUpdated_at(new \DateTime());

        return $questionnaire;
    }

    /**
     * Valide que le texte de question n'est pas vide.
     * 
     * @throws \InvalidArgumentException
     */
    public function validateQuestionText(string $text): void
    {
        if (empty(trim($text))) {
            throw new \InvalidArgumentException('Le texte de la question ne peut pas être vide');
        }
    }

    /**
     * Valide que le jobTitle n'est pas vide.
     * 
     * @throws \InvalidArgumentException
     */
    public function validateJobTitle(string $jobTitle): void
    {
        if (empty(trim($jobTitle))) {
            throw new \InvalidArgumentException('Le jobTitle ne peut pas être vide');
        }
    }

    /**
     * Valide les answerOptions si fournis (supposé être JSON).
     * 
     * @throws \InvalidArgumentException
     */
    public function validateAnswerOptions(?string $options): void
    {
        if (!empty($options)) {
            $decoded = json_decode($options, true);
            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Format answerOptions invalide (JSON attendu)');
            }
        }
    }

    /**
     * Met à jour le timestamp updated_at.
     */
    public function updateTimestamp(Questionnaire $questionnaire): void
    {
        $questionnaire->setUpdated_at(new \DateTime());
        $this->em->flush();
    }

    /**
     * Récupère les réponses possibles en tant que tableau.
     * Retorna un array ou null si vide.
     */
    public function getAnswerOptionsAsArray(Questionnaire $questionnaire): ?array
    {
        $options = $questionnaire->getAnswerOptions();
        if (empty($options)) {
            return null;
        }
        return json_decode($options, true);
    }

    /**
     * Convertit un tableau d'options en JSON.
     */
    public function setAnswerOptionsFromArray(Questionnaire $questionnaire, array $options): void
    {
        $questionnaire->setAnswerOptions(json_encode($options, JSON_UNESCAPED_UNICODE));
    }
}
