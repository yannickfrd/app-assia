<?php

namespace App\DataFixtures;

use App\Entity\Support\Payment;
use App\Repository\Support\SupportGroupRepository;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

/*
 * @codeCoverageIgnore
 */
class E_PaymentFixtures extends Fixture
{
    public function __construct(EntityManagerInterface $manager, SupportGroupRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $supports = $this->repo->findAll();

        foreach ($supports as $support) {
            for ($i = 0; $i < mt_rand(6, 10); ++$i) {
                $createdAt = AppFixtures::getDateTimeBeetwen('-12 months', 'now');

                $date = new DateTime($createdAt->format('Y-m').'-01');

                $resourcesAmt = mt_rand(0, 1500);
                $paidAmt = $resourcesAmt * 0.1;

                $payment = (new Payment())
                    ->setType(1)
                    ->setMonthContrib($date)
                    ->setResourcesAmt($resourcesAmt)
                    ->setToPayAmt($resourcesAmt * 0.1)
                    ->setPaidAmt($paidAmt)
                    ->setPaymentDate($createdAt)
                    ->setPaymentType(mt_rand(1, 4))
                    ->setSupportGroup($support)
                    ->setCreatedAt($createdAt)
                    ->setCreatedBy($support->getReferent())
                    ->setUpdatedAt($createdAt)
                    ->setUpdatedBy($support->getReferent());

                $this->manager->persist($payment);
            }
        }
        $this->manager->flush();
    }
}
