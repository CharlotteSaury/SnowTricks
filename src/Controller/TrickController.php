<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\TrickRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    /**
     * @var TrickRepository
     */
    private $trickRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    public function __construct(TrickRepository $trickRepository, UserRepository $userRepository)
    {
        $this->trickRepository = $trickRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/", name="trick.index")
     * @return Response
     */
    public function index(): Response
    {    
        $tricks = $this->trickRepository->findAll();
        return $this->render('trick/index.html.twig', [
            'tricks' => $tricks
        ]);
    }

    /**
     * @Route("/trick{id}/{slug}", name="trick.show")
     * @return Response
     */
    public function show($id, Request $request): Response
    {
        $trick = $this->trickRepository->find($id);

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $author = $this->userRepository->findOneByUsername($this->getUser()->getUserName());
            $comment->setAuthor($author)
                    ->setCreatedAt(new \DateTime())
                    ->setTrick($trick);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            $this->addFlash('successComment', 'Your comment is posted !');

            return $this->redirect($this->generateUrl('trick.show', [
                '_fragment' => 'trickCommentForm',
                'id' => $id,
                'slug' => $trick->getName()
                ]));
        }
        
        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'form' => $form->createView()
        ]);
    }

}
