<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Job_offer;

#[ORM\Entity]
class Saved_job_offer
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_saved;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "savedJobOffers")]
    #[ORM\JoinColumn(name: 'candidate_id', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private Users $candidate_id;

        #[ORM\ManyToOne(targetEntity: Job_offer::class, inversedBy: "saved_job_offers")]
    #[ORM\JoinColumn(name: 'job_offer_id', referencedColumnName: 'id_job_offer', onDelete: 'CASCADE')]
    private Job_offer $job_offer_id;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $saved_at;

    public function getId_saved()
    {
        return $this->id_saved;
    }

    public function setId_saved($value)
    {
        $this->id_saved = $value;
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

    public function getSaved_at()
    {
        return $this->saved_at;
    }

    public function setSaved_at($value)
    {
        $this->saved_at = $value;
    }
}
