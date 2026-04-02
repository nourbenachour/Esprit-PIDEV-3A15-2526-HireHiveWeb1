<?php

namespace App\Service\interview;

use App\Entity\Contrat;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Service de gestion de la logique métier pour Contract.
 */
class ContractService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Statuts valides pour un Contrat.
     */
    public const VALID_STATUSES = ['PENDING', 'SENT', 'SIGNED', 'REJECTED'];

    /**
     * Types de contrat valides.
     */
    public const VALID_CONTRACT_TYPES = ['CDI', 'CDD', 'STAGE', 'ALTERNANCE', 'FREELANCE'];

    /**
     * Crée un nouveau Contrat avec les défauts.
     */
    public function createContract(
        string $candidateName,
        string $companyName,
        float $salary,
        string $contractType,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): Contrat {
        $contract = new Contrat();
        $contract->setCandidateName($candidateName);
        $contract->setCompanyName($companyName);
        $contract->setSalary($salary);
        $contract->setContractType($contractType);
        $contract->setStartDate($startDate);
        $contract->setEndDate($endDate);
        
        // État initial
        $contract->setStatus('PENDING');
        $contract->setSignature(''); // Vide jusqu'à signature réelle
        $contract->setSignedAt(new \DateTime()); // Sera null logiquement avant signature réelle

        return $contract;
    }

    /**
     * Valide la cohérence des dates.
     * 
     * @throws \InvalidArgumentException
     */
    public function validateDateCoherence(\DateTimeInterface $startDate, \DateTimeInterface $endDate): void
    {
        if ($startDate >= $endDate) {
            throw new \InvalidArgumentException('La date fin doit être après la date début');
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
     * Valide le type de contrat.
     * 
     * @throws \InvalidArgumentException
     */
    public function validateContractType(string $type): void
    {
        if (!in_array($type, self::VALID_CONTRACT_TYPES, true)) {
            throw new \InvalidArgumentException(
                sprintf('Type invalide: %s. Valides: %s', $type, implode(', ', self::VALID_CONTRACT_TYPES))
            );
        }
    }

    /**
     * Envoie le contrat au candidat (passage à SENT).
     */
    public function sendContract(Contrat $contract): void
    {
        if ($contract->getStatus() !== 'PENDING') {
            throw new \InvalidArgumentException('Seul un contrat en PENDING peut être envoyé');
        }
        $contract->setStatus('SENT');
        $this->em->flush();
    }

    /**
     * Signe le contrat (passage à SIGNED).
     * La signature doit être non vide pour valider.
     * 
     * @throws \InvalidArgumentException
     */
    public function signContract(Contrat $contract, string $signatureBase64): void
    {
        if (empty(trim($signatureBase64))) {
            throw new \InvalidArgumentException('La signature ne peut pas être vide');
        }

        $contract->setSignature($signatureBase64);
        $contract->setStatus('SIGNED');
        $contract->setSignedAt(new \DateTime());
        $this->em->flush();
    }

    /**
     * Rejette le contrat (passage à REJECTED).
     */
    public function rejectContract(Contrat $contract): void
    {
        $contract->setStatus('REJECTED');
        $this->em->flush();
    }

    /**
     * Vérifie si le contrat peut être signé (doit être en SENT).
     */
    public function canBeSignedNow(Contrat $contract): bool
    {
        return $contract->getStatus() === 'SENT' && empty($contract->getSignature());
    }

    /**
     * Exporte la signature en base64 (pour PDF ou affichage).
     */
    public function getSignatureData(Contrat $contract): ?string
    {
        if (empty($contract->getSignature())) {
            return null;
        }
        return $contract->getSignature();
    }
}
