<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Reaction
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_reaction;

    #[ORM\Column(type: "bigint")]
    private string $id_user;

    #[ORM\Column(type: "bigint")]
    private string $post_id;

    #[ORM\Column(type: "string")]
    private string $reaction_type;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    public function getId_reaction()
    {
        return $this->id_reaction;
    }

    public function setId_reaction($value)
    {
        $this->id_reaction = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
    }

    public function getPost_id()
    {
        return $this->post_id;
    }

    public function setPost_id($value)
    {
        $this->post_id = $value;
    }

    public function getReaction_type()
    {
        return $this->reaction_type;
    }

    public function setReaction_type($value)
    {
        $this->reaction_type = $value;
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
