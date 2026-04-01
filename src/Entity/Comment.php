<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use App\Entity\Comment_reply;

#[ORM\Entity]
class Comment
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_comment;

    #[ORM\Column(type: "bigint")]
    private string $id_user;

    #[ORM\Column(type: "bigint")]
    private string $post_id;

    #[ORM\Column(type: "text")]
    private string $content;

    #[ORM\Column(type: "boolean")]
    private bool $is_edited;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $updated_at;

    #[ORM\Column(type: "string", length: 500)]
    private string $image_url;

    public function getId_comment()
    {
        return $this->id_comment;
    }

    public function setId_comment($value)
    {
        $this->id_comment = $value;
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

    public function getImage_url()
    {
        return $this->image_url;
    }

    public function setImage_url($value)
    {
        $this->image_url = $value;
    }

    #[ORM\OneToMany(mappedBy: "comment_id", targetEntity: Comment_reply::class)]
    private Collection $comment_replys;

        public function getComment_replys(): Collection
        {
            return $this->comment_replys;
        }
    
        public function addComment_reply(Comment_reply $comment_reply): self
        {
            if (!$this->comment_replys->contains($comment_reply)) {
                $this->comment_replys[] = $comment_reply;
                $comment_reply->setComment_id($this);
            }
    
            return $this;
        }
    
        public function removeComment_reply(Comment_reply $comment_reply): self
        {
            if ($this->comment_replys->removeElement($comment_reply)) {
                // set the owning side to null (unless already changed)
                if ($comment_reply->getComment_id() === $this) {
                    $comment_reply->setComment_id(null);
                }
            }
    
            return $this;
        }
}
