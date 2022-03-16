<?php

declare(strict_types=1);

namespace App\DataPersister;

use App\Entity\Wallet;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use Symfony\Bridge\Doctrine\Logger\DbalLogger;

class WalletDataPersister implements DataPersisterInterface
{
    use PersisterTrait;

    public function supports($data): bool
    {
        return $data instanceof Wallet;
    }

    /**
     * @param [type] $data
     * @return object
     */
    public function persist($data)
    {
        if (null === $data->getBalance())
            $data->setBalance(0);

        $this->save($data);
        return $data;
    }
}
