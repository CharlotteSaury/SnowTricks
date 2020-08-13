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

class UserTrickController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var TrickRepository
     */
    private $trickRepository;

    public function __construct(UserRepository $userRepository, TrickRepository $trickRepository, EntityManagerInterface $em)
    {
        $this->userRepository = $userRepository;
        $this->trickRepository = $trickRepository;
        $this->em = $em;
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
                $mainImageName = $uploaderHelper->uploadFile($mainImage);
                $trick->setMainImage($mainImageName);
            }

            $images = $form->get('images')->getData();
            foreach ($images as $image) {
                $imageName = $uploaderHelper->uploadFile($image->getFile());

                $image->setName($imageName)
                    ->setTrick($trick);
                $trick->addImage($image);
            }

            $videos = $form->get('videos')->getData();
            foreach ($videos as $video) {
                if (preg_match('#(?:(?:youtube\.com|youtu\.be))(?:\/(?:[\w\-]+\?v=|embed\/)?)([\w\-]+)(?:\S+)?$#', $video->getLink(), $matches)) {
                    $video->setName('https://www.youtube.com/embed/' . $matches[1]);
                } elseif (preg_match('#(?:(?:dai\.ly|dailymotion\.com))(?:\/(?:video\/|embed\/video\/)?)([\w\-]+)(?:\S+)?$#', $video->getLink(), $matches)) {
                    $video->setName('https://www.dailymotion.com/embed/video/' . $matches[1]);
                } elseif (preg_match('#(?:(?:vimeo\.com|player\.vimeo\.com))(?:\/(?:video\/)?)([\w\-]+)(?:\S+)?$#', $video->getLink(), $matches)) {
                    $video->setName('https://player.vimeo.com/video/' . $matches[1]);
                }
                $trick->addVideo($video);
            }

            $this->em->persist($trick);
            $this->em->flush();
            $this->addFlash('success', 'Your trick is posted !');
            return $this->redirectToRoute('user.tricks');
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
                $mainImageName = $uploaderHelper->uploadFile($mainImage);
                $trick->setMainImage($mainImageName);
            }

            $images = $form->get('images')->getData();

            foreach ($images as $image) {
                if ($image->getFile() != null) {
                    $imageName = $uploaderHelper->uploadFile($image->getFile());

                    $image->setName($imageName)
                        ->setTrick($trick);
                    $trick->addImage($image);
                }
            }

            $videos = $form->get('videos')->getData();
            foreach ($videos as $video) {
                if (preg_match('#(?:(?:youtube\.com|youtu\.be))(?:\/(?:[\w\-]+\?v=|embed\/)?)([\w\-]+)(?:\S+)?$#', $video->getLink(), $matches)) {
                    $video->setName('https://www.youtube.com/embed/' . $matches[1]);
                } elseif (preg_match('#(?:(?:dai\.ly|dailymotion\.com))(?:\/(?:video\/|embed\/video\/)?)([\w\-]+)(?:\S+)?$#', $video->getLink(), $matches)) {
                    $video->setName('https://www.dailymotion.com/embed/video/' . $matches[1]);
                } elseif (preg_match('#(?:(?:vimeo\.com|player\.vimeo\.com))(?:\/(?:video\/)?)([\w\-]+)(?:\S+)?$#', $video->getLink(), $matches)) {
                    $video->setName('https://player.vimeo.com/video/' . $matches[1]);
                }
                $trick->addVideo($video);
            }

            $trick->setUpdatedAt(new \DateTime())
                ->setAuthor($author);

            $this->em->persist($trick);
            $this->em->flush();

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
            $this->em->remove($trick);
            $this->em->flush(); 
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
