<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\TransactionRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TransactionReadController
{
    private TransactionRepository $transactionRepository;
    private Security $security;

    public function __construct(TransactionRepository $transactionRepository, Security $security)
    {
        $this->transactionRepository = $transactionRepository;
        $this->security = $security;
    }

    public function __invoke()
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user->hasRole("ROLE_ADMIN")) {
            return new AccessDeniedException("Vous n'avez pas les droits");
        }
        return $this->transactionRepository->findAll();
    }
}
