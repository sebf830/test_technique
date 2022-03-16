<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Company;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    private UserPasswordHasherInterface $hasher;
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User;
        $user->setUsername('john_doe@gmail.com');
        $user->setPassword($this->hasher->hashPassword($user, 'password'));

        $companies = ['Boulangerie A', 'Boulangerie B', 'Croissanterie C', 'Chocolatinerie D'];
        for ($i = 0; $i < count($companies); $i++) {
            $company = (new Company)->setName($companies[$i]);
            $manager->persist($company);
        }
        $manager->persist($user);
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['groupDev'];
    }
}
