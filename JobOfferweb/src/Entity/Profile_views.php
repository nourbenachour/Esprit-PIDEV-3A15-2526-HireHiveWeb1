<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Users;

#[ORM\Entity]
class Profile_views
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_view;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "profileViewsAsRecruiter")]
    #[ORM\JoinColumn(name: 'recruiter_id', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private Users $recruiter_id;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "profileViewsAsViewer")]
    #[ORM\JoinColumn(name: 'viewer_id', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private Users $viewer_id;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $viewed_at;

    public function getId_view()
    {
        return $this->id_view;
    }

    public function setId_view($value)
    {
        $this->id_view = $value;
    }

    public function getRecruiter_id()
    {
        return $this->recruiter_id;
    }

    public function setRecruiter_id($value)
    {
        $this->recruiter_id = $value;
    }

    public function getViewer_id()
    {
        return $this->viewer_id;
    }

    public function setViewer_id($value)
    {
        $this->viewer_id = $value;
    }

    public function getViewed_at()
    {
        return $this->viewed_at;
    }

    public function setViewed_at($value)
    {
        $this->viewed_at = $value;
    }
}
