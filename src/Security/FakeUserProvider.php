<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class FakeUserProvider implements UserProviderInterface
{
    public function refreshUser(UserInterface $user): UserInterface
    {
        // Ici, vous pouvez recharger l'utilisateur si nécessaire
        // Cela n'est généralement pas nécessaire pour un système d'authentification stateless avec JWT
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        // Retourne true si cette classe est un utilisateur que ce fournisseur de services peut charger
        return $class === FakeUser::class;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $password = $_ENV['PASSWORD'] ?? null;

        if ($password !== null) {
            return new FakeUser($identifier, $password);
        }

        // Si l'utilisateur n'est pas trouvé ou s'il n'a pas de mot de passe, une exception est lancée
        throw new \InvalidArgumentException(sprintf('Utilisateur "%s" non trouvé.', $identifier));
    }
}