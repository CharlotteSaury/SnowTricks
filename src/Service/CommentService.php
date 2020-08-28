<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CommentService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Handle new comment creation in database.
     *
     * @return void
     */
    public function handleNewComment(Comment $comment, Trick $trick, User $author)
    {
        try {
            $comment->setAuthor($author)
            ->setCreatedAt(new DateTime())
            ->setTrick($trick);

            $this->entityManager->persist($comment);
            $this->entityManager->flush();
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Handle comment deletion in database.
     *
     * @return void
     */
    public function handleDeleteComment(Comment $comment)
    {
        try {
            $this->entityManager->remove($comment);
            $this->entityManager->flush();
        } catch (Exception $exception) {
            throw $exception;
        }
    }
}
