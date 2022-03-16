<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Constante\TransactionTypes;
use App\Repository\TransactionRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\TransactionReadController;
use App\Controller\TransactionWriteController;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
#[
    ApiResource(
        collectionOperations: [
            "getWallets" => [
                'method' => 'GET',
                'path' => 'transactions/read',
                'controller' =>  TransactionReadController::class,
                "openapi_context" => [
                    "summary" => "Recuperer la liste des transactions (role admin)",
                ]
            ],
            "create_one" => [
                'method' => 'POST',
                'path' => 'transactions/create',
                'controller' => TransactionWriteController::class,
                "openapi_context" => [
                    "summary" => "Créer une nouvelle opération (Crédit/Paiement)",
                    "requestBody" => [
                        "content" => [
                            "application/json" => [
                                "schema" => [
                                    'type'       => 'object',
                                    'properties' =>
                                    [
                                        'name'        => [
                                            'type' => 'string',
                                            'example' => 'nom'
                                        ],
                                        'amount'        => [
                                            'type' => 'float',
                                            'example' => '0'
                                        ],
                                        'date'        => [
                                            'type' => 'string',
                                            'example' => '2022-01-22 20:00:00'
                                        ],
                                        'transactionType'        => [
                                            'type' => 'string',
                                            'example' => 'credit'
                                        ],
                                        'wallet'        => [
                                            'type' => 'string',
                                            'example' => 'api/wallets/{un id}'
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        itemOperations: [
            'get' => [
                'controller' => NotFoundAction::class,
                'read' => false,
                'output' => false,
                'openapi_context' => ['summary' => 'hidden'],
            ],
        ],
    ),
]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['transaction:collection', 'wallet:collection', 'wallet:item'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['transaction:collection', 'wallet:collection', 'wallet:item'])]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 60, minMessage: "2 charctères minimum SVP", maxMessage: "60 caractères max")]
    private $name;

    #[ORM\Column(type: 'float')]
    #[Groups(['transaction:collection', 'wallet:collection', 'wallet:item'])]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private $amount;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['transaction:collection', 'wallet:collection', 'wallet:item'])]
    private $date;

    #[ORM\ManyToOne(targetEntity: Wallet::class, inversedBy: 'transactions',)]
    #[Groups(['transaction:collection'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private $wallet;

    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['transaction:collection', 'wallet:collection', 'wallet:item'])]
    #[Assert\NotBlank]
    #[Assert\Choice([TransactionTypes::CREDIT_ACCOUNT, TransactionTypes::DEBIT_ACCOUNT])]
    private $transactionType;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getWallet(): ?Wallet
    {
        return $this->wallet;
    }

    public function setWallet(?Wallet $wallet): self
    {
        $this->wallet = $wallet;

        return $this;
    }

    public function getTransactionType(): ?string
    {
        return $this->transactionType;
    }

    public function setTransactionType(string $transactionType): self
    {
        $this->transactionType = $transactionType;

        return $this;
    }
}
