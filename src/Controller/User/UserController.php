<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use App\Helper\ImageFileDeletor;
use App\Helper\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @IsGranted("access", subject="user", message="Access denied")
 */
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
     * @Route("/user/dashboard/{username}", name="user.dashboard")
     */
    public function dashboard(User $user, TrickRepository $trickRepository, CommentRepository $commentRepository)
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
     * @Route("/user/profile/{username}", name="user.profile")
     */
    public function profile(User $user)
    {
        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'nav' => 'profile'
        ]);
    }

    /**
     * @Route("/user/edit/{username}", name="user.edit")
     */
    public function edit(Request $request, User $user, UploaderHelper $uploaderHelper, ImageFileDeletor $imageFileDeletor)
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $avatar = $form->get('avatar')->getData();
            if (!empty($avatar)) {
                $avatarName = $uploaderHelper->uploadFile($avatar, 'users', 'user_' . $user->getId());
                $user->setAvatar($avatarName);
            }
            $this->em->persist($user);
            $this->em->flush();
            
            $imageFileDeletor->deleteFile('user', $user->getId(), [$avatarName]);

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
}
