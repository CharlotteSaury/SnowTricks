<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\TrickRepository;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Response;
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
     * Display website statistics (users, tricks, comments)
     * 
     * @Route("/statistics", name="stats")
     * 
     * @return Response
     */
    public function statistics() : Response
    {
        $usersNb = count($this->userRepository->findAll());
        $activatedUsers = count($this->userRepository->findBy(['activationToken' => null]));
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
