<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Questionnaire
{

    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    private int $idQuestionnaire;

    #[ORM\Column(type: "string", length: 255)]
    private string $jobTitle;

    #[ORM\Column(type: "text")]
    private string $questionText;

    #[ORM\Column(type: "text")]
    private string $answerOptions;

    #[ORM\Column(type: "string", length: 255)]
    private string $correctAnswer;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $updated_at;

    public function getIdQuestionnaire()
    {
        return $this->idQuestionnaire;
    }

    public function setIdQuestionnaire($value)
    {
        $this->idQuestionnaire = $value;
    }

    public function getJobTitle()
    {
        return $this->jobTitle;
    }

    public function setJobTitle($value)
    {
        $this->jobTitle = $value;
    }

    public function getQuestionText()
    {
        return $this->questionText;
    }

    public function setQuestionText($value)
    {
        $this->questionText = $value;
    }

    public function getAnswerOptions()
    {
        return $this->answerOptions;
    }

    public function setAnswerOptions($value)
    {
        $this->answerOptions = $value;
    }

    public function getCorrectAnswer()
    {
        return $this->correctAnswer;
    }

    public function setCorrectAnswer($value)
    {
        $this->correctAnswer = $value;
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
