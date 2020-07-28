<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var TrickRepository
     */
    private $trickRepository;

    public function __construct(UserRepository $userRepository, TrickRepository $trickRepository, EntityManagerInterface $em)
    {
        $this->userRepository = $userRepository;
        $this->trickRepository = $trickRepository;
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
}
