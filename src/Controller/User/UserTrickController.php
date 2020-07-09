<?php

namespace App\Controller\User;

use App\Entity\Trick;
use App\Form\TrickType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserTrickController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;   
    }
    /**
     * @Route("/user/trick/new", name="user.trick.new")
     */
    public function new(Request $request)
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $this->em->persist($trick);
            $this->em->flush();
            return $this->redirectToRoute('trick.index');
        }

        return $this->render('user/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView()
        ]);
    }
}
