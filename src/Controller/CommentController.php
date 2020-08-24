<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

        /**
     * @var CommentRepository
     */
    private $commentRepository;

    public function __construct(CommentRepository $commentRepository, EntityManagerInterface $entityManager)
    {
        $this->commentRepository = $commentRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/user/comments", name="user.comments")
     * @return Response
     */
    public function userComments() : Response
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
     * @return Response
     */
    public function delete(Request $request, Comment $comment) : Response
    {
        if ($this->isCsrfTokenValid('comment_deletion_' . $comment->getId(), $request->get('_token'))) {
            $this->entityManager->remove($comment);
            $this->entityManager->flush();
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

    /**
     * Create new comment
     *
     * @param Request $request
     * @param Trick $trick
     * @return Response
     */
    public function new(Request $request, Trick $trick) 
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setAuthor($this->getUser())
                    ->setCreatedAt(new \DateTime())
                    ->setTrick($trick);
            
            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            $this->addFlash('successComment', 'Your comment is posted !');

            return $this->redirect($this->generateUrl('trick.show', [
                '_fragment' => 'trickCommentForm',
                'id' => $trick->getId(),
                'slug' => $trick->getName()
                ]));
        }
        return $form;
    }
}
