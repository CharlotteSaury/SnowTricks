<?php

namespace App\Controller\User;

use App\Entity\Image;
use App\Entity\Trick;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function new(Request $request)
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $author = $this->userRepository->findOneByUsername($this->getUser()->getUserName());
            $trick->setAuthor($author);

            $mainImage = $form->get('mainImage')->getData();
            if (!empty($mainImage)) {
                $mainImageName = md5(uniqid()) . '.' . $mainImage->guessExtension();
                $mainImage->move($this->getParameter('media_directory'), $mainImageName);
                $trick->setMainImage($mainImageName);
            }

            $images = $form->get('images')->getData();
            foreach ($images as $image) {
                $imageName = md5(uniqid()) . '.' . $image->getFile()->guessExtension();
                $image->getFile()->move($this->getParameter('media_directory'), $imageName);

                $image->setName($imageName)
                    ->setTrick($trick);
                $trick->addImage($image);
            }

            $videos = $form->get('videos')->getData();
            foreach ($videos as $video) {
                $trick->addVideo($video);
            }

            $this->em->persist($trick);
            $this->em->flush();
            $this->addFlash('success', 'Your trick is posted !');
            return $this->redirectToRoute('trick.index');
        }

        return $this->render('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/trick/edit{id}", name="user.trick.edit")
     */
    public function edit($id, Request $request)
    {
        $trick = $this->trickRepository->find($id);
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {
            $mainImage = $form->get('mainImage')->getData();
            if (!empty($mainImage)) {
                $mainImageName = md5(uniqid()) . '.' . $mainImage->guessExtension();
                $mainImage->move($this->getParameter('media_directory'), $mainImageName);
                $trick->setMainImage($mainImageName);
            }

            $images = $form->get('images')->getData();
            foreach ($images as $image) {
                if ($image->getFile() != null) {
                    $imageName = md5(uniqid()) . '.' . $image->getFile()->guessExtension();
                    $image->getFile()->move($this->getParameter('media_directory'), $imageName);

                    $image->setName($imageName)
                        ->setTrick($trick);
                    $trick->addImage($image);
                }
            }

            $videos = $form->get('videos')->getData();
            foreach ($videos as $video) {
                $trick->addVideo($video);
            }

            $trick->setUpdatedAt(new \DateTime());

            $this->em->persist($trick);
            $this->em->flush();
            $this->addFlash('success', 'Your trick has been modified !');

            return $this->redirectToRoute('trick.show', [
                'id' => $id,
                'slug' => $trick->getName()
            ]);
        }


        return $this->render('trick/edit.html.twig', [
            'trick' => $trick,
            'form' => $form->createView()
        ]);
    }
}
