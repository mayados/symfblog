<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostFixtures extends Fixture implements DependentFixtureInterface
{

    public function __construct(private readonly SluggerInterface $slugger){}

    public function load(ObjectManager $manager): void
    {
        $newPost = new Post();
        $newPost->setUser($this->getReference('Admin'));
        $newPost->setTitle("Ceci est un titre");
        $newPost->setSlug(strtolower($this->slugger->slug($newPost->getTitle())));
        $newPost->setContent("Ceci est le contenu");
        $newPost->setFeaturedImage('default.webp');
        $manager->persist($newPost);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
