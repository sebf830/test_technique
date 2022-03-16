<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Entity\Wallet;
use App\Constante\TransactionTypes;
use App\Repository\WalletRepository;
use App\Repository\TransactionRepository;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\Core\Validator\Exception\ValidationException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WalletItemController
{
    private WalletRepository $walletRepository;
    private Security $security;

    public function __construct(
        WalletRepository $walletRepository,
        Security $security,
    ) {
        $this->walletRepository = $walletRepository;
        $this->security = $security;
    }

    public function __invoke(Wallet $data, Request $request)
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user->hasRole("ROLE_ADMIN") && $user != $data->getUser()) {
            return new AccessDeniedException("Vous n'avez pas les droits");
        }

        $operation = $request->get('transactionType');

        $validator = Validation::createValidator();
        $context = $validator->startContext();
        $context->atPath('operation')->validate($operation, [
            new Assert\Choice([
                'choices' => [TransactionTypes::CREDIT_ACCOUNT, TransactionTypes::DEBIT_ACCOUNT],
            ]),
        ]);

        if (0 !== count($context->getViolations())) {
            throw new ValidationException("Le type de transaction n'est pas reconnu");
        }
        return $this->walletRepository->getWallet($operation, $user->getId());
    }
}
