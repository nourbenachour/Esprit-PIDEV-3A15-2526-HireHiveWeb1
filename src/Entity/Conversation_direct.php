<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Users;
use Doctrine\Common\Collections\Collection;
use App\Entity\Direct_message;

#[ORM\Entity]
class Conversation_direct
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_conversation;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "conversationDirectsLow")]
    #[ORM\JoinColumn(name: 'user_low_id', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private Users $user_low_id;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "conversationDirectsHigh")]
    #[ORM\JoinColumn(name: 'user_high_id', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private Users $user_high_id;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $updated_at;

    public function getId_conversation()
    {
        return $this->id_conversation;
    }

    public function setId_conversation($value)
    {
        $this->id_conversation = $value;
    }

    public function getUser_low_id()
    {
        return $this->user_low_id;
    }

    public function setUser_low_id($value)
    {
        $this->user_low_id = $value;
    }

    public function getUser_high_id()
    {
        return $this->user_high_id;
    }

    public function setUser_high_id($value)
    {
        $this->user_high_id = $value;
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

    #[ORM\OneToMany(mappedBy: "id_conversation", targetEntity: Direct_message::class)]
    private Collection $direct_messages;

        public function getDirect_messages(): Collection
        {
            return $this->direct_messages;
        }
    
        public function addDirect_message(Direct_message $direct_message): self
        {
            if (!$this->direct_messages->contains($direct_message)) {
                $this->direct_messages[] = $direct_message;
                $direct_message->setId_conversation($this);
            }
    
            return $this;
        }
    
        public function removeDirect_message(Direct_message $direct_message): self
        {
            if ($this->direct_messages->removeElement($direct_message)) {
                // set the owning side to null (unless already changed)
                if ($direct_message->getId_conversation() === $this) {
                    $direct_message->setId_conversation(null);
                }
            }
    
            return $this;
        }
}
