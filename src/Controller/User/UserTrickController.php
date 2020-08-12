<?php

namespace App\Controller\User;

use App\Entity\Image;
use App\Entity\Trick;
use App\Form\TrickType;
use App\Repository\UserRepository;
use App\Repository\TrickRepository;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;

class UserTrickController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var TrickRepository
     */
    private $trickRepository;

    /**
     * @var FileSystem
     */
    private $fileSystem;

    public function __construct(UserRepository $userRepository, TrickRepository $trickRepository, EntityManagerInterface $entityManager, Filesystem $fileSystem)
    {
        $this->userRepository = $userRepository;
        $this->trickRepository = $trickRepository;
        $this->entityManager = $entityManager;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @Route("/user/trick/new", name="user.trick.new")
     */
    public function new(Request $request, UploaderHelper $uploaderHelper)
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $author = $this->userRepository->findOneByUsername($this->getUser()->getUserName());
            $trick->setAuthor($author);

            $mainImage = $form->get('mainImage')->getData();
            if (!empty($mainImage)) {
                $mainImageName = $uploaderHelper->uploadFile($mainImage, 'tmp_trick');
                $trick->setMainImage($mainImageName);
            }

            $images = $form->get('images')->getData();
            foreach ($images as $image) {
                $imageName = $uploaderHelper->uploadFile($image->getFile(), 'tmp_trick');

                $image->setName($imageName)
                    ->setTrick($trick);
                $trick->addImage($image);
            }

            $videos = $form->get('videos')->getData();
            foreach ($videos as $video) {
                $trick->addVideo($video);
            }

            $this->entityManager->persist($trick);
            $this->entityManager->flush();
            $this->fileSystem->rename($this->getParameter('media_directory') . 'tmp_trick', $this->getParameter('media_directory') . 'trick_' . $trick->getId());
            $this->addFlash('success', 'Your trick is posted !');
            return $this->redirectToRoute('trick.show', [
                'id' => $trick->getId(),
                'slug' => $trick->getSlug()
            ]);
        }

        return $this->render('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/trick/edit{id}", name="user.trick.edit")
     * @IsGranted("edit", subject="trick", message="Access denied")
     */
    public function edit(Trick $trick, Request $request, UploaderHelper $uploaderHelper)
    {
        $author = $trick->getAuthor();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mainImage = $form->get('mainImage')->getData();
            if (!empty($mainImage)) {
                $mainImageName = $uploaderHelper->uploadFile($mainImage, 'trick_' . $trick->getId());
                $trick->setMainImage($mainImageName);
            }

            $images = $form->get('images')->getData();

            foreach ($images as $image) {
                if ($image->getFile() != null) {
                    $imageName = $uploaderHelper->uploadFile($image->getFile(), 'trick_' . $trick->getId());

                    $image->setName($imageName)
                        ->setTrick($trick);
                    $trick->addImage($image);
                }
            }

            $videos = $form->get('videos')->getData();
            foreach ($videos as $video) {
                $trick->addVideo($video);
            }

            $trick->setUpdatedAt(new \DateTime())
                ->setAuthor($author);

            $this->entityManager->persist($trick);
            $this->entityManager->flush();

            // remove deleted image files from uploads folder
            $trickImages = [$trick->getMainImage()];
            foreach ($trick->getImages() as $image) {
                array_push($trickImages, $image->getName());
            }
            $directory = $this->getParameter('media_directory') . 'trick_' . $trick->getId();
            if ($directory) {
                if (opendir($directory)) {
                    foreach (scandir($directory) as $oldImage) {
                        if ($oldImage != '.' && $oldImage != '..') {
                            if (!in_array($oldImage, $trickImages)) {
                                $this->fileSystem->remove($directory . '/' . $oldImage);
                            }
                        }
                    }
                }
            }
             
            if ($author == $this->getUser()) {
                $this->addFlash('success', 'Your trick has been updated !');
            } else {
                $this->addFlash('success', $author->getUsername() . '\'s trick has been updated !');
            }
            
            return $this->redirectToRoute('trick.show', [
                'id' => $trick->getId(),
                'slug' => $trick->getSlug()
            ]);
            
        }

        return $this->render('trick/edit.html.twig', [
            'trick' => $trick,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/trick/delete{id}", name="user.trick.delete", methods="DELETE")
     * @IsGranted("edit", subject="trick", message="Access denied")
     */
    public function delete(Request $request, Trick $trick)
    {
        if ($this->isCsrfTokenValid('trick_deletion_' . $trick->getId(), $request->get('_token'))) {
            $this->entityManager->remove($trick);
            $this->entityManager->flush(); 
        }
        if ($trick->getAuthor() == $this->getUser()) {
            $this->addFlash('success', 'Your trick has been deleted !');
            return $this->redirectToRoute('user.tricks');
        } else {
            $this->addFlash('success', $trick->getAuthor()->getUsername() . '\'s trick has been deleted !');
            return $this->redirectToRoute('trick.index');
        }
        
    }

    /**
     * @Route("user/tricks", name="user.tricks")
     * @return Response
     */
    public function index(): Response
    {    
        $tricks = $this->trickRepository->findBy(['author' => $this->getUser()->getId()]);

        return $this->render('user/tricks.html.twig', [
            'tricks' => $tricks,
            'nav' => 'myTricks'
        ]);
    }

}
