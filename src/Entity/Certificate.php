<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Course;

#[ORM\Entity]
class Certificate
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_certificate;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $issued_at;

    #[ORM\Column(type: "bigint")]
    private string $condidat_id;

        #[ORM\ManyToOne(targetEntity: Course::class, inversedBy: "certificates")]
    #[ORM\JoinColumn(name: 'course_id', referencedColumnName: 'id_course', onDelete: 'CASCADE')]
    private Course $course_id;

    #[ORM\Column(type: "string", length: 500)]
    private string $shareable_link;

    #[ORM\Column(type: "string", length: 500)]
    private string $certified_url;

    public function getId_certificate()
    {
        return $this->id_certificate;
    }

    public function setId_certificate($value)
    {
        $this->id_certificate = $value;
    }

    public function getIssued_at()
    {
        return $this->issued_at;
    }

    public function setIssued_at($value)
    {
        $this->issued_at = $value;
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

    public function getShareable_link()
    {
        return $this->shareable_link;
    }

    public function setShareable_link($value)
    {
        $this->shareable_link = $value;
    }

    public function getCertified_url()
    {
        return $this->certified_url;
    }

    public function setCertified_url($value)
    {
        $this->certified_url = $value;
    }
}
