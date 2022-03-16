<?php

declare(strict_types=1);

namespace App\DataPersister;

use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\DataPersisterInterface;

class TransactionDataPersister implements DataPersisterInterface
{
    use PersisterTrait;

    public function supports($data): bool
    {
        return $data instanceof Transaction;
    }

    /**
     * @param [type] $data
     * @return object
     */
    public function persist($data)
    {
        if (!$data->getDate() || $data->getDate()->format("Y-m-d H:i:s") !== date('Y-m-d H:i:s'))
            $data->setDate(new \DateTime(date('Y-m-d H:i:s')));

        $this->save($data);
        return $data;
    }
}
