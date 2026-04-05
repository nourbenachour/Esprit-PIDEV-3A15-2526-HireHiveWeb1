<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Job_notification;
use App\Repository\ApplicationRepository;

#[ORM\Entity(repositoryClass: ApplicationRepository::class)]
class Application
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_condidature;

    #[ORM\Column(type: "bigint")]
    private string $condidat_id;

    #[ORM\Column(type: "bigint")]
    private string $job_offer_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $cv_file_path;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $application_date;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $last_update;

    #[ORM\Column(type: "string")]
    private string $status;

    #[ORM\Column(type: "string", length: 2000)]
    private string $lettre;

    public function getId_condidature()
    {
        return $this->id_condidature;
    }

    public function setId_condidature($value)
    {
        $this->id_condidature = $value;
    }

    public function getCondidat_id()
    {
        return $this->condidat_id;
    }

    public function setCondidat_id($value)
    {
        $this->condidat_id = $value;
    }

    public function getJob_offer_id()
    {
        return $this->job_offer_id;
    }

    public function setJob_offer_id($value)
    {
        $this->job_offer_id = $value;
    }

    public function getCv_file_path()
    {
        return $this->cv_file_path;
    }

    public function setCv_file_path($value)
    {
        $this->cv_file_path = $value;
    }

    public function getApplication_date()
    {
        return $this->application_date;
    }

    public function setApplication_date($value)
    {
        $this->application_date = $value;
    }

    public function getLast_update()
    {
        return $this->last_update;
    }

    public function setLast_update($value)
    {
        $this->last_update = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getLettre()
    {
        return $this->lettre;
    }

    public function setLettre($value)
    {
        $this->lettre = $value;
    }

    #[ORM\OneToMany(mappedBy: "application_id", targetEntity: Job_notification::class)]
    private Collection $job_notifications;

        public function getJob_notifications(): Collection
        {
            return $this->job_notifications;
        }
    
        public function addJob_notification(Job_notification $job_notification): self
        {
            if (!$this->job_notifications->contains($job_notification)) {
                $this->job_notifications[] = $job_notification;
                $job_notification->setApplication_id($this);
            }
    
            return $this;
        }
    
        public function removeJob_notification(Job_notification $job_notification): self
        {
            if ($this->job_notifications->removeElement($job_notification)) {
                // set the owning side to null (unless already changed)
                if ($job_notification->getApplication_id() === $this) {
                    $job_notification->setApplication_id(null);
                }
            }
    
            return $this;
        }
}
