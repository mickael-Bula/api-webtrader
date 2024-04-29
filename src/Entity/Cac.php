<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Repository\CacRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: CacRepository::class)]
#[ORM\Table(name: "cac")]   // Déclare explicitement la table mappée en raison de l'héritage
class Cac extends Stock
{
    // La propriété createdAt n'a pas le même format pour les classes Cac et Lvc
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'd/m/Y'])]
    protected ?\DateTimeInterface $createdAt = null;

    // Toutes les autres propriétés sont déclarées dans la classe parente

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
