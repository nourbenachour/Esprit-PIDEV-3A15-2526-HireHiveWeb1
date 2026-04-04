<?php

namespace App\Entity;

use App\Repository\Post\ReactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReactionRepository::class)]
class Reaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private ?string $id_reaction = null;

    #[ORM\ManyToOne(targetEntity: Users::class)]
    #[ORM\JoinColumn(name: 'id_user', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private ?Users $user = null;

    #[ORM\ManyToOne(targetEntity: Post::class, inversedBy: "reactions")]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id_post', onDelete: 'CASCADE')]
    private ?Post $post = null;

    #[Assert\NotBlank(message: "Le type de réaction est obligatoire.")]
    #[ORM\Column(type: "string", nullable: true)]
    private ?string $reaction_type = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    public function __construct()
    {
        $this->created_at = new \DateTime();
    }

    public function getId_reaction(): ?string
    {
        return $this->id_reaction;
    }

    public function getIdReaction(): ?string
    {
        return $this->id_reaction;
    }

    public function setId_reaction($value): void
    {
        $this->id_reaction = $value;
    }

    // ---- User relation ----
    public function getUser(): ?Users
    {
        if ($this->user === null) {
            return null;
        }

        try {
            $this->user->getEmail();
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

    /** Legacy accessor */
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

    public function getReaction_type(): ?string
    {
        return $this->reaction_type;
    }

    public function getReactionType(): ?string
    {
        return $this->reaction_type;
    }

    public function setReaction_type(?string $value): void
    {
        $this->reaction_type = $value;
    }

    public function setReactionType(?string $value): void
    {
        $this->reaction_type = $value;
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
}
