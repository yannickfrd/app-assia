<?php

namespace App\Tests\Entity;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

trait AssertHasErrorsTrait
{
    public function assertHasErrors(object $object, int $nbErrors = 0): void
    {
        self::bootKernel();
        $errors = KernelTestCase::getContainer()->get('validator')->validate($object); // Valide l'objet et donne les erreurs

        // Récupère l'ensemble des erreurs
        $messages = [];
        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath().' => '.$error->getMessage();
        }

        $this->assertCount($nbErrors, $errors, implode(', ', $messages));
    }
}
