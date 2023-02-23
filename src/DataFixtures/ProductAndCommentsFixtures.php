<?php

namespace App\DataFixtures;

use App\Factory\CommentsFactory;
use App\Factory\ProductFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductAndCommentsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $a = ProductFactory::createMany(100);

        CommentsFactory::
        createMany(100, function() use ($a){
            return [
                 'product' => $a[array_rand($a)]
            ];
        });
    }
}
