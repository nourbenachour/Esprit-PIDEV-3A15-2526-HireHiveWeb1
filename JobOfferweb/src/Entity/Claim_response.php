<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Claim;

#[ORM\Entity]
class Claim_response
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_response;

        #[ORM\ManyToOne(targetEntity: Claim::class, inversedBy: "claim_responses")]
    #[ORM\JoinColumn(name: 'claim_id', referencedColumnName: 'id_claim', onDelete: 'CASCADE')]
    private Claim $claim_id;

    #[ORM\Column(type: "bigint")]
    private string $responder_id;

    #[ORM\Column(type: "text")]
    private string $message;

    #[ORM\Column(type: "text")]
    private string $internal_notes;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    public function getId_response()
    {
        return $this->id_response;
    }

    public function setId_response($value)
    {
        $this->id_response = $value;
    }

    public function getClaim_id()
    {
        return $this->claim_id;
    }

    public function setClaim_id($value)
    {
        $this->claim_id = $value;
    }

    public function getResponder_id()
    {
        return $this->responder_id;
    }

    public function setResponder_id($value)
    {
        $this->responder_id = $value;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($value)
    {
        $this->message = $value;
    }

    public function getInternal_notes()
    {
        return $this->internal_notes;
    }

    public function setInternal_notes($value)
    {
        $this->internal_notes = $value;
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
