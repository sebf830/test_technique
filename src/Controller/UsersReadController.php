<?php

declare(strict_types=1);

namespace App\Controller;

use Exception;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class UsersReadController
{
    private UserRepository $userRepository;
    private Security $security;



    public function __construct(UserRepository $userRepository, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    public function __invoke()
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user->hasRole("ROLE_ADMIN")) {
            return new AccessDeniedException("Vous n'avez pas les droits");
        }
        return $this->userRepository->findAll();
    }
}
