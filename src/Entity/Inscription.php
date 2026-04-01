<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Course;

#[ORM\Entity]
class Inscription
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_inscription;

    #[ORM\Column(type: "bigint")]
    private string $condidat_id;

        #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: "inscriptions")]
    #[ORM\JoinColumn(name: 'course_id', referencedColumnName: 'id_course', onDelete: 'CASCADE')]
    private Course $course_id;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $inscrit_at;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $completed_at;

    #[ORM\Column(type: "bigint")]
    private string $certificate_id;

    #[ORM\Column(type: "bigint")]
    private string $badge_id;

    public function getId_inscription()
    {
        return $this->id_inscription;
    }

    public function setId_inscription($value)
    {
        $this->id_inscription = $value;
    }

    public function getCondidat_id()
    {
        return $this->condidat_id;
    }

    public function setCondidat_id($value)
    {
        $this->condidat_id = $value;
    }

    public function getCourse_id()
    {
        return $this->course_id;
    }

    public function setCourse_id($value)
    {
        $this->course_id = $value;
    }

    public function getInscrit_at()
    {
        return $this->inscrit_at;
    }

    public function setInscrit_at($value)
    {
        $this->inscrit_at = $value;
    }

    public function getCompleted_at()
    {
        return $this->completed_at;
    }

    public function setCompleted_at($value)
    {
        $this->completed_at = $value;
    }

    public function getCertificate_id()
    {
        return $this->certificate_id;
    }

    public function setCertificate_id($value)
    {
        $this->certificate_id = $value;
    }

    public function getBadge_id()
    {
        return $this->badge_id;
    }

    public function setBadge_id($value)
    {
        $this->badge_id = $value;
    }
}
