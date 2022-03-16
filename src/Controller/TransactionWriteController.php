<?php

declare(strict_types=1);

namespace App\Controller;

use Exception;
use App\Entity\Transaction;
use App\Constante\OffersTypes;
use App\Services\UpdateBalance;
use App\Services\BonusDepository;

class TransactionWriteController
{
    private BonusDepository $bonus;
    private UpdateBalance $balance;

    public function __construct(
        BonusDepository $bonus,
        UpdateBalance $balance,
    ) {
        $this->bonus = $bonus;
        $this->balance = $balance;
    }

    /**
     *  Augmente le montant déposé avec les bonus definis dans le service : DepositoryBonus
     *
     * @param Transaction $data
     * @return Transaction
     */
    public function __invoke(Transaction $data): Transaction
    {
        $depository = $data->getAmount();

        if ($data->getTransactionType() == "credit") {
            $this->checkDepositoryMatchesOffers($depository);
            $data->setAmount($depository);
            $depository += $this->bonus->calc_bonus($depository);
        }

        if ($this->balance->updateBalance($data, $depository)) {
            return $data;
        }
        throw new Exception("Problème avec la création de la transaction, opération abandonnée");
    }


    public function checkDepositoryMatchesOffers(float $depository)
    {
        foreach (OffersTypes::BONUS_BY_RANGE as $key => $value) {
            if ($depository == $key)
                return true;
        }
        throw new Exception("La somme versée ne correspond pas à nos offres");
    }
}
