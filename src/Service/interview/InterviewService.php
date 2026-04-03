<?php

namespace App\Service\interview;

use App\Entity\Interview;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service de gestion de la logique métier pour Interview.
 */
class InterviewService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Statuts valides pour une Interview.
     */
    public const VALID_STATUSES = ['PENDING', 'ACCEPTED', 'REJECTED', 'CANCELLED'];
    
    /**
     * Résultats valides.
     */
    public const VALID_RESULTS = ['SENT', 'INTERVIEW_ANSWER', 'DECISION_TAKEN'];

    /**
     * Statuts de présence valides.
     */
    public const VALID_ATTENDANCE_STATUSES = ['PLANNED', 'NO_SHOW', 'COMPLETED', 'CANCELLED'];

    /**
     * Crée une nouvelle Interview avec les défauts.
     */
    public function createInterview(
        string $candidateName,
        string $companyName,
        \DateTimeInterface $interviewDate,
        string $heureDebut,
        string $heureFin,
        string $meetLink = ''
    ): Interview {
        $interview = new Interview();
        $interview->setCandidateName($candidateName);
        $interview->setCompanyName($companyName);
        $interview->setInterviewDate($interviewDate);
        $interview->setHeureDebut($heureDebut);
        $interview->setHeureFin($heureFin);
        $interview->setMeet_link($meetLink);
        
        // Valeurs par défaut
        $interview->setStatus('PENDING');
        $interview->setResult('SENT');
        $interview->setAttendanceStatus('PLANNED');
        $interview->setRequestDate(new \DateTime());
        $interview->setDecisionDate(new \DateTime()); // Sera null après mise à jour en DB si nullable
        $interview->setIdContract(0);

        return $interview;
    }

    /**
     * Valide la cohérence heure début < heure fin.
     * 
     * @throws \InvalidArgumentException
     */
    public function validateTimeCoherence(string $heureDebut, string $heureFin): bool
    {
        // Format supposé: HH:MM
        try {
            $begin = \DateTime::createFromFormat('H:i', $heureDebut);
            $end = \DateTime::createFromFormat('H:i', $heureFin);

            if (!$begin || !$end) {
                throw new \InvalidArgumentException('Format heure invalide (HH:MM requis)');
            }

            if ($begin >= $end) {
                throw new \InvalidArgumentException('L\'heure fin doit être > heure début');
            }

            return true;
        } catch (\Exception $e) {
            throw new \InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * Valide que le statut est permis.
     * 
     * @throws \InvalidArgumentException
     */
    public function validateStatus(string $status): void
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            throw new \InvalidArgumentException(
                sprintf('Statut invalide: %s. Valides: %s', $status, implode(', ', self::VALID_STATUSES))
            );
        }
    }

    /**
     * Valide le résultat.
     * 
     * @throws \InvalidArgumentException
     */
    public function validateResult(string $result): void
    {
        if (!in_array($result, self::VALID_RESULTS, true)) {
            throw new \InvalidArgumentException(
                sprintf('Résultat invalide: %s. Valides: %s', $result, implode(', ', self::VALID_RESULTS))
            );
        }
    }

    /**
     * Valide le statut de présence.
     * 
     * @throws \InvalidArgumentException
     */
    public function validateAttendanceStatus(string $status): void
    {
        if (!in_array($status, self::VALID_ATTENDANCE_STATUSES, true)) {
            throw new \InvalidArgumentException(
                sprintf('Statut présence invalide: %s. Valides: %s', $status, implode(', ', self::VALID_ATTENDANCE_STATUSES))
            );
        }
    }

    /**
     * Marque l'entretien comme accepté par le candidat.
     */
    public function acceptInterview(Interview $interview): void
    {
        $interview->setStatus('ACCEPTED');
        $interview->setResult('INTERVIEW_ANSWER');
        $interview->setDecisionDate(new \DateTime());
        $this->em->flush();
    }

    /**
     * Marque l'entretien comme refusé par le candidat.
     */
    public function rejectInterview(Interview $interview): void
    {
        $interview->setStatus('REJECTED');
        $interview->setResult('INTERVIEW_ANSWER');
        $interview->setDecisionDate(new \DateTime());
        $this->em->flush();
    }

    /**
     * Marque l'entretien comme terminé (présence).
     */
    public function completeInterview(Interview $interview): void
    {
        $interview->setAttendanceStatus('COMPLETED');
        $interview->setResult('DECISION_TAKEN');
        $this->em->flush();
    }

    /**
     * Annule un entretien.
     */
    public function cancelInterview(Interview $interview): void
    {
        $interview->setStatus('CANCELLED');
        $interview->setAttendanceStatus('CANCELLED');
        $interview->setDecisionDate(new \DateTime());
        $this->em->flush();
    }
}
