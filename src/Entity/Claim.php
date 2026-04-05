<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\Reclamation\ClaimRepository;

/**
 * Claim entity – reverse-engineered from existing DB schema.
 * Patched to add:
 *   - Assert validation constraints (server-side validation)
 *   - Proper collection initialisation in constructor
 *   - Accessors for the claim_responses collection
 *   - Default values for status and priority
 */
#[ORM\Entity(repositoryClass: ClaimRepository::class)]
#[ORM\Table(name: 'claim')]
class Claim
{
    // -----------------------------------------------------------------------
    // Primary key — kept as bigint to match the reverse-engineered schema
    // -----------------------------------------------------------------------
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'bigint')]
    private ?string $id_claim = null;

    // -----------------------------------------------------------------------
    // FK column stored as plain integer (mirrors the DB column)
    // -----------------------------------------------------------------------
    #[ORM\Column(type: 'bigint', nullable: true)]
    private ?string $user_id = null;

    // -----------------------------------------------------------------------
    // Claim type – e.g. "Technical", "Billing", "Other"
    // -----------------------------------------------------------------------
    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Please select a claim type.')]
    private string $type = 'Other';

    // -----------------------------------------------------------------------
    // Title: mandatory, minimum 5 characters
    // -----------------------------------------------------------------------
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Le titre ne peut pas être vide.')]
    #[Assert\Length(min: 5, minMessage: 'Le titre doit faire au moins {{ limit }} caractères.')]
    #[Assert\Regex(pattern: '/^(?!0+$).+$/', message: 'Le titre ne peut pas être composé uniquement de zéros.')]
    private string $title = '';

    // -----------------------------------------------------------------------
    // Description: mandatory text body of the claim, min 10 chars
    // -----------------------------------------------------------------------
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'La description ne peut pas être vide.')]
    #[Assert\Length(min: 10, minMessage: 'La description doit faire au moins {{ limit }} caractères pour être claire.')]
    private string $description = '';

    // -----------------------------------------------------------------------
    // Status: OPEN | IN_PROGRESS | RESOLVED | CLOSED  (default: OPEN)
    // -----------------------------------------------------------------------
    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'OPEN';

    // -----------------------------------------------------------------------
    // Priority: LOW | MEDIUM | URGENT  (auto-set by ClaimPriorityService on creation)
    // -----------------------------------------------------------------------
    #[ORM\Column(type: 'string', length: 50)]
    private string $priority = 'LOW';

    // -----------------------------------------------------------------------
    // Timestamps
    // -----------------------------------------------------------------------
    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $resolved_at = null;

    // -----------------------------------------------------------------------
    // Relations
    // -----------------------------------------------------------------------

    /** Attachments (from reverse-engineering, kept as-is) */
    #[ORM\OneToMany(mappedBy: 'claim_id', targetEntity: Claim_attachment::class)]
    private Collection $claim_attachments;

    /**
     * Responses submitted by admins for this claim.
     * mappedBy refers to the $claim_id property in Claim_response (the FK side).
     */
    #[ORM\OneToMany(mappedBy: 'claim_id', targetEntity: Claim_response::class, cascade: ['persist', 'remove'])]
    private Collection $claim_responses;

    // -----------------------------------------------------------------------
    // Constructor
    // -----------------------------------------------------------------------
    public function __construct()
    {
        $this->claim_attachments = new ArrayCollection();
        $this->claim_responses   = new ArrayCollection();
        $this->created_at        = new \DateTime();
        $this->status            = 'OPEN';
        $this->priority          = 'LOW';
    }

    // -----------------------------------------------------------------------
    // Getters / Setters
    // -----------------------------------------------------------------------

    public function getId_claim(): ?string
    {
        return $this->id_claim;
    }

    /** Alias used by Symfony routing (path('…', {id: claim.id})) */
    public function getId(): ?string
    {
        return $this->id_claim;
    }

    public function getUser_id(): ?string
    {
        return $this->user_id;
    }

    public function setUser_id($value): void
    {
        $this->user_id = $value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType($value): void
    {
        $this->type = $value;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle($value): void
    {
        $this->title = $value;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription($value): void
    {
        $this->description = $value;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus($value): void
    {
        $this->status = $value;
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function setPriority($value): void
    {
        $this->priority = $value;
    }

    public function getCreated_at(): \DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreated_at($value): void
    {
        $this->created_at = $value;
    }

    public function getResolved_at(): ?\DateTimeInterface
    {
        return $this->resolved_at;
    }

    public function setResolved_at($value): void
    {
        $this->resolved_at = $value;
    }

    // -----------------------------------------------------------------------
    // Collection: Claim_attachment
    // -----------------------------------------------------------------------

    public function getClaim_attachments(): Collection
    {
        return $this->claim_attachments;
    }

    public function addClaim_attachment(Claim_attachment $claim_attachment): self
    {
        if (!$this->claim_attachments->contains($claim_attachment)) {
            $this->claim_attachments[] = $claim_attachment;
            $claim_attachment->setClaim_id($this);
        }
        return $this;
    }

    public function removeClaim_attachment(Claim_attachment $claim_attachment): self
    {
        if ($this->claim_attachments->removeElement($claim_attachment)) {
            if ($claim_attachment->getClaim_id() === $this) {
                $claim_attachment->setClaim_id(null);
            }
        }
        return $this;
    }

    // -----------------------------------------------------------------------
    // Collection: Claim_response
    // -----------------------------------------------------------------------

    public function getClaim_responses(): Collection
    {
        return $this->claim_responses;
    }

    public function addClaim_response(Claim_response $response): self
    {
        if (!$this->claim_responses->contains($response)) {
            $this->claim_responses[] = $response;
            $response->setClaim_id($this);
        }
        return $this;
    }

    public function removeClaim_response(Claim_response $response): self
    {
        if ($this->claim_responses->removeElement($response)) {
            if ($response->getClaim_id() === $this) {
                $response->setClaim_id(null);
            }
        }
        return $this;
    }
}
