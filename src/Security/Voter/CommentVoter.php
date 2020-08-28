<?php

namespace App\Security\Voter;

use App\Entity\Comment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class CommentVoter extends Voter
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    const DELETE = 'delete';

    protected function supports(string $attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (self::DELETE !== $attribute) {
            return false;
        }

        if (!$subject instanceof Comment) {
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
            return true;
        }

        /** @var Comment $comment */
        $comment = $subject;

        if (self::DELETE === $attribute) {
            return $this->canDelete($comment, $user);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canDelete(Comment $comment, User $user)
    {
        return $user === $comment->getAuthor();
    }
}
