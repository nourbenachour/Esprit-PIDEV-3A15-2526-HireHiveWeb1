<?php

namespace App\Entity;

use App\Enum\TypePost;
use App\Enum\Visibility;
use App\Repository\Post\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private ?string $id_post = null;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "posts")]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private ?Users $id_user = null;

    #[Assert\NotBlank(message: "La visibilité est obligatoire.")]
    #[ORM\Column(type: "string", nullable: true)]
    private ?string $visibility = 'PUBLIC';

    #[Assert\NotBlank(message: "Le type de post est obligatoire.")]
    #[ORM\Column(type: "string", nullable: true)]
    private ?string $type = 'TEXT';

    #[Assert\NotBlank(message: "Le titre est obligatoire.")]
    #[Assert\Length(
        min: 3,
        max: 150,
        minMessage: "Le titre doit contenir au moins {{ limit }} caractères.",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    #[ORM\Column(type: "string", length: 150, nullable: true)]
    private ?string $title = null;

    #[Assert\NotBlank(message: "Le contenu est obligatoire.")]
    #[Assert\Length(
        min: 10,
        minMessage: "Le contenu doit contenir au moins {{ limit }} caractères."
    )]
    #[ORM\Column(type: "text", nullable: true)]
    private ?string $content = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $image_url = null;

    #[ORM\Column(type: "boolean")]
    private bool $is_published = true;

    #[ORM\Column(type: "integer")]
    private int $view_count = 0;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $updated_at;

    #[ORM\OneToMany(mappedBy: "post", targetEntity: Comment::class, cascade: ["remove"], orphanRemoval: true)]
    private Collection $comments;

    #[ORM\OneToMany(mappedBy: "post", targetEntity: Reaction::class, cascade: ["remove"], orphanRemoval: true)]
    private Collection $reactions;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->comments = new ArrayCollection();
        $this->reactions = new ArrayCollection();
    }

    public function getId_post(): ?string
    {
        return $this->id_post;
    }

    public function getIdPost(): ?string
    {
        return $this->id_post;
    }

    public function setId_post($value): void
    {
        $this->id_post = $value;
    }

    public function getId_user(): ?Users
    {
        if ($this->id_user === null) {
            return null;
        }

        try {
            if (method_exists($this->id_user, '__load')) {
                $this->id_user->__load();
            } else {
                // Touch a non-identifier field so invalid relations fail here instead of in Twig.
                $this->id_user->getEmail();
            }
        } catch (\Throwable) {
            $this->id_user = null;
        }

        return $this->id_user;
    }

    public function setId_user(?Users $value): void
    {
        $this->id_user = $value;
    }

    public function getVisibility(): ?string
    {
        return $this->visibility;
    }

    public function setVisibility(?string $value): void
    {
        $this->visibility = $value;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $value): void
    {
        $this->type = $value;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $value): void
    {
        $this->title = $value;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $value): void
    {
        $this->content = $value;
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

    public function setImageUrl(?string $value): void
    {
        $this->image_url = $value;
    }

    public function getIs_published(): bool
    {
        return $this->is_published;
    }

    public function isPublished(): bool
    {
        return $this->is_published;
    }

    public function setIs_published(bool $value): void
    {
        $this->is_published = $value;
    }

    public function getView_count(): int
    {
        return $this->view_count;
    }

    public function getViewCount(): int
    {
        return $this->view_count;
    }

    public function setView_count(int $value): void
    {
        $this->view_count = $value;
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

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdated_at(\DateTimeInterface $value): void
    {
        $this->updated_at = $value;
    }

    /** @return Collection<int, Comment> */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setPost($this);
        }
        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }
        return $this;
    }

    /** @return Collection<int, Reaction> */
    public function getReactions(): Collection
    {
        return $this->reactions;
    }

    public function addReaction(Reaction $reaction): self
    {
        if (!$this->reactions->contains($reaction)) {
            $this->reactions[] = $reaction;
            $reaction->setPost($this);
        }
        return $this;
    }

    public function removeReaction(Reaction $reaction): self
    {
        if ($this->reactions->removeElement($reaction)) {
            if ($reaction->getPost() === $this) {
                $reaction->setPost(null);
            }
        }
        return $this;
    }

    /**
     * Helper: count reactions by type
     */
    public function countReactionsByType(string $type): int
    {
        return $this->reactions->filter(fn(Reaction $r) => $r->getReaction_type() === $type)->count();
    }
}
