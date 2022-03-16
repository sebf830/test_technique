<?php

declare(strict_types=1);

namespace App\Controller;

use Exception;
use App\Entity\Wallet;
use App\Repository\WalletRepository;

class WalletWriteController
{
    private WalletRepository $walletRepository;

    public function __construct(WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }


    public function __invoke(Wallet $data): Wallet
    {
        if ($this->isWalletExists($data))
            throw new Exception('Vous avez déjà crée un portefeuille avec ce commerçant');

        return $data;
    }

    public function isWalletExists(Wallet $data): bool
    {
        if ($this->walletRepository->findOneBy(['company' => $data->getCompany(), 'user' => $data->getUser()]))
            return true;
        return false;
    }
}
