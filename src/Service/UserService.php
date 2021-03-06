<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class UserService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var TokenGeneratorInterface
     */
    private $tokenGenerator;

    /**
     * @var ImageService
     */
    private $imageService;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, TokenGeneratorInterface $tokenGenerator, ImageService $imageService)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenGenerator = $tokenGenerator;
        $this->imageService = $imageService;
    }

    /**
     * Handle new user creation.
     *
     * @return String $token
     */
    public function handleNewUser(User $user, FormInterface $form)
    {
        try {
            $user->setPassword(
                $this->passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $token = $this->tokenGenerator->generateToken();
            $user->setActivationToken($token);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $token;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Handle user profile edition.
     *
     * @return void
     */
    public function handleProfileEdition(User $user, FormInterface $form)
    {
        try {
            $this->imageService->handleAvatarEdition($user, $form);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->imageService->handleAvatarFileDeletion($user, $form);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Handle activation token deletion after user activation by activation link.
     *
     * @return void
     */
    public function handleUserActivation(User $user)
    {
        try {
            $user->setActivationToken(null);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Handle password update.
     *
     * @return void
     */
    public function handlePasswordUpdate(User $user, string $password)
    {
        try {
            $user->setPassword(
            $this->passwordEncoder->encodePassword($user, $password)
            );
            $user->setResetToken(null);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Handle reset password token generation.
     *
     * @return String $token
     */
    public function handleResetPassword(User $user)
    {
        try {
            $token = $this->tokenGenerator->generateToken();
            $user->setResetToken($token);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $token;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
