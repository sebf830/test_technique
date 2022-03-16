<?php

namespace App\Entity;

use App\Entity\Transaction;
use App\Filter\WalletFilter;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\WalletRepository;
use App\Controller\WalletItemController;
use App\Controller\WalletReadController;
use App\Controller\WalletWriteController;
use ApiPlatform\Core\Annotation\ApiFilter;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WalletRepository::class)]
#[
    ApiResource(
        collectionOperations: [
            "get" => [
                'normalization_context' => [
                    'groups' => ['wallet:collection']
                ],
                "openapi_context" => [
                    "summary" => "Récuperer la liste des portefeuilles (role utilisateur)"
                ]
            ],
            "getWallets" => [
                'method' => 'GET',
                'path' => 'wallets/read',
                'controller' =>  WalletReadController::class,
                "openapi_context" => [
                    "summary" => "Recuperer la liste des portefeuilles (role admin)",
                ]
            ],
            "create_wallet" => [
                'method' => 'POST',
                'path' => 'wallets/create',
                'controller' => WalletWriteController::class,
                // 'normalization_context' => ['groups' => 'wallet:post'],
                "openapi_context" => [
                    "summary" => "Créer un nouveau portefeuille",
                    "requestBody" => [
                        "content" => [
                            "application/json" => [
                                "schema" => [
                                    'type'       => 'object',
                                    'properties' =>
                                    [
                                        'name'        => [
                                            'type' => 'string',
                                            'example' => 'Choisir un nom'
                                        ],
                                        'company' => [
                                            'type' => 'string',
                                            'example' => 'api/companies/{id}'
                                        ],
                                        'user' => [
                                            'type' => 'string',
                                            'example' => 'api/users/{id}'
                                        ],
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ],
        itemOperations: [
            'get' => [
                "path" => "wallets/{id}",
                "controller" => WalletItemController::class,
                "input" => Transaction::class,
                'normalization_context' => [
                    'groups' => ['wallet:item']
                ],
                "openapi_context" => [
                    "summary" => "Récuperer un portefeuille"
                ],
            ]
        ],
        paginationEnabled: true
    ),
]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["wallet:collection", 'wallet:item'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["wallet:collection", 'wallet:item'])]
    #[Assert\NotBlank]
    private $name;

    #[Groups(["wallet:collection", 'wallet:item'])]
    #[ORM\Column(type: 'float', options: ["default" => 0], nullable: true)]
    #[Assert\PositiveOrZero]
    private $balance;

    #[ORM\OneToMany(mappedBy: 'wallet', targetEntity: Transaction::class, orphanRemoval: true)]
    #[Groups(['wallet:collection', 'wallet:item'])]
    #[ApiFilter(WalletFilter::class)]
    private $transactions;

    #[ORM\ManyToOne(targetEntity: Company::class, inversedBy: 'wallet')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["wallet:collection", 'wallet:item'])]
    private $company;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'wallets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["wallet:collection", 'wallet:item'])]
    private $user;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

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

    public function getBalance(): ?float
    {
        return $this->balance;
    }

    public function setBalance(?float $balance): self
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): self
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions[] = $transaction;
            $transaction->setWallet($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): self
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getWallet() === $this) {
                $transaction->setWallet(null);
            }
        }

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
