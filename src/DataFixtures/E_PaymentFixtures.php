<?php

namespace App\DataFixtures;

use App\Entity\Support\Payment;
use App\Repository\Support\SupportGroupRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/*
 * @codeCoverageIgnore
 */
class E_PaymentFixtures extends Fixture
{
    private $em;
    private $supportGroupRepo;
    private $faker;

    public function __construct(EntityManagerInterface $em, SupportGroupRepository $supportGroupRepo)
    {
        $this->em = $em;
        $this->supportGroupRepo = $supportGroupRepo;
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function load(ObjectManager $em): void
    {
        foreach ($this->supportGroupRepo->findAll() as $support) {
            for ($i = 0; $i < mt_rand(6, 10); ++$i) {
                $createdAt = AppFixtures::getDateTimeBeetwen('-12 months', 'now');

                $payment = (new Payment())
                ->setType(1)
                ->setMonthContrib(new \DateTime($createdAt->format('Y-m').'-01'))
                ->setResourcesAmt($resourcesAmt = mt_rand(0, 1500))
                ->setToPayAmt($resourcesAmt * 0.1)
                ->setPaidAmt($resourcesAmt * 0.1)
                ->setPaymentDate($createdAt)
                ->setPaymentType(mt_rand(1, 4))
                ->setSupportGroup($support)
                ->setCreatedAt($createdAt)
                ->setCreatedBy($support->getReferent())
                ->setUpdatedAt($createdAt)
                ->setUpdatedBy($support->getReferent());

                $this->em->persist($payment);
            }
        }
        $this->em->flush();
    }
}
