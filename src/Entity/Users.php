<?php

namespace App\Entity;

use App\Entity\Job_notification;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: \App\Repository\UserRepository::class)]
class Users implements UserInterface, PasswordAuthenticatedUserInterface
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
    private ?string $id_user = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $email = '';

    #[ORM\Column(type: "string", length: 100)]
    private string $first_name = '';

    #[ORM\Column(type: "string", length: 100)]
    private string $last_name = '';

    #[ORM\Column(type: "string", length: 500)]
    private string $profile_picture = '';

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $password = '';

    #[ORM\Column(type: "string")]
    private string $role = 'USER';

    #[ORM\Column(type: "boolean")]
    private bool $is_active = false;

    #[ORM\Column(type: "string")]
    private string $status = '';

    #[ORM\Column(name: "created_at", type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(name: "updated_at", type: "datetime")]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->condidats = new ArrayCollection();
        $this->passwordResetTokens = new ArrayCollection();
        $this->posts = new ArrayCollection();
        $this->recruiters = new ArrayCollection();
        $this->commentReplies = new ArrayCollection();
        $this->conversationDirectsLow = new ArrayCollection();
        $this->conversationDirectsHigh = new ArrayCollection();
        $this->profileViewsAsRecruiter = new ArrayCollection();
        $this->profileViewsAsViewer = new ArrayCollection();
        $this->savedJobOffers = new ArrayCollection();
        $this->directMessagesSent = new ArrayCollection();
        $this->directMessagesReceived = new ArrayCollection();
        $this->jobNotifications = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTime();
    }

    public function getId_user(): ?string
    {
        return $this->id_user;
    }

    public function getId(): ?string
    {
        return $this->id_user;
    }

    public function setId_user($value): void
    {
        $this->id_user = $value;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail($value): void
    {
        $this->email = $value;
    }

    public function getFirst_name()
    {
        return $this->first_name;
    }

    public function setFirst_name($value)
    {
        $this->first_name = $value;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name ?? null;
    }

    public function setFirstName(?string $value): void
    {
        $this->first_name = $value ?? '';
    }

    public function getLast_name()
    {
        return $this->last_name;
    }

    public function setLast_name($value)
    {
        $this->last_name = $value;
    }

    public function getLastName(): ?string
    {
        return $this->last_name ?? null;
    }

    public function setLastName(?string $value): void
    {
        $this->last_name = $value ?? '';
    }

    public function getProfile_picture()
    {
        return $this->profile_picture;
    }

    public function setProfile_picture($value)
    {
        $this->profile_picture = $value;
    }

    public function getPhone(): string
    {
        return $this->phone ?? '';
    }

    public function setPhone(?string $value): void
    {
        $this->phone = $value;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword($value): void
    {
        $this->password = $value;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole($value): void
    {
        $this->role = $value;
    }

    /**
     * UserInterface implementation.
     */
    public function getRoles(): array
    {
        $role = strtoupper($this->role ?? 'USER');
        $symfonyRole = str_starts_with($role, 'ROLE_') ? $role : 'ROLE_'.$role;

        // Ensure at least ROLE_USER
        $roles = ['ROLE_USER'];
        if ($symfonyRole !== 'ROLE_USER') {
            $roles[] = $symfonyRole;
        }

        return array_values(array_unique($roles));
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /** @deprecated since Symfony 5.3 */
    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    public function eraseCredentials(): void
    {
        // No temporary sensitive data stored.
    }

    public function getIs_active(): bool
    {
        return $this->is_active;
    }

    public function setIs_active(bool $value): void
    {
        $this->is_active = $value;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $value): void
    {
        $this->createdAt = $value;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $value): void
    {
        $this->updatedAt = $value;
    }

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Condidat::class)]
    private Collection $condidats;

    public function getCondidats(): Collection
    {
        return $this->condidats;
    }

    public function addCondidat(Condidat $condidat): self
    {
        if (!$this->condidats->contains($condidat)) {
            $this->condidats[] = $condidat;
            $condidat->setUser_id($this);
        }

        return $this;
    }

    public function removeCondidat(Condidat $condidat): self
    {
        if ($this->condidats->removeElement($condidat)) {
            if ($condidat->getUser_id() === $this) {
                $condidat->setUser_id(null);
            }
        }

        return $this;
    }

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Password_reset_tokens::class)]
    private Collection $passwordResetTokens;

    #[ORM\OneToMany(mappedBy: "id_user", targetEntity: Post::class)]
    private Collection $posts;

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setId_user($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            if ($post->getId_user() === $this) {
                $post->setId_user(null);
            }
        }

        return $this;
    }

    #[ORM\OneToMany(mappedBy: "user_id", targetEntity: Recruiter::class)]
    private Collection $recruiters;

    #[ORM\OneToMany(mappedBy: "id_user", targetEntity: Comment_reply::class)]
    private Collection $commentReplies;

    #[ORM\OneToMany(mappedBy: "user_low_id", targetEntity: Conversation_direct::class)]
    private Collection $conversationDirectsLow;

    public function getConversationDirectsLow(): Collection
    {
        return $this->conversationDirectsLow;
    }

    #[ORM\OneToMany(mappedBy: "user_high_id", targetEntity: Conversation_direct::class)]
    private Collection $conversationDirectsHigh;

    public function getConversationDirectsHigh(): Collection
    {
        return $this->conversationDirectsHigh;
    }

    #[ORM\OneToMany(mappedBy: "recruiter_id", targetEntity: Profile_views::class)]
    private Collection $profileViewsAsRecruiter;

    public function getProfileViewsAsRecruiter(): Collection
    {
        return $this->profileViewsAsRecruiter;
    }

    #[ORM\OneToMany(mappedBy: "viewer_id", targetEntity: Profile_views::class)]
    private Collection $profileViewsAsViewer;

    public function getProfileViewsAsViewer(): Collection
    {
        return $this->profileViewsAsViewer;
    }

    #[ORM\OneToMany(mappedBy: "candidate_id", targetEntity: Saved_job_offer::class)]
    private Collection $savedJobOffers;

    public function getSavedJobOffers(): Collection
    {
        return $this->savedJobOffers;
    }

    #[ORM\OneToMany(mappedBy: "sender_id", targetEntity: Direct_message::class)]
    private Collection $directMessagesSent;

    public function getDirectMessagesSent(): Collection
    {
        return $this->directMessagesSent;
    }

    #[ORM\OneToMany(mappedBy: "receiver_id", targetEntity: Direct_message::class)]
    private Collection $directMessagesReceived;

    public function getDirectMessagesReceived(): Collection
    {
        return $this->directMessagesReceived;
    }

    #[ORM\OneToMany(mappedBy: "candidate_id", targetEntity: Job_notification::class)]
    private Collection $jobNotifications;

    public function getJobNotifications(): Collection
    {
        return $this->jobNotifications;
    }
}
