<?php

namespace App\Controller\User;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserCommentController extends AbstractController
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
        $this->denyAccessIfGranted('ROLE_UNVUSER');
        $comments = $this->commentRepository->findByAuthor($this->getUser()->getId());

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
        $this->denyAccessIfGranted('ROLE_UNVUSER');
        if ($this->isCsrfTokenValid('comment_deletion_' . $comment->getId(), $request->get('_token'))) {
            $this->em->remove($comment);
            $this->em->flush();
            $this->addFlash('success', 'Your comment has been deleted !');
        }
        return $this->redirectToRoute('user.comments');
    }
}
