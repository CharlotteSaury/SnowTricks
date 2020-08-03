<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    const ACCESS = 'access';

    protected function supports(string $attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!self::ACCESS) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        /** @var User $user */
        $user = $subject;

        if ($attribute == self::ACCESS) {
                return $this->canAccess($user, $currentUser);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canAccess(User $user, User $currentUser)
    {
        return $user === $currentUser;
    }
}
