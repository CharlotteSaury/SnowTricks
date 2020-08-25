<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Form\NewPasswordType;
use App\Form\PasswordFormType;
use App\Form\ResetPasswordType;
use App\Form\RegistrationFormType;
use App\Helper\MailSenderHelper;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
     /**
      * @var UserRepository
      */
    private $userRepository;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var MailSenderHelper
     */
    private $mailSenderHelper;

    public function __construct(UserService $userService, MailSenderHelper $mailSenderHelper, UserRepository $userRepository)
    {
        $this->userService = $userService;
        $this->mailSenderHelper = $mailSenderHelper;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request): Response
    {        
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $token = $this->userService->handleNewUser($user, $form);

            $url = $this->generateUrl('app_verify_email', ['token' => $token]);
            $this->mailSenderHelper->sendMail('account_confirmation', $user, $url);

            $this->addFlash('success', 'A confirmation link has been sent to your email. Please follow the link to activate your account !');
            return $this->redirectToRoute('trick.index');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/{token}", name="app_verify_email")
     */
    public function verifyUserEmail($token): Response
    {
        $user = $this->userRepository->findOneBy(['activationToken' => $token]);
        if (!$user) {
            $this->addFlash('danger', 'Invalid token');
            return $this->redirectToRoute('trick.index');
        }
        $this->userService->handleUserActivation($user);
        $this->addFlash('success', 'Your email address has been verified. Now login to access to all functionalities !');

        return $this->redirectToRoute('trick.index');
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('trick.index');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException();
    }

    /**
     * @Route("/user/reset_password", name="user.resetPass")
     */
    public function reset(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getUser();

        $form = $this->createForm(PasswordFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($passwordEncoder->isPasswordValid($this->getUser(), $form->get('oldPassword')->getData())) {
                $this->userService->handlePasswordUpdate($user, $form->get('plainPassword')->getData());
                
                $this->addFlash('success', 'Your password has been updated !');
                return $this->redirectToRoute('user.resetPass');
            }
            $this->addFlash('danger', 'Your old password is not valid');
            return $this->redirectToRoute('user.resetPass');
        }

        return $this->render('security/reset_password.html.twig', [
            'user' => $user,
            'nav' => 'resetPass',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/forgot_password_link", name="app_forgotten_password")
     */
    public function passwordLink(Request $request, MailerInterface $mailer)
    {
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $data = $form->getData();
            $user = $this->userRepository->findOneByUsername($data['username']);

            if (!$user) {
                $this->addFlash('danger', 'No account is associated with this username.');
                return $this->redirectToRoute('app_forgotten_password');
            }

            $token = $this->userService->handleResetPassword($user);
            $url = $this->generateUrl('app_new_password', ['token' => $token]);
            $this->mailSenderHelper->sendMail('password_reset', $user, $url);

            $this->addFlash('success', 'A confirmation link has been sent to your email. Please follow the link to choose a new password !');
            return $this->redirectToRoute('trick.index');
        }

        return $this->render('security/forgot_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/new_password/{token}", name="app_new_password")
     */
    public function newPassword($token, Request $request)
    {
        $user = $this->userRepository->findOneBy(['resetToken' => $token]);

        if (!$user) {
            $this->addFlash('danger', 'Invalid token');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(NewPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->handlePasswordUpdate($user, $form->get('plainPassword')->getData());
            $this->addFlash('success', 'Your password has been updated !');
            return $this->redirectToRoute('trick.index');
        }

        return $this->render('security/new_password.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}
