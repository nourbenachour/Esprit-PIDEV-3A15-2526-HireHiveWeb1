<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Claim;

#[ORM\Entity]
class Claim_attachment
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_attachment;

        #[ORM\ManyToOne(targetEntity: Claim::class, inversedBy: "claim_attachments")]
    #[ORM\JoinColumn(name: 'claim_id', referencedColumnName: 'id_claim', onDelete: 'CASCADE')]
    private Claim $claim_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $file_path;

    #[ORM\Column(type: "string", length: 255)]
    private string $file_name;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $uploaded_at;

    public function getId_attachment()
    {
        return $this->id_attachment;
    }

    public function setId_attachment($value)
    {
        $this->id_attachment = $value;
    }

    public function getClaim_id()
    {
        return $this->claim_id;
    }

    public function setClaim_id($value)
    {
        $this->claim_id = $value;
    }

    public function getFile_path()
    {
        return $this->file_path;
    }

    public function setFile_path($value)
    {
        $this->file_path = $value;
    }

    public function getFile_name()
    {
        return $this->file_name;
    }

    public function setFile_name($value)
    {
        $this->file_name = $value;
    }

    public function getUploaded_at()
    {
        return $this->uploaded_at;
    }

    public function setUploaded_at($value)
    {
        $this->uploaded_at = $value;
    }
}
