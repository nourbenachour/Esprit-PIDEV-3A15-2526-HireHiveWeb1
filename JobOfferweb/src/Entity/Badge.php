<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Badge
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_badge;

    #[ORM\Column(type: "string", length: 255)]
    private string $title;

    #[ORM\Column(type: "text")]
    private string $description;

    #[ORM\Column(type: "string", length: 500)]
    private string $icon_url;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    public function getId_badge()
    {
        return $this->id_badge;
    }

    public function setId_badge($value)
    {
        $this->id_badge = $value;
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

    public function getIcon_url()
    {
        return $this->icon_url;
    }

    public function setIcon_url($value)
    {
        $this->icon_url = $value;
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
