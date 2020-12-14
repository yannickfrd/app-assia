<?php

namespace App\Security;

use App\Entity\Organization\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'security_login';

    private $manager;
    private $csrfTokenManager;
    private $router;

    public function __construct(
        EntityManagerInterface $manager,
        CsrfTokenManagerInterface $csrfTokenManager,
        UserPasswordEncoderInterface $encoder,
        RouterInterface $router)
    {
        $this->manager = $manager;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->encoder = $encoder;
        $this->router = $router;
    }

    public function supports(Request $request)
    {
        return self::LOGIN_ROUTE === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'username' => $request->request->get('_username'),
            'password' => $request->request->get('_password'),
            'token' => $request->request->get('_csrf_token'),
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            $this->getErrorMessage('invalid_token');
        }

        /** @var UserRepository */
        $repo = $this->manager->getRepository(User::class);
        $user = $repo->findUser($credentials['username']); // $user = $userProvider->loadUserByUsername($credentials['username']);

        try {
            if (!$user) {
                $this->getErrorMessage();
            }

            if ($user->getFailureLoginCount() >= 5) {
                $this->getErrorMessage('blocked_user');
            }

            return $user;
        } catch (UsernameNotFoundException $e) {
            $this->getErrorMessage();
        }
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        $isPasswordValid = $this->encoder->isPasswordValid($user, $credentials['password']);

        if (!$isPasswordValid) {
            $countFails = $user->getFailureLoginCount() + 1;
            $user->setFailureLoginCount($countFails);

            $this->manager->flush();

            $this->getErrorMessage('invalid_password', ['count' => $countFails]);
        }

        return $isPasswordValid;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function getPassword($credentials): ?string
    {
        return $credentials['password'];
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        /** @var User */
        $user = $token->getUser();
        $session = $request->getSession();

        $session->getFlashBag()->add('success', "Bonjour {$user->getFirstname()} !");

        if ($targetPath = $this->getTargetPath($session, $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->getLoginUrl());
    }

    /**
     * Override to change what happens after a bad username/password is submitted.
     *
     * @return RedirectResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);
        }

        return new RedirectResponse($this->getLoginUrl());
    }

    public function supportsRememberMe()
    {
        return true;
    }

    protected function getLoginUrl()
    {
        return $this->router->generate(self::LOGIN_ROUTE);
    }

    protected function getErrorMessage($message = 'invalid_credentials', $messageData = [])
    {
        throw new CustomUserMessageAuthenticationException($message, $messageData);
    }
}
