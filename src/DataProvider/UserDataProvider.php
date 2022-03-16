<?php

namespace App\DataProvider;

use Exception;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;

final class UserDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{

    private UserRepository $userRepository;
    private Security $security;
    public function __construct(UserRepository $userRepository, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return User::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        if ($this->security->getUser())
            return $this->userRepository->findBy(['username' => $this->security->getUser()->getUserIdentifier()]);
        throw new Exception("Vous n'avez pas les droits");
    }
}
