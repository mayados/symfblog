<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{

    public function __construct(private readonly UserPasswordHasherInterface $hasher){}

    public function load(ObjectManager $manager): void
    {
        $newUser = new User();
        
        $newUser->setNickname('User');
        $newUser->setPassword($this->hasher->hashPassword($newUser,'azerty'));
        $newUser->setEmail('user@gmail.com');
        $newUser->setIsVerified(true);

        $manager->persist($newUser);

        $newUser = new User();
        
        $newUser->setNickname('Admin');
        $newUser->setPassword($this->hasher->hashPassword($newUser,'admin'));
        $newUser->setEmail('admin@gmail.com');
        $newUser->setIsVerified(true);
        $newUser->setRoles(['ROLE_ADMIN']);
        $this->setReference("Admin",$newUser);

        $manager->persist($newUser);

        $manager->flush();
    }
}
