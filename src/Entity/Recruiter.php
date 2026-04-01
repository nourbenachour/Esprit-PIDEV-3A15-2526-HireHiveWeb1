<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Users;

#[ORM\Entity]
class Recruiter
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_recruiter;

        #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "recruiters")]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private Users $user_id;

    #[ORM\Column(type: "bigint")]
    private string $company_id;

    #[ORM\Column(type: "string", length: 255)]
    private string $company_name;

    #[ORM\Column(type: "string", length: 500)]
    private string $company_logo;

    #[ORM\Column(type: "text")]
    private string $company_bio;

    #[ORM\Column(type: "string", length: 255)]
    private string $company_website;

    #[ORM\Column(type: "string", length: 150)]
    private string $secteur_activite;

    #[ORM\Column(type: "string", length: 150)]
    private string $email_contact_entreprise;

    #[ORM\Column(type: "string", length: 20)]
    private string $telephone_service_client;

    #[ORM\Column(type: "integer")]
    private int $salaires;

    #[ORM\Column(type: "string", length: 150)]
    private string $adresse;

    #[ORM\Column(type: "string", length: 150)]
    private string $position;

    #[ORM\Column(type: "string", length: 20)]
    private string $permission;

    public function getId_recruiter()
    {
        return $this->id_recruiter;
    }

    public function setId_recruiter($value)
    {
        $this->id_recruiter = $value;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function setUser_id($value)
    {
        $this->user_id = $value;
    }

    public function getCompany_id()
    {
        return $this->company_id;
    }

    public function setCompany_id($value)
    {
        $this->company_id = $value;
    }

    public function getCompany_name()
    {
        return $this->company_name;
    }

    public function setCompany_name($value)
    {
        $this->company_name = $value;
    }

    public function getCompany_logo()
    {
        return $this->company_logo;
    }

    public function setCompany_logo($value)
    {
        $this->company_logo = $value;
    }

    public function getCompany_bio()
    {
        return $this->company_bio;
    }

    public function setCompany_bio($value)
    {
        $this->company_bio = $value;
    }

    public function getCompany_website()
    {
        return $this->company_website;
    }

    public function setCompany_website($value)
    {
        $this->company_website = $value;
    }

    public function getSecteur_activite()
    {
        return $this->secteur_activite;
    }

    public function setSecteur_activite($value)
    {
        $this->secteur_activite = $value;
    }

    public function getEmail_contact_entreprise()
    {
        return $this->email_contact_entreprise;
    }

    public function setEmail_contact_entreprise($value)
    {
        $this->email_contact_entreprise = $value;
    }

    public function getTelephone_service_client()
    {
        return $this->telephone_service_client;
    }

    public function setTelephone_service_client($value)
    {
        $this->telephone_service_client = $value;
    }

    public function getSalaires()
    {
        return $this->salaires;
    }

    public function setSalaires($value)
    {
        $this->salaires = $value;
    }

    public function getAdresse()
    {
        return $this->adresse;
    }

    public function setAdresse($value)
    {
        $this->adresse = $value;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($value)
    {
        $this->position = $value;
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function setPermission($value)
    {
        $this->permission = $value;
    }
}
