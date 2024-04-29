<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Classe abstraite rassemblant le code commun aux Entités cac et Lvc
 */
#[ORM\MappedSuperclass] // Indique que toutes les propriétés déclarées sont mappées pour les classes enfants
abstract class Stock
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'SEQUENCE')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    private ?float $opening = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    private ?float $closing = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    private ?float $higher = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    private ?float $lower = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOpening(): ?float
    {
        return $this->opening;
    }

    public function setOpening(float $opening): static
    {
        $this->opening = $opening;

        return $this;
    }

    public function getClosing(): ?float
    {
        return $this->closing;
    }

    public function setClosing(float $closing): static
    {
        $this->closing = $closing;

        return $this;
    }

    public function getHigher(): ?float
    {
        return $this->higher;
    }

    public function setHigher(float $higher): static
    {
        $this->higher = $higher;

        return $this;
    }

    public function getLower(): ?float
    {
        return $this->lower;
    }

    public function setLower(float $lower): static
    {
        $this->lower = $lower;

        return $this;
    }
}
