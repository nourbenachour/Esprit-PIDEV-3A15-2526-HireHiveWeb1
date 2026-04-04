<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Users;

#[ORM\Entity]
class Post
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_post;

        #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "posts")]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private Users $id_user;

    #[ORM\Column(type: "string")]
    private string $visibility;

    #[ORM\Column(type: "string")]
    private string $type;

    #[ORM\Column(type: "string", length: 150)]
    private string $title;

    #[ORM\Column(type: "text")]
    private string $content;

    #[ORM\Column(type: "text")]
    private string $image_url;

    #[ORM\Column(type: "boolean")]
    private bool $is_published;

    #[ORM\Column(type: "integer")]
    private int $view_count;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $updated_at;

    public function getId_post()
    {
        return $this->id_post;
    }

    public function setId_post($value)
    {
        $this->id_post = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
    }

    public function getVisibility()
    {
        return $this->visibility;
    }

    public function setVisibility($value)
    {
        $this->visibility = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($value)
    {
        $this->type = $value;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($value)
    {
        $this->title = $value;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($value)
    {
        $this->content = $value;
    }

    public function getImage_url()
    {
        return $this->image_url;
    }

    public function setImage_url($value)
    {
        $this->image_url = $value;
    }

    public function getIs_published()
    {
        return $this->is_published;
    }

    public function setIs_published($value)
    {
        $this->is_published = $value;
    }

    public function getView_count()
    {
        return $this->view_count;
    }

    public function setView_count($value)
    {
        $this->view_count = $value;
    }

    public function getCreated_at()
    {
        return $this->created_at;
    }

    public function setCreated_at($value)
    {
        $this->created_at = $value;
    }

    public function getUpdated_at()
    {
        return $this->updated_at;
    }

    public function setUpdated_at($value)
    {
        $this->updated_at = $value;
    }
}
