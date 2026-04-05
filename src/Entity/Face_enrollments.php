<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Face_enrollments
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id;

    #[ORM\Column(type: "bigint")]
    private string $user_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $email;

    #[ORM\Column(type: "string", length: 64)]
    private string $provider;

    #[ORM\Column(type: "string", length: 255)]
    private string $person_id;

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

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($value)
    {
        $this->email = $value;
    }

    public function getProvider()
    {
        return $this->provider;
    }

    public function setProvider($value)
    {
        $this->provider = $value;
    }

    public function getPerson_id()
    {
        return $this->person_id;
    }

    public function setPerson_id($value)
    {
        $this->person_id = $value;
    }
}
