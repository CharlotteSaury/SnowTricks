<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Trick;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TrickVoter extends Voter
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    const EDIT = 'edit';
    const REPORT = 'report';

    protected function supports(string $attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [self::EDIT, self::REPORT])) {
            return false;
        }

        if (!$subject instanceof Trick) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN') || $this->security->isGranted('ROLE_MODERATOR')) {
            if ($attribute != self::REPORT) {
                return true;
            }  
        }

        /** @var Trick $trick */
        $trick = $subject;

        if (in_array($attribute, [self::EDIT, self::REPORT])) {
            return $this->canEdit($trick, $user);             
        } 

        throw new \LogicException('This code should not be reached!');
    }

    private function canEdit(Trick $trick, User $user)
    {
        return $user === $trick->getAuthor();
    }
}
