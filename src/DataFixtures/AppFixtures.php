<?php

namespace App\DataFixtures;

use App\Entity\Events;
use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {

            $member = new Team();
            $member->setFirstname($faker->firstName);
            $member->setLastname($faker->lastName);
            $member->setDescription($faker->text);
            $manager->persist($member);
        }

        $manager->flush();
    }

}
