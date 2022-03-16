<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\Collection;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
#[ApiResource(
    collectionOperations: [
        "get" => [
            'normalization_context' => [
                'groups' => ['company:collection']
            ],
            "openapi_context" => [
                "summary" => "Récuperer la liste des entreprises"
            ]
        ],
    ],
    itemOperations: [
        "get" => [
            'normalization_context' => [
                'groups' => ['company:item'],
            ],
            "controller" => NotFoundAction::class,
            'read' => false,
            'output' => false,
            'openapi_context' => ['summary' => 'hidden'],
        ]
    ]
)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["company:collection", "company:item"])]
    private $id;

    #[Groups(["company:collection", "company:item"])]
    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Wallet::class, orphanRemoval: true)]
    #[Groups(["company:item"])]
    private $wallet;

    public function __construct()
    {
        $this->wallets = new ArrayCollection();
        $this->wallet = new ArrayCollection();
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

    /**
     * @return Collection<int, Wallet>
     */
    public function getWallet(): Collection
    {
        return $this->wallet;
    }

    public function addWallet(Wallet $wallet): self
    {
        if (!$this->wallet->contains($wallet)) {
            $this->wallet[] = $wallet;
            $wallet->setCompany($this);
        }

        return $this;
    }

    public function removeWallet(Wallet $wallet): self
    {
        if ($this->wallet->removeElement($wallet)) {
            // set the owning side to null (unless already changed)
            if ($wallet->getCompany() === $this) {
                $wallet->setCompany(null);
            }
        }

        return $this;
    }
}
