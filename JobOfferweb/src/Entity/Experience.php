<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Condidat;

#[ORM\Entity]
class Experience
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id;

        #[ORM\ManyToOne(targetEntity: Condidat::class, inversedBy: "experiences")]
    #[ORM\JoinColumn(name: 'condidat_id', referencedColumnName: 'id_condidat', onDelete: 'CASCADE')]
    private Condidat $condidat_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "string", length: 255)]
    private string $company;

    #[ORM\Column(type: "string", length: 100)]
    private string $period;

    #[ORM\Column(type: "text")]
    private string $description;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getCondidat_id()
    {
        return $this->condidat_id;
    }

    public function setCondidat_id($value)
    {
        $this->condidat_id = $value;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($value)
    {
        $this->title = $value;
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function setCompany($value)
    {
        $this->company = $value;
    }

    public function getPeriod()
    {
        return $this->period;
    }

    public function setPeriod($value)
    {
        $this->period = $value;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($value)
    {
        $this->description = $value;
    }
}
