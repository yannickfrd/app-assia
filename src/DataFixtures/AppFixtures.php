<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function __construct()
    {
    }

    public function load(ObjectManager $manager)
    {
    }

    public static function getDateTimeBeetwen($startEnd, $endDate = 'now')
    {
        $faker = \Faker\Factory::create('fr_FR');

        return $faker->dateTimeBetween($startEnd, $endDate, $timezone = null);
    }

    public static function getStartDate($date)
    {
        $interval = (new \DateTime())->diff($date);
        $days = $interval->days;

        return '-'.$days.' days';
    }
}
