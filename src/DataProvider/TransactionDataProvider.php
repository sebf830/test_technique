<?php

namespace App\DataProvider;

use App\Repository\TransactionRepository;
use Symfony\Component\Security\Core\Security;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use App\Entity\Transaction;

final class TransactionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{

    private TransactionRepository $transactionRepository;
    private Security $security;
    public function __construct(TransactionRepository $transactionRepository, Security $security)
    {
        $this->transactionRepository = $transactionRepository;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Transaction::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $wallets = $user->getWallets();
        $arrayTransaction = [];
        foreach ($wallets as $wallet) {
            foreach ($wallet->getTransactions() as $transaction) {
                $arrayTransaction[] = $transaction;
            }
        }
        return $arrayTransaction;
    }
}
