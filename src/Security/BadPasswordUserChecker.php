<?php

namespace App\Security;

use App\Entity\Organization\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class BadPasswordUserChecker implements UserCheckerInterface
{
    private $request;
    private $passwordHasher;
    private $em;

    public function __construct(RequestStack $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em)
    {
        $this->request = $request;
        $this->passwordHasher = $passwordHasher;
        $this->em = $em;
    }

    /**
     * @param User $user
     */
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getFailureLoginCount() >= 5) {
            throw new CustomUserMessageAuthenticationException('blocked_user');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $this->request->getMainRequest()->request->get('password'))) {
            $user->setFailureLoginCount($user->getFailureLoginCount() + 1);
            $this->em->flush();
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
