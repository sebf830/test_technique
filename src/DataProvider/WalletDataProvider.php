<?php

namespace App\DataProvider;

use App\Entity\Wallet;
use App\Repository\WalletRepository;
use Symfony\Component\Security\Core\Security;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;

final class WalletDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private WalletRepository $walletRepository;
    private Security $security;
    public function __construct(WalletRepository $walletRepository, Security $security)
    {
        $this->walletRepository = $walletRepository;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Wallet::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        return $this->walletRepository->findBy(['user' => $this->security->getUser()]);
    }
}
