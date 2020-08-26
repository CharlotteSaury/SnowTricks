<?php

namespace App\Service;

use Exception;
use App\Entity\User;
use App\Service\ImageService;
use Symfony\Component\Form\Form;
use Doctrine\ORM\EntityManagerInterface;
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

    public function handleNewUser(User $user, Form $form) 
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
    
    public function handleProfileEdition(User $user, Form $form)
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
