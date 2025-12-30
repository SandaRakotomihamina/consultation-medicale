<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PersonnelRepository;

#[ORM\Entity(repositoryClass: PersonnelRepository::class)]
class Personnel
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 255)]
    private string $matricule;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $grade = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 35, nullable: true)]
    private ?string $LIBUTE = null;

    public function getMatricule(): string
    {
        return $this->matricule;
    }

    public function setMatricule(string $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getGrade(): ?string
    {
        return $this->grade;
    }

    public function setGrade(string $grade): self
    {
        $this->grade = $grade;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

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
