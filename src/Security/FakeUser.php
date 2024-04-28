<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class FakeUser implements UserInterface, PasswordAuthenticatedUserInterface
{

    private string $username;
    private string $password;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
        $this->password =$password;
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
}