<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Claim_response;

#[ORM\Entity]
class Claim
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_claim;

    #[ORM\Column(type: "bigint")]
    private string $user_id;

    #[ORM\Column(type: "string")]
    private string $type;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "string")]
    private string $status;

    #[ORM\Column(type: "string")]
    private string $priority;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $resolved_at;

    public function getId_claim()
    {
        return $this->id_claim;
    }

    public function setId_claim($value)
    {
        $this->id_claim = $value;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($value)
    {
        $this->type = $value;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($value)
    {
        $this->title = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority($value)
    {
        $this->priority = $value;
    }

    public function getCreated_at()
    {
        return $this->created_at;
    }

    public function setCreated_at($value)
    {
        $this->created_at = $value;
    }

    public function getResolved_at()
    {
        return $this->resolved_at;
    }

    public function setResolved_at($value)
    {
        $this->resolved_at = $value;
    }

    #[ORM\OneToMany(mappedBy: "claim_id", targetEntity: Claim_attachment::class)]
    private Collection $claim_attachments;

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
                // set the owning side to null (unless already changed)
                if ($claim_attachment->getClaim_id() === $this) {
                    $claim_attachment->setClaim_id(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "claim_id", targetEntity: Claim_response::class)]
    private Collection $claim_responses;
}
