<?php

/**
 * NovaeZ2FABundle.
 *
 * @package   NovaeZ2FABundle
 *
 * @author    Yassine HANINI
 * @copyright 2021 AlmaviaCX
 * @license   https://github.com/Novactive/NovaeZ2FA/blob/main/LICENSE
 */

declare(strict_types=1);

namespace Novactive\Bundle\eZ2FABundle\Security;

use eZ\Publish\Core\MVC\Symfony\Security\User;
use Novactive\Bundle\eZ2FABundle\Core\UserRepository;
use Novactive\Bundle\eZ2FABundle\Entity\UserGoogleAuthSecret;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class TwoFactorUserProviderDecorator implements UserProviderInterface
{
    /**
     * @var UserProviderInterface
     */
    private $provider;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(UserProviderInterface $provider, UserRepository $userRepository)
    {
        $this->provider = $provider;
        $this->userRepository = $userRepository;
    }

    public function loadUserByUsername(string $username)
    {
        $user = $this->provider->loadUserByUsername($username);

        if ($user instanceof User) {
            $results = $this->userRepository->getUserGoogleAuthSecretByUserId(
                $user->getAPIUserReference()->getUserId()
            );

            if (false === $results) {
                return $user;
            }

            return new UserGoogleAuthSecret(
                $user->getAPIUser(),
                $user->getRoles(),
                $results['secret'] ?? null
            );
        }

        return $user;
    }

    public function loadUserByIdentifier(string $identifier)
    {
        return $this->loadUserByUsername($identifier);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->provider->refreshUser($user);
    }

    public function supportsClass(string $class): bool
    {
        return $this->provider->supportsClass($class);
    }
}
