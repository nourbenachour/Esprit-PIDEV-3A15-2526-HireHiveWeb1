<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Inscription;

#[ORM\Entity]
class Course
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_course;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "string")]
    private string $difficulty;

    #[ORM\Column(type: "bigint")]
    private string $recruiter_id;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: "integer")]
    private int $passed_times;

    #[ORM\Column(type: "string", length: 500)]
    private string $skills;

    #[ORM\Column(type: "string", length: 500)]
    private string $image_url;

    #[ORM\Column(type: "string", length: 500)]
    private string $pdf_url;

    #[ORM\Column(type: "integer")]
    private int $pass_score;

    public function getId_course()
    {
        return $this->id_course;
    }

    public function setId_course($value)
    {
        $this->id_course = $value;
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

    public function getDifficulty()
    {
        return $this->difficulty;
    }

    public function setDifficulty($value)
    {
        $this->difficulty = $value;
    }

    public function getRecruiter_id()
    {
        return $this->recruiter_id;
    }

    public function setRecruiter_id($value)
    {
        $this->recruiter_id = $value;
    }

    public function getCreated_at()
    {
        return $this->created_at;
    }

    public function setCreated_at($value)
    {
        $this->created_at = $value;
    }

    public function getPassed_times()
    {
        return $this->passed_times;
    }

    public function setPassed_times($value)
    {
        $this->passed_times = $value;
    }

    public function getSkills()
    {
        return $this->skills;
    }

    public function setSkills($value)
    {
        $this->skills = $value;
    }

    public function getImage_url()
    {
        return $this->image_url;
    }

    public function setImage_url($value)
    {
        $this->image_url = $value;
    }

    public function getPdf_url()
    {
        return $this->pdf_url;
    }

    public function setPdf_url($value)
    {
        $this->pdf_url = $value;
    }

    public function getPass_score()
    {
        return $this->pass_score;
    }

    public function setPass_score($value)
    {
        $this->pass_score = $value;
    }

    #[ORM\OneToMany(mappedBy: "course_id", targetEntity: Certificate::class)]
    private Collection $certificates;

        public function getCertificates(): Collection
        {
            return $this->certificates;
        }
    
        public function addCertificate(Certificate $certificate): self
        {
            if (!$this->certificates->contains($certificate)) {
                $this->certificates[] = $certificate;
                $certificate->setCourse_id($this);
            }
    
            return $this;
        }
    
        public function removeCertificate(Certificate $certificate): self
        {
            if ($this->certificates->removeElement($certificate)) {
                // set the owning side to null (unless already changed)
                if ($certificate->getCourse_id() === $this) {
                    $certificate->setCourse_id(null);
                }
            }
    
            return $this;
        }

    #[ORM\OneToMany(mappedBy: "course_id", targetEntity: Inscription::class)]
    private Collection $inscriptions;
}
