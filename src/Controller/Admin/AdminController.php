<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\RoleType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\TrickRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin", name="admin.")
 */

class AdminController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var TrickRepository
     */
    private $trickRepository;

    /**
     * @var CommentRepository
     */
    private $commentRepository;

    public function __construct(UserRepository $userRepository, TrickRepository $trickRepository, CommentRepository $commentRepository)
    {
        $this->userRepository = $userRepository;
        $this->trickRepository = $trickRepository;
        $this->commentRepository = $commentRepository;
    }

    /**
     * @Route("/statistics", name="statistics")
     */
    public function statistics()
    {
        $usersNb = count($this->userRepository->findAll());
        $activatedUsers = count($this->userRepository->findVerified(1));
        $tricksNb = count($this->trickRepository->findAll());
        $commentsNb = count($this->commentRepository->findAll());

        return $this->render('admin/statistics.html.twig', [
            'usersNb' => $usersNb,
            'activatedUsers' => $activatedUsers,
            'tricksNb' => $tricksNb,
            'commentsNb' => $commentsNb,
            'nav' => 'statistics'
        ]);
    }

    /**
     * @Route("/users", name="users")
     */
    public function users()
    {
        $verifiedUsers = $this->userRepository->findVerified(1);
        $unverifiedUsers = $this->userRepository->findVerified(0);

        return $this->render('admin/users.html.twig', [
            'verifiedUsers' => $verifiedUsers,
            'unverifiedUsers' => $unverifiedUsers,
            'nav' => 'users'
        ]);
    }

}
