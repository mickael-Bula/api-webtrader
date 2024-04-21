<?php

namespace App\Entity;

use DateTime;
use App\Repository\CacRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: CacRepository::class)]
class Cac
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'd/m/Y'])]
    private ?\DateTimeInterface $createdAt = null;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
