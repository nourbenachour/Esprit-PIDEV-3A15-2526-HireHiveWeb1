<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Interview
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $idInterview;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $interviewDate;

    #[ORM\Column(type: "string")]
    private string $result;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $requestDate;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $decisionDate;

    #[ORM\Column(type: "string")]
    private string $status;

    #[ORM\Column(type: "string")]
    private string $attendanceStatus;

    #[ORM\Column(type: "integer")]
    private int $idContract;

    #[ORM\Column(type: "string", length: 255)]
    private string $candidateName;

    #[ORM\Column(type: "string", length: 255)]
    private string $companyName;

    #[ORM\Column(type: "string")]
    private string $heureDebut;

    #[ORM\Column(type: "string")]
    private string $heureFin;

    #[ORM\Column(type: "string", length: 1024)]
    private string $meet_link;

    public function getIdInterview()
    {
        return $this->idInterview;
    }

    public function setIdInterview($value)
    {
        $this->idInterview = $value;
    }

    public function getInterviewDate()
    {
        return $this->interviewDate;
    }

    public function setInterviewDate($value)
    {
        $this->interviewDate = $value;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setResult($value)
    {
        $this->result = $value;
    }

    public function getRequestDate()
    {
        return $this->requestDate;
    }

    public function setRequestDate($value)
    {
        $this->requestDate = $value;
    }

    public function getDecisionDate()
    {
        return $this->decisionDate;
    }

    public function setDecisionDate($value)
    {
        $this->decisionDate = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getAttendanceStatus()
    {
        return $this->attendanceStatus;
    }

    public function setAttendanceStatus($value)
    {
        $this->attendanceStatus = $value;
    }

    public function getIdContract()
    {
        return $this->idContract;
    }

    public function setIdContract($value)
    {
        $this->idContract = $value;
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

    public function getHeureDebut()
    {
        return $this->heureDebut;
    }

    public function setHeureDebut($value)
    {
        $this->heureDebut = $value;
    }

    public function getHeureFin()
    {
        return $this->heureFin;
    }

    public function setHeureFin($value)
    {
        $this->heureFin = $value;
    }

    public function getMeet_link()
    {
        return $this->meet_link;
    }

    public function setMeet_link($value)
    {
        $this->meet_link = $value;
    }
}
