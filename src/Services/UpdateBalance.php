<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;

class UpdateBalance
{
    private EntityManagerInterface $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Mets à jour le solde du portefeuille associé à la transaction en cours
     *
     * @param Transaction $data
     * @param integer $amount
     * @return void
     */
    public function updateBalance(Transaction $data, float $amount): bool
    {
        $balance =  $data->getWallet()->getBalance();

        if ($data->getTransactionType() === "credit")
            $balance += $amount;

        if ($data->getTransactionType() === "debit") {
            $balance -= $amount;

            if ($balance < 0) {
                throw new Exception('Solde induffisant, la transaction est annulée');
            }
        }
        $data->getWallet()->setBalance($balance);
        $this->em->flush();
        return true;
    }
}
