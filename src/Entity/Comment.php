<?php

namespace App\Entity;

use App\Repository\Post\CommentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private ?string $id_comment = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private ?Users $user = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: "comments")]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id_post', onDelete: 'CASCADE')]
    private ?Post $post = null;

    #[Assert\NotBlank(message: "Le commentaire ne peut pas être vide.")]
    #[Assert\Length(
        min: 2,
        max: 2000,
        minMessage: "Le commentaire doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le commentaire ne peut pas dépasser {{ limit }} caractères."
    )]
    #[ORM\Column(type: "text", nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: "boolean")]
    private bool $is_edited = false;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $updated_at;

    #[ORM\Column(type: "string", length: 500, nullable: true)]
    private ?string $image_url = null;

    #[ORM\OneToMany(mappedBy: "comment_id", targetEntity: Comment_reply::class)]
    private Collection $comment_replys;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->comment_replys = new ArrayCollection();
    }

    public function getId_comment(): ?string
    {
        return $this->id_comment;
    }

    public function getIdComment(): ?string
    {
        return $this->id_comment;
    }

    public function setId_comment($value): void
    {
        $this->id_comment = $value;
    }

    // ---- User relation ----
    public function getUser(): ?Users
    {
        if ($this->user === null) {
            return null;
        }

        try {
            if (method_exists($this->user, '__load')) {
                $this->user->__load();
            } else {
                // Touch a non-identifier field so invalid relations fail here instead of in Twig.
                $this->user->getEmail();
            }
        } catch (\Throwable) {
            $this->user = null;
        }

        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;
        return $this;
    }

    /** Legacy accessor to keep compatibility */
    public function getId_user(): ?Users
    {
        return $this->getUser();
    }

    public function setId_user(?Users $value): self
    {
        $this->user = $value;
        return $this;
    }

    // ---- Post relation ----
    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(?Post $post): self
    {
        $this->post = $post;
        return $this;
    }

    /** Legacy accessor */
    public function getPost_id(): ?Post
    {
        return $this->post;
    }

    public function setPost_id(?Post $value): self
    {
        $this->post = $value;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $value): void
    {
        $this->content = $value;
    }

    public function getIs_edited(): bool
    {
        return $this->is_edited;
    }

    public function isEdited(): bool
    {
        return $this->is_edited;
    }

    public function setIs_edited(bool $value): void
    {
        $this->is_edited = $value;
    }

    public function getCreated_at(): \DateTimeInterface
    {
        return $this->created_at;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreated_at(\DateTimeInterface $value): void
    {
        $this->created_at = $value;
    }

    public function getUpdated_at(): \DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdated_at(\DateTimeInterface $value): void
    {
        $this->updated_at = $value;
    }

    public function getImage_url(): ?string
    {
        return $this->image_url;
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImage_url(?string $value): void
    {
        $this->image_url = $value;
    }

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
            if ($comment_reply->getComment_id() === $this) {
                $comment_reply->setComment_id(null);
            }
        }
        return $this;
    }
}
