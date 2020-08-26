<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Service\CommentService;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    /**
     * @var CommentRepository
     */
    private $commentRepository;

    /**
     * @var CommentService
     */
    private $commentService;

    public function __construct(CommentRepository $commentRepository, CommentService $commentService)
    {
        $this->commentRepository = $commentRepository;
        $this->commentService = $commentService;
    }

    /**
     * Display all comments from loggued user
     * 
     * @Route("/user/comments", name="user.comments")
     * @return Response
     */
    public function userComments() : Response
    {
        $comments = $this->commentRepository->findBy(['author' => $this->getUser()->getId()]);

        return $this->render('comment/comments.html.twig', [
            'comments' => $comments,
            'nav' => 'myComments'
        ]);
    }

    /**
     * Handle comment deletion
     * 
     * @Route("/user/comment/delete{id}", name="user.comment.delete", methods="DELETE")
     * @IsGranted("delete", subject="comment", message="You are not allowed to delete other users comments")
     *
     * @param Request $request
     * @param Comment $comment
     * @return Response
     */
    public function delete(Request $request, Comment $comment) : Response
    {
        if ($this->isCsrfTokenValid('comment_deletion_' . $comment->getId(), $request->get('_token'))) {
            $this->commentService->handleDeleteComment($comment);
            
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
