<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\Reclamation\ClaimResponseRepository;

/**
 * Claim_response entity – response posted by an admin for a given Claim.
 * Patched to add:
 *   - Assert\NotBlank on $message
 *   - ClaimResponseRepository link
 *   - nullable fields to avoid DB hydration errors
 *   - getId() alias for Symfony routing convenience
 */
#[ORM\Entity(repositoryClass: ClaimResponseRepository::class)]
#[ORM\Table(name: 'claim_response')]
class Claim_response
{
    // -----------------------------------------------------------------------
    // Primary key
    // -----------------------------------------------------------------------
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?string $id_response = null;

    // -----------------------------------------------------------------------
    // FK → Claim (owning side of the OneToMany)
    // -----------------------------------------------------------------------
    #[ORM\ManyToOne(targetEntity: Claim::class, inversedBy: 'claim_responses')]
    #[ORM\JoinColumn(name: 'claim_id', referencedColumnName: 'id_claim', onDelete: 'CASCADE')]
    private ?Claim $claim_id = null;

    // -----------------------------------------------------------------------
    // Responder (stored as plain int to match schema; can be null for forms)
    // -----------------------------------------------------------------------
    #[ORM\Column(type: 'bigint', nullable: true)]
    private ?string $responder_id = null;

    // -----------------------------------------------------------------------
    // The response message – mandatory (server-side validation)
    // -----------------------------------------------------------------------
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le message de réponse ne peut pas être vide.')]
    private string $message = '';

    // -----------------------------------------------------------------------
    // Internal notes – optional
    // -----------------------------------------------------------------------
    #[ORM\Column(type: 'text')]
    private string $internal_notes = '';

    // -----------------------------------------------------------------------
    // Timestamp
    // -----------------------------------------------------------------------
    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $created_at;

    // -----------------------------------------------------------------------
    // Constructor
    // -----------------------------------------------------------------------
    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    // -----------------------------------------------------------------------
    // Getters / Setters
    // -----------------------------------------------------------------------

    public function getId_response(): ?string
    {
        return $this->id_response;
    }

    /** Alias for Symfony routing */
    public function getId(): ?string
    {
        return $this->id_response;
    }

    public function getClaim_id(): ?Claim
    {
        return $this->claim_id;
    }

    public function setClaim_id($value): void
    {
        $this->claim_id = $value;
    }

    public function getResponder_id(): ?string
    {
        return $this->responder_id;
    }

    public function setResponder_id($value): void
    {
        $this->responder_id = $value;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage($value): void
    {
        $this->message = $value;
    }

    public function getInternal_notes(): string
    {
        return $this->internal_notes;
    }

    public function setInternal_notes(string $value): void
    {
        $this->internal_notes = $value;
    }

    public function getCreated_at(): \DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreated_at($value): void
    {
        $this->created_at = $value;
    }
}
