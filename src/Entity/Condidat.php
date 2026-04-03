<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Users;
use Doctrine\Common\Collections\Collection;
use App\Entity\Experience;

#[ORM\Entity]
class Condidat
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_condidat;

        #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "condidats")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private Users $user_id;

    #[ORM\Column(type: "text")]
    private string $education;

    #[ORM\Column(type: "text")]
    private string $bio;

    #[ORM\Column(type: "string", length: 500)]
    private string $cv;

    #[ORM\Column(type: "text")]
    private string $experience;

    #[ORM\Column(type: "text")]
    private string $competances;

    #[ORM\Column(type: "text")]
    private string $formations;

    #[ORM\Column(type: "string", length: 500)]
    private string $photo;

    public function getId_condidat()
    {
        return $this->id_condidat;
    }

    public function setId_condidat($value)
    {
        $this->id_condidat = $value;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    public function getEducation()
    {
        return $this->education;
    }

    public function setEducation($value)
    {
        $this->education = $value;
    }

    public function getBio()
    {
        return $this->bio;
    }

    public function setBio($value)
    {
        $this->bio = $value;
    }

    public function getCv()
    {
        return $this->cv;
    }

    public function setCv($value)
    {
        $this->cv = $value;
    }

    public function getExperience()
    {
        return $this->experience;
    }

    public function setExperience($value)
    {
        $this->experience = $value;
    }

    public function getCompetances()
    {
        return $this->competances;
    }

    public function setCompetances($value)
    {
        $this->competances = $value;
    }

    public function getFormations()
    {
        return $this->formations;
    }

    public function setFormations($value)
    {
        $this->formations = $value;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($value)
    {
        $this->photo = $value;
    }

    #[ORM\OneToMany(mappedBy: "condidat_id", targetEntity: Experience::class)]
    private Collection $experiences;

        public function getExperiences(): Collection
        {
            return $this->experiences;
        }
    
        public function addExperience(Experience $experience): self
        {
            if (!$this->experiences->contains($experience)) {
                $this->experiences[] = $experience;
                $experience->setCondidat_id($this);
            }
    
            return $this;
        }
    
        public function removeExperience(Experience $experience): self
        {
            if ($this->experiences->removeElement($experience)) {
                // set the owning side to null (unless already changed)
                if ($experience->getCondidat_id() === $this) {
                    $experience->setCondidat_id(null);
                }
            }
    
            return $this;
        }
}
