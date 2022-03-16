<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\WalletRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WalletReadController
{
    private WalletRepository $walletRepository;
    private Security $security;


    public function __construct(WalletRepository $walletRepository, Security $security)
    {
        $this->walletRepository = $walletRepository;
        $this->security = $security;
    }

    public function __invoke()
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user->hasRole("ROLE_ADMIN")) {
            return new AccessDeniedException("Vous n'avez pas les droits");
        }
        return $this->walletRepository->findAll();
    }
}
