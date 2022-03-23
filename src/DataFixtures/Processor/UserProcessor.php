<?php

namespace App\DataFixtures\Processor;

use App\Entity\Organization\User;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserProcessor implements ProcessorInterface
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Processes an object before it is persisted to DB.
     *
     * @param string $id     Fixture ID
     * @param object $object
     */
    public function preProcess(string $id, $object): void
    {
        if (false === $object instanceof User) {
            return;
        }

        /* @var User $object */
        $object->setPassword($this->passwordHasher->hashPassword($object, $object->getPlainPassword()));
    }

    /**
     * Processes an object after it is persisted to DB.
     *
     * @param string $id     Fixture ID
     * @param object $object
     */
    public function postProcess(string $id, $object): void
    {
        // TODO: Implement postProcess() method.
    }
}
