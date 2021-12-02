<?php

namespace App\Security;

use App\Entity\Organization\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use App\Repository\Organization\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class CustomAuthenticator extends AbstractAuthenticator
{
    public const LOGIN_ROUTE = 'security_login';
    public const HOME_ROUTE = 'home';

    private $em;
    private $csrfTokenManager;
    private $passwordHasher;
    private $router;
    
    public function __construct(
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrfTokenManager,
        UserPasswordHasherInterface $passwordHasher,
        RouterInterface $router
    ) {
        $this->em = $em;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordHasher = $passwordHasher;
        $this->router = $router;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function authenticate(Request $request): PassportInterface
    {
        $token = new CsrfToken('authenticate', $request->request->get('_csrf_token'));
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            $this->getErrorMessage('invalid_token');
        }

        $username = $request->request->get('username');

        /** @var UserRepository */
        $userRepo = $this->em->getRepository(User::class);
        $user = $userRepo->findUser($username);

        if (!$user) {
            $this->getErrorMessage();
        }

        if ($user->getFailureLoginCount() >= 5) {
            $this->getErrorMessage('blocked_user');
        }
        
        if (!$this->passwordHasher->isPasswordValid($user, $request->request->get('password'))) {
            $user->setFailureLoginCount($user->getFailureLoginCount() + 1);
            $this->em->flush();
            
            $this->getErrorMessage();
        }

        return new SelfValidatingPassport(new UserBadge($username));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->router->generate(self::HOME_ROUTE));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }

        return new RedirectResponse($this->getLoginUrl());
    }
    
    protected function getLoginUrl(): string
    {
        return $this->router->generate(self::LOGIN_ROUTE);
    }

    protected function getErrorMessage($message = 'invalid_credentials', $messageData = []): void
    {
        throw new CustomUserMessageAuthenticationException($message, $messageData);
    }
}