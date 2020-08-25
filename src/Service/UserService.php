<?php

namespace App\Service;

use Exception;
use App\Entity\User;
use App\Helper\UploaderHelper;
use App\Helper\ImageFileDeletor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

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

    public function __construct(UploaderHelper $uploaderHelper, ImageFileDeletor $imageFileDeletor, EntityManagerInterface $entityManager)
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->imageFileDeletor = $imageFileDeletor;
        $this->entityManager = $entityManager;
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
        } catch (Exception $exception) {
            throw $exception;
        }
    }
}
