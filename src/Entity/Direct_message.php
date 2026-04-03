<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use App\Entity\Users;

#[ORM\Entity]
class Direct_message
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_message;

    #[ORM\ManyToOne(targetEntity: Conversation_direct::class, inversedBy: "direct_messages")]
    #[ORM\JoinColumn(name: 'id_conversation', referencedColumnName: 'id_conversation', onDelete: 'CASCADE')]
    private Conversation_direct $id_conversation;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "directMessagesSent")]
    #[ORM\JoinColumn(name: 'sender_id', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private Users $sender_id;

    #[ORM\ManyToOne(targetEntity: Users::class, inversedBy: "directMessagesReceived")]
    #[ORM\JoinColumn(name: 'receiver_id', referencedColumnName: 'id_user', onDelete: 'CASCADE')]
    private Users $receiver_id;

    #[ORM\Column(type: "text")]
    private string $content;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $sent_at;

    #[ORM\Column(type: "boolean")]
    private bool $is_read;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $read_at;

    public function getId_message()
    {
        return $this->id_message;
    }

    public function setId_message($value)
    {
        $this->id_message = $value;
    }

    public function getId_conversation()
    {
        return $this->id_conversation;
    }

    public function setId_conversation($value)
    {
        $this->id_conversation = $value;
    }

    public function getSender_id()
    {
        return $this->sender_id;
    }

    public function setSender_id($value)
    {
        $this->sender_id = $value;
    }

    public function getReceiver_id()
    {
        return $this->receiver_id;
    }

    public function setReceiver_id($value)
    {
        $this->receiver_id = $value;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent($value)
    {
        $this->content = $value;
    }

    public function getSent_at()
    {
        return $this->sent_at;
    }

    public function setSent_at($value)
    {
        $this->sent_at = $value;
    }

    public function getIs_read()
    {
        return $this->is_read;
    }

    public function setIs_read($value)
    {
        $this->is_read = $value;
    }

    public function getRead_at()
    {
        return $this->read_at;
    }

    public function setRead_at($value)
    {
        $this->read_at = $value;
    }
}
