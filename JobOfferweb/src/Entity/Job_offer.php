<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Job_notification;

#[ORM\Entity]
class Job_offer
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_job_offer;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "string", length: 150)]
    private string $location;

    #[ORM\Column(type: "string", length: 100)]
    private string $contract_type;

    #[ORM\Column(type: "string", length: 50)]
    private string $status;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: "text")]
    private string $skills;

    #[ORM\Column(type: "text")]
    private string $soft_skills;

    #[ORM\Column(type: "bigint")]
    private string $recruiter_id;

    #[ORM\Column(type: "float")]
    private float $latitude;

    #[ORM\Column(type: "float")]
    private float $longitude;

    public function getId_job_offer()
    {
        return $this->id_job_offer;
    }

    public function setId_job_offer($value)
    {
        $this->id_job_offer = $value;
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

    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($value)
    {
        $this->location = $value;
    }

    public function getContract_type()
    {
        return $this->contract_type;
    }

    public function setContract_type($value)
    {
        $this->contract_type = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getCreated_at()
    {
        return $this->created_at;
    }

    public function setCreated_at($value)
    {
        $this->created_at = $value;
    }

    public function getSkills()
    {
        return $this->skills;
    }

    public function setSkills($value)
    {
        $this->skills = $value;
    }

    public function getSoft_skills()
    {
        return $this->soft_skills;
    }

    public function setSoft_skills($value)
    {
        $this->soft_skills = $value;
    }

    public function getRecruiter_id()
    {
        return $this->recruiter_id;
    }

    public function setRecruiter_id($value)
    {
        $this->recruiter_id = $value;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLatitude($value)
    {
        $this->latitude = $value;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setLongitude($value)
    {
        $this->longitude = $value;
    }

    #[ORM\OneToMany(mappedBy: "job_offer_id", targetEntity: Saved_job_offer::class)]
    private Collection $saved_job_offers;

        public function getSaved_job_offers(): Collection
        {
            return $this->saved_job_offers;
        }
    
        public function addSaved_job_offer(Saved_job_offer $saved_job_offer): self
        {
            if (!$this->saved_job_offers->contains($saved_job_offer)) {
                $this->saved_job_offers[] = $saved_job_offer;
                $saved_job_offer->setJob_offer_id($this);
            }
    
            return $this;
        }
    
        public function removeSaved_job_offer(Saved_job_offer $saved_job_offer): self
        {
            if ($this->saved_job_offers->removeElement($saved_job_offer)) {
                // set the owning side to null (unless already changed)
                if ($saved_job_offer->getJob_offer_id() === $this) {
                    $saved_job_offer->setJob_offer_id(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "job_offer_id", targetEntity: Job_notification::class)]
    private Collection $job_notifications;
}
