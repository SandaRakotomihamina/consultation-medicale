<?php

namespace App\Entity;

use App\Repository\UniteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UniteRepository::class)]
class Unite
{
    #[ORM\Id]
    #[ORM\Column(length: 6)]
    private ?string $CODUTE = null;

    #[ORM\Column(length: 35)]
    private ?string $LIBUTE = null;

    #[ORM\Column(length: 35)]
    private ?string $LOCAL = null;

    public function getCODUTE(): ?string
    {
        return $this->CODUTE;
    }

    public function setCODUTE(string $CODUTE): static
    {
        $this->CODUTE = $CODUTE;

        return $this;
    }

    public function getLIBUTE(): ?string
    {
        return $this->LIBUTE;
    }

    public function setLIBUTE(string $LIBUTE): static
    {
        $this->LIBUTE = $LIBUTE;

        return $this;
    }

    public function getLOCAL(): ?string
    {
        return $this->LOCAL;
    }

    public function setLOCAL(string $LOCAL): static
    {
        $this->LOCAL = $LOCAL;

        return $this;
    }
}
