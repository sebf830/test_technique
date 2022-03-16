<?php

declare(strict_types=1);

namespace App\Services;

use App\Constante\OffersTypes;

class BonusDepository
{
    public function calc_bonus($depository): float
    {
        foreach (OffersTypes::BONUS_BY_RANGE as $depository_amount => $bonus_range) {
            if ($depository == $depository_amount)
                return $bonus_range;
        }
        return 0;
    }
}
