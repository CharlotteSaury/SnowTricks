<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use App\Repository\UserRepository;
use App\Service\UserService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    /**
     * @var UserService
     */
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display user tricks and comments number
     * 
     * @IsGranted("access", subject="user", message="Access denied")
     * @Route("/user/dashboard/{username}", name="user.dashboard")
     *
     * @param User $user
     * @param TrickRepository $trickRepository
     * @param CommentRepository $commentRepository
     * @return Response
     */
    public function dashboard(User $user, TrickRepository $trickRepository, CommentRepository $commentRepository) : Response
    {
        $tricksNb = count($trickRepository->findBy(['author' => $user->getId()]));
        $commentsNb = count($commentRepository->findBy(['author' => $user->getId()]));

        return $this->render('user/dashboard.html.twig', [
            'user' => $user,
            'tricksNb' => $tricksNb,
            'commentsNb' => $commentsNb,
            'nav' => 'dashboard'
        ]);
    }

    /**
     * Display user profile
     * 
     * @IsGranted("access", subject="user", message="Access denied")
     * @Route("/user/profile/{username}", name="user.profile")
     *
     * @param User $user
     * @return Response
     */
    public function profile(User $user): Response
    {
        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'nav' => 'profile'
        ]);
    }

    /**
     * Handle user profile edition
     * 
     * @IsGranted("access", subject="user", message="Access denied")
     * @Route("/user/edit/{username}", name="user.edit")
     *
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function edit(Request $request, User $user) : Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->handleProfileEdition($user, $form);
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

    /**
     * Display users list accessible to admin
     * 
     * @IsGranted("ROLE_ADMIN")
     * @Route("/users", name="admin.users")
     *
     * @param UserRepository $userRepository
     * @return Response
     */
    public function users(UserRepository $userRepository): Response
    {
        $verifiedUsers = $userRepository->findBy(['activationToken' => null]);
        $unverifiedUsers = $userRepository->findUnverified();

        return $this->render('admin/users.html.twig', [
            'verifiedUsers' => $verifiedUsers,
            'unverifiedUsers' => $unverifiedUsers,
            'nav' => 'users'
        ]);
    }
}
