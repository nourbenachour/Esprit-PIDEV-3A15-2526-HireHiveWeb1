<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Notification
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_notification;

    #[ORM\Column(type: "bigint")]
    private string $recipient_user_id;

    #[ORM\Column(type: "bigint")]
    private string $actor_user_id;

    #[ORM\Column(type: "bigint")]
    private string $post_id;

    #[ORM\Column(type: "string")]
    private string $type;

    #[ORM\Column(type: "string")]
    private string $reaction_type;

    #[ORM\Column(type: "string", length: 255)]
    private string $comment_preview;

    #[ORM\Column(type: "boolean")]
    private bool $is_read;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    public function getId_notification()
    {
        return $this->id_notification;
    }

    public function setId_notification($value)
    {
        $this->id_notification = $value;
    }

    public function getRecipient_user_id()
    {
        return $this->recipient_user_id;
    }

    public function setRecipient_user_id($value)
    {
        $this->recipient_user_id = $value;
    }

    public function getActor_user_id()
    {
        return $this->actor_user_id;
    }

    public function setActor_user_id($value)
    {
        $this->actor_user_id = $value;
    }

    public function getPost_id()
    {
        return $this->post_id;
    }

    public function setPost_id($value)
    {
        $this->post_id = $value;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($value)
    {
        $this->type = $value;
    }

    public function getReaction_type()
    {
        return $this->reaction_type;
    }

    public function setReaction_type($value)
    {
        $this->reaction_type = $value;
    }

    public function getComment_preview()
    {
        return $this->comment_preview;
    }

    public function setComment_preview($value)
    {
        $this->comment_preview = $value;
    }

    public function getIs_read()
    {
        return $this->is_read;
    }

    public function setIs_read($value)
    {
        $this->is_read = $value;
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
