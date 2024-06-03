<?php

namespace App\DataFixtures;

use App\Entity\Keyword;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class KeywordFixtures extends Fixture
{

    public function __construct(private readonly SluggerInterface $slugger){}

    public function load(ObjectManager $manager): void
    {

        $keywords = [
            "France", "Politique", "Animaux", "Monde", "Informatique", "Arts"
        ];

        foreach($keywords as $keyword){
            $newKeyword = new Keyword();
            $newKeyword->setName($keyword);
            
            $slug = strtolower($this->slugger->slug($newKeyword->getName()));
            $newKeyword->setSlug($slug);

            $manager->persist($newKeyword);            
        }

        $manager->flush();
    }
}