<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use App\Helper\ImageFileDeletor;
use App\Helper\UploaderHelper;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @IsGranted("access", subject="user", message="Access denied")
     * @Route("/user/dashboard/{username}", name="user.dashboard")
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
     * @IsGranted("access", subject="user", message="Access denied")
     * @Route("/user/profile/{username}", name="user.profile")
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
     * @IsGranted("access", subject="user", message="Access denied")
     * @Route("/user/edit/{username}", name="user.edit")
     * @return Response
     */
    public function edit(Request $request, User $user, UploaderHelper $uploaderHelper, ImageFileDeletor $imageFileDeletor) : Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $avatar = $form->get('avatar')->getData();
            if (!empty($avatar)) {
                $avatarName = $uploaderHelper->uploadFile($avatar, 'users', 'user_' . $user->getId());
                $user->setAvatar($avatarName);
            }
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            if (!empty($avatar)) {
                $imageFileDeletor->deleteFile('user', $user->getId(), [$avatarName]);
            }
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
     * @IsGranted("ROLE_ADMIN")
     * @Route("/users", name="admin.users")
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
