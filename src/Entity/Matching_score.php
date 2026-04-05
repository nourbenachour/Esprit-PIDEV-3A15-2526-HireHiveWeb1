<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
class Matching_score
{

    #[ORM\Id]
    #[ORM\Column(type: "bigint")]
    private string $id_matching;

    #[ORM\Column(type: "bigint")]
    private string $job_offer_id;

    #[ORM\Column(type: "bigint")]
    private string $condidat_id;

    #[ORM\Column(type: "bigint")]
    private string $id_condidature;

    #[ORM\Column(type: "integer")]
    private int $niveau_compatibilite;

    #[ORM\Column(type: "text")]
    private string $recommandations;

    #[ORM\Column(type: "integer")]
    private int $score_matching;

    public function getId_matching()
    {
        return $this->id_matching;
    }

    public function setId_matching($value)
    {
        $this->id_matching = $value;
    }

    public function getJob_offer_id()
    {
        return $this->job_offer_id;
    }

    public function setJob_offer_id($value)
    {
        $this->job_offer_id = $value;
    }

    public function getCondidat_id()
    {
        return $this->condidat_id;
    }

    public function setCondidat_id($value)
    {
        $this->condidat_id = $value;
    }

    public function getId_condidature()
    {
        return $this->id_condidature;
    }

    public function setId_condidature($value)
    {
        $this->id_condidature = $value;
    }

    public function getNiveau_compatibilite()
    {
        return $this->niveau_compatibilite;
    }

    public function setNiveau_compatibilite($value)
    {
        $this->niveau_compatibilite = $value;
    }

    public function getRecommandations()
    {
        return $this->recommandations;
    }

    public function setRecommandations($value)
    {
        $this->recommandations = $value;
    }

    public function getScore_matching()
    {
        return $this->score_matching;
    }

    public function setScore_matching($value)
    {
        $this->score_matching = $value;
    }
}
