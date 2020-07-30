<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Repository\TrickRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * @Route("/admin/statistics", name="admin.statistics")
     */
    public function statistics()
    {
        $usersNb = count($this->userRepository->findAll());
        $activatedUsers = count($this->userRepository->findActivated());
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

}
