<?php

namespace App\DataFixtures;

use App\Entity\Support\Payment;
use App\Entity\Support\SupportGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/*
 * @codeCoverageIgnore
 */
class PaymentFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $objectManager): void
    {
        foreach ($objectManager->getRepository(SupportGroup::class)->findAll() as $support) {
            for ($i = 0; $i < mt_rand(5, 10); ++$i) {
                $createdAt = AppFixtures::getDateTimeBeetwen('-12 months', 'now');
                $user = $support->getReferent();

                $payment = (new Payment())
                    ->setType(1)
                    ->setMonthContrib(new \DateTime($createdAt->format('Y-m').'-01'))
                    ->setResourcesAmt($resourcesAmt = mt_rand(0, 1500))
                    ->setToPayAmt($resourcesAmt * 0.1)
                    ->setPaidAmt($resourcesAmt * 0.1)
                    ->setPaymentDate($createdAt)
                    ->setPaymentType(array_rand(Payment::PAYMENT_TYPES))
                    ->setSupportGroup($support)
                    ->setCreatedAt($createdAt)
                    ->setCreatedBy($user)
                    ->setUpdatedAt($createdAt)
                    ->setUpdatedBy($user)
                ;

                $objectManager->persist($payment);
            }
        }
        $objectManager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SupportFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['payment'];
    }
}
