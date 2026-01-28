<?php

declare(strict_types=1);

namespace App\Auth;

use App\Entity\User;
use App\SplitFairly\CurrentUserInterface;
use Symfony\Bundle\SecurityBundle\Security;

final readonly class CurrentUser implements CurrentUserInterface
{
    public function __construct(private Security $security) {}

    public function getUuid(): string
    {
        return $this
            ->getUser()
            ->getUuid()
            ->toString();
    }

    public function getEmail(): string
    {
        return $this
            ->getUser()
            ->getUserIdentifier();
    }

    private function getUser(): User
    {
        $user = $this->security->getUser();

        assert($user instanceof User, 'Please login first!');

        return $user;
    }
}
