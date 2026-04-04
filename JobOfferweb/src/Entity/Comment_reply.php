<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Users;

#[ORM\Entity]
class Comment_reply
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_reply;

        #[ORM\ManyToOne(targetEntity: Comment::class, inversedBy: "comment_replys")]
    #[ORM\JoinColumn(name: 'comment_id', referencedColumnName: 'id_comment', onDelete: 'CASCADE')]
    private Comment $comment_id;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "commentReplies")]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private Users $id_user;

    #[ORM\Column(type: "text")]
    private string $content;

    #[ORM\Column(type: "boolean")]
    private bool $is_edited;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $updated_at;

    public function getId_reply()
    {
        return $this->id_reply;
    }

    public function setId_reply($value)
    {
        $this->id_reply = $value;
    }

    public function getComment_id()
    {
        return $this->comment_id;
    }

    public function setComment_id($value)
    {
        $this->comment_id = $value;
    }

    public function getId_user()
    {
        return $this->id_user;
    }

    public function setId_user($value)
    {
        $this->id_user = $value;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($value)
    {
        $this->content = $value;
    }

    public function getIs_edited()
    {
        return $this->is_edited;
    }

    public function setIs_edited($value)
    {
        $this->is_edited = $value;
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
