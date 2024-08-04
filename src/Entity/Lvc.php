<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Repository\LvcRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: LvcRepository::class)]
#[ORM\Table(name: "lvc")]   // Déclare explicitement la table mappée en raison de l'héritage
class Lvc extends Stock
{
    // NOTE : La propriété createdAt peut avoir un format différent pour les classes Cac et Lvc
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'd/m/Y'])]     // Faire attention au format reçu
    protected ?\DateTimeInterface $createdAt = null;

    // Toutes les propriétés sont déclarées dans la classe parente

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
