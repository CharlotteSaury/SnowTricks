<?php

namespace App\Controller\User;

use App\Entity\Image;
use App\Entity\Trick;
use App\Form\TrickType;
use App\Repository\UserRepository;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
            return $this->redirectToRoute('user.tricks');
        }

        return $this->render('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/trick/edit{id}", name="user.trick.edit")
     */
    public function edit(Trick $trick, Request $request)
    {
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
            $this->addFlash('success', 'Your trick has been updated !');

            return $this->redirectToRoute('user.tricks');
        }

        return $this->render('trick/edit.html.twig', [
            'trick' => $trick,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/trick/delete{id}", name="user.trick.delete", methods="DELETE")
     */
    public function delete(Request $request, Trick $trick)
    {
        if ($this->isCsrfTokenValid('trick_deletion_' . $trick->getId(), $request->get('_token'))) {
            $this->em->remove($trick);
            $this->em->flush();
            $this->addFlash('success', 'Your trick has been deleted !');
        }
        return $this->redirectToRoute('trick.index');
    }

    /**
     * @Route("user/tricks", name="user.tricks")
     * @return Response
     */
    public function index(): Response
    {    
        $tricks = $this->trickRepository->findByAuthor($this->getUser()->getId());

        return $this->render('user/tricks.html.twig', [
            'tricks' => $tricks,
            'nav' => 'myTricks'
        ]);
    }

}
