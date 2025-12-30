<?php

namespace App\Entity;

use App\Repository\ConsultationListRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsultationListRepository::class)]
class ConsultationList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $Date = null;

    #[ORM\Column(length: 255)]
    private ?string $Grade = null;

    #[ORM\Column(length: 255)]
    private ?string $Nom = null;

    #[ORM\Column(length: 16)]
    private ?string $Matricule = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $Motif = null;

    #[ORM\Column(length: 255)]
    private ?string $DelivreurDeMotif = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $Observation = null;

    #[ORM\Column(length: 255)]
    private ?string $DelivreurDObservation = null;

    #[ORM\Column(length: 16, nullable: true)]
    private ?string $Repos = null;

    #[ORM\Column(type: 'string', length: 35, nullable: true)]
    private ?string $LIBUTE = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $Exemption = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $debutExemption = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $finExemption = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $Adrresse = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $PATC = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

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

    public function getObservation(): ?string
    {
        return $this->Observation;
    }

    public function setObservation(string $Observation): static
    {
        $this->Observation = $Observation;

        return $this;
    }

    public function getDelivreurDObservation(): ?string
    {
        return $this->DelivreurDObservation;
    }

    public function setDelivreurDObservation(string $DelivreurDObservation): static
    {
        $this->DelivreurDObservation = $DelivreurDObservation;

        return $this;
    }

    public function getRepos(): ?string
    {
        return $this->Repos;
    }

    public function setRepos(?string $Repos): static
    {
        $this->Repos = $Repos;

        return $this;
    }

    public function getExemption(): ?array
    {
        return $this->Exemption;
    }

    public function setExemption(?array $Exemption): static
    {
        $this->Exemption = $Exemption;

        return $this;
    }

    public function getDebutExemption(): ?\DateTime
    {
        return $this->debutExemption;
    }

    public function setDebutExemption(?\DateTime $debutExemption): static
    {
        $this->debutExemption = $debutExemption;

        return $this;
    }

    public function getFinExemption(): ?\DateTime
    {
        return $this->finExemption;
    }

    public function setFinExemption(?\DateTime $finExemption): static
    {
        $this->finExemption = $finExemption;

        return $this;
    }

    public function getAdrresse(): ?array
    {
        return $this->Adrresse;
    }

    public function setAdrresse(?array $Adrresse): static
    {
        $this->Adrresse = $Adrresse;

        return $this;
    }

    public function getPATC(): ?int
    {
        return $this->PATC;
    }

    public function setPATC(?int $PATC): static
    {
        $this->PATC = $PATC;

        return $this;
    }

        public function getLIBUTE(): ?string
    {
        return $this->LIBUTE;
    }

    public function setLIBUTE(?string $LIBUTE): self
    {
        $this->LIBUTE = $LIBUTE;

        return $this;
    }
}
