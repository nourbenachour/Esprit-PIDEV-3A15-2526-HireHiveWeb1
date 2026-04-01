<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Application;

#[ORM\Entity]
class Job_notification
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_notification;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "jobNotifications")]
    #[ORM\JoinColumn(name: 'candidate_id', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private Users $candidate_id;

        #[ORM\ManyToOne(targetEntity: Job_offer::class, inversedBy: "job_notifications")]
    #[ORM\JoinColumn(name: 'job_offer_id', referencedColumnName: 'id_job_offer', onDelete: 'CASCADE')]
    private Job_offer $job_offer_id;

        #[ORM\ManyToOne(targetEntity: Application::class, inversedBy: "job_notifications")]
    #[ORM\JoinColumn(name: 'application_id', referencedColumnName: 'id_condidature', onDelete: 'CASCADE')]
    private Application $application_id;

    #[ORM\Column(type: "string", length: 500)]
    private string $message;

    #[ORM\Column(type: "boolean")]
    private bool $is_read;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    public function getId_notification()
    {
        return $this->id_notification;
    }

    public function setId_notification($value)
    {
        $this->id_notification = $value;
    }

    public function getCandidate_id()
    {
        return $this->candidate_id;
    }

    public function setCandidate_id($value)
    {
        $this->candidate_id = $value;
    }

    public function getJob_offer_id()
    {
        return $this->job_offer_id;
    }

    public function setJob_offer_id($value)
    {
        $this->job_offer_id = $value;
    }

    public function getApplication_id()
    {
        return $this->application_id;
    }

    public function setApplication_id($value)
    {
        $this->application_id = $value;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($value)
    {
        $this->message = $value;
    }

    public function getIs_read()
    {
        return $this->is_read;
    }

    public function setIs_read($value)
    {
        $this->is_read = $value;
    }

    public function getCreated_at()
    {
        return $this->created_at;
    }

    public function setCreated_at($value)
    {
        $this->created_at = $value;
    }
}
