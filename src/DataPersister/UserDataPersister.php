<?php

declare(strict_types=1);

namespace App\DataPersister;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserDataPersister implements DataPersisterInterface
{

    use PersisterTrait;

    public function supports($data): bool
    {
        return $data instanceof User;
    }

    /**
     * @param [type] $data
     * @return object
     */
    public function persist($data)
    {
        if ($data->getPassword()) {
            $data->setPassword($this->hasher->hashPassword($data, $data->getPassword()));
            $data->eraseCredentials();
        }

        $this->save($data);
        return $data;
    }
}
