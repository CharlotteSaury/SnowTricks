<?php

namespace App\Service;

use Exception;
use App\Entity\User;
use App\Helper\UploaderHelper;
use App\Helper\ImageFileDeletor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class UserService
{
    /**
     * @var UploaderHelper
     */
    private $uploaderHelper;

    /**
     * @var ImageFileDeletor
     */
    private $imageFileDeletor;

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

    public function __construct(UploaderHelper $uploaderHelper, ImageFileDeletor $imageFileDeletor, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, TokenGeneratorInterface $tokenGenerator)
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->imageFileDeletor = $imageFileDeletor;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenGenerator = $tokenGenerator;
    }

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
    
    public function handleProfileEdition(User $user, FormInterface $form)
    {
        try {
            $avatar = $form->get('avatar')->getData();
            if (!empty($avatar)) {
                $avatarName = $this->uploaderHelper->uploadFile($avatar, 'users', 'user_' . $user->getId());
                $user->setAvatar($avatarName);
            }
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            if (!empty($avatar)) {
                $this->imageFileDeletor->deleteFile('user', $user->getId(), [$avatarName]);
            }
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
