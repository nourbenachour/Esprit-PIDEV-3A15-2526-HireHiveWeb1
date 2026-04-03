<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Users;

#[ORM\Entity]
class Password_reset_tokens
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "passwordResetTokens")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private Users $user_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $token_hash;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $expires_at;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $used_at;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    public function getId()
    {
        return $this->id;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    public function getToken_hash()
    {
        return $this->token_hash;
    }

    public function setToken_hash($value)
    {
        $this->token_hash = $value;
    }

    public function getExpires_at()
    {
        return $this->expires_at;
    }

    public function setExpires_at($value)
    {
        $this->expires_at = $value;
    }

    public function getUsed_at()
    {
        return $this->used_at;
    }

    public function setUsed_at($value)
    {
        $this->used_at = $value;
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
