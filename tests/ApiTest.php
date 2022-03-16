<?php

namespace App\Tests;

use App\Entity\User;
use App\Entity\Wallet;
use App\Entity\Company;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ApiTest extends ApiTestCase
{
    public const OFFERS_TESTS = [20 => 1, 50 => 2.5, 100 => 10];
    public const DEBITS_TESTS = [200, 500, 450.99, 1000, 2650, 3540.50, 5500, 10000];
    public const CREDITS_TESTS = [5, 20.50, 55, 101, 235, 500, 1440.60];

    public function getClient()
    {
        return self::createClient();
    }

    public function getManager()
    {
        return self::getContainer()->get('doctrine')->getManager();
    }

    protected function createEntities(): void
    {
        $user = new User;
        $user->setUsername('user@gmail.com');
        $user->setPassword('$2y$13$seIyXCc5KqcoNZozYPQdcur2AxoBiwmYgFJ9YCjmLfPGpvD.J/Oq6'); // 'password'
        $this->getManager()->persist($user);

        $company = new Company();
        $company->setName('company-test');
        $this->getManager()->persist($company);

        $this->getManager()->flush();
    }

    public function testBadCredentialsAuthentication()
    {
        $responseAuth = $this->getClient()->request('POST', '/api/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['username' => 'bademail@gmail.com', 'password' => 'badPassword',]
        ]);
        $this->assertResponseStatusCodeSame(401, "JWT Token not found");
    }

    public function testBadRouteAuthentication()
    {
        $responseAuth = $this->getClient()->request('POST', '/api/jwt_authentication', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['username' => 'user@gmail.com', 'password' => 'password',]
        ]);
        $this->assertResponseStatusCodeSame(404, "No route found for 'POST http://localhost:8253/api/jwt_authentication'");
    }

    public function testNoAuthorizationGET()
    {
        $response = $this->getClient()->request('GET', "/api/companies?page=1", [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
        ]);
        $this->assertResponseStatusCodeSame(401, "JWT Token not found");
    }

    public function testNoAuthorizationPOST()
    {
        $response = $this->getClient()->request('POST', '/api/wallets/create', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                "name" => "walletFail",
                "company" => "api/companies/1",
                "user" => "api/users/1"
            ],
        ]);
        $this->assertResponseStatusCodeSame(401, "JWT Token not found");
    }

    public function testCanCreateUserWithoutAuthorization()
    {
        $response = $this->getClient()->request('POST', '/api/users', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                "username" => "newUser@gmail.com",
                "password" => "newPassword",
            ],
        ]);
        $this->assertResponseIsSuccessful(201);
    }

    public function testJwtAutentication(): void
    {
        $this->createEntities();

        $responseAuth = $this->getClient()->request('POST', '/api/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['username' => 'user@gmail.com', 'password' => 'password',]
        ]);

        $this->assertResponseStatusCodeSame(200, "Get JWT token");
        $this->assertArrayHasKey('token', $responseAuth->toArray());
    }

    public function getJWTToken(): string
    {
        $responseAuth = $this->getClient()->request('POST', '/api/login_check', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => ['username' => 'user@gmail.com', 'password' => 'password',]
        ]);
        return $responseAuth->toArray()['token'];
    }

    public function testCompanyRead(): void
    {
        $response = $this->getClient()->request('GET', "/api/companies?page=1", [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'bearer ' . $this->getJWTToken()
            ],
        ]);

        $this->assertResponseIsSuccessful(200);
        $this->assertEquals("company-test", $response->toArray()['hydra:member'][0]['name']);
    }

    public function testWalletWrite(): void
    {
        $response = $this->getClient()->request('POST', '/api/wallets/create', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'bearer ' . $this->getJWTToken()
            ],
            'json' => [
                "name" => "wallet",
                "company" => "api/companies/1",
                "user" => "api/users/1"
            ],
        ]);
        $this->assertResponseIsSuccessful(201);
        $this->assertEquals($response->toArray()['name'], "wallet");
        $this->assertEquals($response->toArray()['company'], "/api/companies/1");
        $this->assertEquals($response->toArray()['user'], "/api/users/1");

        $wallet = $this->getManager()->getRepository(Wallet::class)->findOneBy(['id' => 1]);

        $this->assertEquals(1, $wallet->getId());
        $this->assertEquals("wallet", $wallet->getName());
    }

    public function testTransactionWrite(): void
    {
        foreach (self::OFFERS_TESTS as $depository => $bonus) {
            $response = $this->getClient()->request('POST', '/api/transactions/create', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'bearer ' . $this->getJWTToken()
                ],
                'json' => [
                    'name' => 'Transaction-' . $depository,
                    'amount' => $depository,
                    "date" => "2022-03-14T11:45:02+00:00",
                    "transactionType" => "credit",
                    "wallet" => 'api/wallets/1',
                ],
            ]);

            $this->assertResponseIsSuccessful(201);
            $this->assertEquals($response->toArray()['name'], 'Transaction-' . $depository);
            $this->assertEquals($response->toArray()['amount'], $depository);
        }

        $wallet = $this->getManager()->getRepository(Wallet::class)->findOneBy(['id' => 1]);

        $this->assertEquals($wallet->getBalance(), 183.5);
        $this->assertEquals(count($wallet->getTransactions()), 3);
    }

    public function testCanCreateManyShopWalletWithSameSeller()
    {
        $response = $this->getClient()->request('POST', '/api/wallets/create', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'bearer ' . $this->getJWTToken()
            ],
            'json' => [
                "name" => "wallet",
                "company" => "api/companies/1",
                "user" => "api/users/1"
            ],
        ]);
        $this->assertResponseStatusCodeSame(200, "Vous avez déjà crée un portefeuille avec ce commerçant");
    }

    public function testCanHaveNegativeBalance()
    {
        for ($i = 0; $i < count(self::DEBITS_TESTS); $i++) {
            $response = $this->getClient()->request('POST', '/api/transactions/create', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'bearer ' . $this->getJWTToken()
                ],
                'json' => [
                    'name' => 'Payment-' . $i,
                    'amount' => self::DEBITS_TESTS[$i],
                    "date" => "2022-03-14T11:45:02+00:00",
                    "transactionType" => "debit",
                    "wallet" => 'api/wallets/1',
                ],
            ]);
            $this->assertResponseStatusCodeSame(200, "Solde induffisant, la transaction est annulée");
        }
    }

    public function testCanMakeDepositoryNotMatchingOffers()
    {
        foreach (self::CREDITS_TESTS as $credit) {
            $response = $this->getClient()->request('POST', '/api/transactions/create', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'bearer ' . $this->getJWTToken()
                ],
                'json' => [
                    'name' => 'Credit de ' . $credit . 'euros',
                    'amount' => $credit,
                    "date" => "2022-03-14T11:45:02+00:00",
                    "transactionType" => "credit",
                    "wallet" => 'api/wallets/1',
                ],
            ]);
            $this->assertResponseStatusCodeSame(200, "La somme versée ne correspond pas à nos offres");
        }
    }
}
