<?php

namespace App\Security;

use App\Repository\Organization\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'security_login';
    public const HOME_ROUTE = 'home';

    private UrlGeneratorInterface $urlGenerator;
    private UserRepository $userRepo;

    public function __construct(UrlGeneratorInterface $urlGenerator, UserRepository $userRepo)
    {
        $this->urlGenerator = $urlGenerator;
        $this->userRepo = $userRepo;
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');

        if (strpos($username, '@')) {
            $user = $this->userRepo->findUser($username);
            $username = $user ? $user->getUsername() : $username;
        }

        $request->getSession()->set(Security::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate(self::HOME_ROUTE));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        /** @var Session $session */
        $session = $request->getSession();
        $session->getFlashBag()->add('danger', $this->getErrorMessage($exception));

        return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    private function getErrorMessage(AuthenticationException $exception): string
    {
        if ($exception instanceof InvalidCsrfTokenException) {
            return 'Le token est invalide.<br/> Veuillez actualiser la page.';
        }

        if ('blocked_user' === $exception->getMessageKey()) {
            return "Ce compte est bloqué suite à de nombreux échecs de connexion.<br/> 
                Veuillez-vous rapprocher d'un administrateur ou réinitialiser votre mot de passe.";
        }

        return 'Login ou mot de passe incorrect.';
    }
}
