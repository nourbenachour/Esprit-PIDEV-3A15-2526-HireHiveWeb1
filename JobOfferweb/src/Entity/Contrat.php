<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Contrat
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $idContrat;

    #[ORM\Column(type: "float")]
    private float $salary;

    #[ORM\Column(type: "string")]
    private string $contractType;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $startDate;

    #[ORM\Column(type: "date")]
    private \DateTimeInterface $endDate;

    #[ORM\Column(type: "string")]
    private string $status;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $signedAt;

    #[ORM\Column(type: "text")]
    private string $signature;

    #[ORM\Column(type: "string", length: 255)]
    private string $candidateName;

    #[ORM\Column(type: "string", length: 255)]
    private string $companyName;

    public function getIdContrat()
    {
        return $this->idContrat;
    }

    public function setIdContrat($value)
    {
        $this->idContrat = $value;
    }

    public function getSalary()
    {
        return $this->salary;
    }

    public function setSalary($value)
    {
        $this->salary = $value;
    }

    public function getContractType()
    {
        return $this->contractType;
    }

    public function setContractType($value)
    {
        $this->contractType = $value;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate($value)
    {
        $this->startDate = $value;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEndDate($value)
    {
        $this->endDate = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getSignedAt()
    {
        return $this->signedAt;
    }

    public function setSignedAt($value)
    {
        $this->signedAt = $value;
    }

    public function getSignature()
    {
        return $this->signature;
    }

    public function setSignature($value)
    {
        $this->signature = $value;
    }

    public function getCandidateName()
    {
        return $this->candidateName;
    }

    public function setCandidateName($value)
    {
        $this->candidateName = $value;
    }

    public function getCompanyName()
    {
        return $this->companyName;
    }

    public function setCompanyName($value)
    {
        $this->companyName = $value;
    }
}
