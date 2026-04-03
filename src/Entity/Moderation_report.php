<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Moderation_report
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_report;

    #[ORM\Column(type: "bigint")]
    private string $id_user;

    #[ORM\Column(type: "bigint")]
    private string $post_id;

    #[ORM\Column(type: "bigint")]
    private string $comment_id;

    #[ORM\Column(type: "string", length: 500)]
    private string $comment_text;

    #[ORM\Column(type: "float")]
    private float $toxicity_score;

    #[ORM\Column(type: "string")]
    private string $status;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    public function getId_report()
    {
        return $this->id_report;
    }

    public function setId_report($value)
    {
        $this->id_report = $value;
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

    public function getComment_id()
    {
        return $this->comment_id;
    }

    public function setComment_id($value)
    {
        $this->comment_id = $value;
    }

    public function getComment_text()
    {
        return $this->comment_text;
    }

    public function setComment_text($value)
    {
        $this->comment_text = $value;
    }

    public function getToxicity_score()
    {
        return $this->toxicity_score;
    }

    public function setToxicity_score($value)
    {
        $this->toxicity_score = $value;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($value)
    {
        $this->status = $value;
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
