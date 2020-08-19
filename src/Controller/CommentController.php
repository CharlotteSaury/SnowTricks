<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

        /**
     * @var CommentRepository
     */
    private $commentRepository;

    public function __construct(CommentRepository $commentRepository, EntityManagerInterface $em)
    {
        $this->commentRepository = $commentRepository;
        $this->em = $em;
    }

    /**
     * @Route("/user/comments", name="user.comments")
     */
    public function userComments()
    {
        $comments = $this->commentRepository->findBy(['author' => $this->getUser()->getId()]);

        return $this->render('user/comments.html.twig', [
            'comments' => $comments,
            'nav' => 'myComments'
        ]);
    }

    /**
     * @Route("/user/comment/delete{id}", name="user.comment.delete", methods="DELETE")
     * @IsGranted("delete", subject="comment", message="You are not allowed to delete other users comments")
     */
    public function delete(Request $request, Comment $comment)
    {
        if ($this->isCsrfTokenValid('comment_deletion_' . $comment->getId(), $request->get('_token'))) {
            $this->em->remove($comment);
            $this->em->flush();
            if ($comment->getAuthor() == $this->getUser()) {
                $this->addFlash('successComment', 'Your comment has been deleted !');
            } else {
                $this->addFlash('successComment', $comment->getAuthor()->getUsername() . '\'s comment has been deleted !');
            }
        }
        return $this->redirectToRoute('trick.show', [
            'id' => $comment->getTrick()->getId(),
            'slug' => $comment->getTrick()->getSlug(),
            '_fragment' => 'trickCommentForm',
            ]);
    }
}
