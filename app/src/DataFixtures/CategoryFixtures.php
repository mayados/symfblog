<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryFixtures extends Fixture
{

    public function __construct(private readonly SluggerInterface $slugger){}

    public function load(ObjectManager $manager): void
    {

        $categories = [
            [
                'name' => 'France',
                'parent' => null
            ],
            [
                'name' => 'Monde',
                'parent' => null
            ],
            [
                'name' => 'Politique',
                'parent' => "France"
            ],
            [
                'name' => 'Associations',
                'parent' => "France"
            ],
            [
                'name' => 'Economie',
                'parent' => "Monde"
            ]
        ];

        foreach($categories as $category){
            $newCategory = new Category();
            $newCategory->setName($category["name"]);
            
            $slug = strtolower($this->slugger->slug($newCategory->getName()));
            $newCategory->setSlug($slug);

            // create a reference to this category
            $this->setReference($category["name"],$newCategory);

            $parent = null;

            // verify if the category has a parent in the table
            if($category["parent"] !== null){
                $parent = $this->getReference($category["parent"]);
            }

            $newCategory->setParent($parent);

            $manager->persist($newCategory);            
        }

        $manager->flush();
    }
}