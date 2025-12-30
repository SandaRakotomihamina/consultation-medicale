<?php

namespace App\Entity;

use App\Repository\DemandeDeConsultationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeDeConsultationRepository::class)]
class DemandeDeConsultation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $Grade = null;

    #[ORM\Column(length: 255)]
    private ?string $Nom = null;

    #[ORM\Column(length: 16)]
    private ?string $Matricule = null;

    #[ORM\Column(length: 35, nullable: true)]
    private ?string $LIBUTE = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $Motif = null;

    #[ORM\Column(length: 255)]
    private ?string $DelivreurDeMotif = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $Date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGrade(): ?string
    {
        return $this->Grade;
    }

    public function setGrade(string $Grade): static
    {
        $this->Grade = $Grade;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->Nom;
    }

    public function setNom(string $Nom): static
    {
        $this->Nom = $Nom;

        return $this;
    }

    public function getMatricule(): ?string
    {
        return $this->Matricule;
    }

    public function setMatricule(string $Matricule): static
    {
        $this->Matricule = $Matricule;

        return $this;
    }

    public function getLIBUTE(): ?string
    {
        return $this->LIBUTE;
    }

    public function setLIBUTE(?string $LIBUTE): static
    {
        $this->LIBUTE = $LIBUTE;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->Motif;
    }

    public function setMotif(string $Motif): static
    {
        $this->Motif = $Motif;

        return $this;
    }

    public function getDelivreurDeMotif(): ?string
    {
        return $this->DelivreurDeMotif;
    }

    public function setDelivreurDeMotif(string $DelivreurDeMotif): static
    {
        $this->DelivreurDeMotif = $DelivreurDeMotif;

        return $this;
    }

    public function getDate(): ?\DateTime
    {
        return $this->Date;
    }

    public function setDate(\DateTime $Date): static
    {
        $this->Date = $Date;

        return $this;
    }
}
