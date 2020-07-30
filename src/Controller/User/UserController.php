<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
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
     * @Route("/user/dashboard/{id}", name="user.dashboard")
     */
    public function dashboard(User $user)
    {
        return $this->render('user/dashboard.html.twig', [
            'user' => $user,
            'nav' => 'dashboard'
        ]);
    }

    /**
     * @Route("/user/profile/{username}", name="user.profile")
     */
    public function profile(User $user)
    {
        if ($this->getUser()->getUsername() == $user->getUsername() || $this->isGranted('ROLE_ADMIN')) {
            return $this->render('user/profile.html.twig', [
                'user' => $user,
                'nav' => 'profile'
            ]);
        }
        throw new \Exception("Vous n'avez pas accès à cette page");
    }

    /**
     * @Route("/user/edit/{username}", name="user.edit")
     */
    public function edit(Request $request, User $user)
    {
        if ($this->getUser()->getUsername() == $user->getUsername() || $this->isGranted('ROLE_ADMIN')) {
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->em->persist($user);
                $this->em->flush();
                $this->addFlash('success', 'Your profile has been updated !');

                return $this->redirectToRoute('user.profile', [
                    'username' => $user->getUsername()
                ]);
            }

            return $this->render('user/edit.html.twig', [
                'user' => $user,
                'nav' => 'profile',
                'form' => $form->createView()
            ]);
        }
        throw new \Exception("Vous n'avez pas accès à cette page");
    }
}
