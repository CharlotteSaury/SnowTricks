<?php

namespace App\Controller\User;

use App\Entity\Image;
use App\Entity\Trick;
use App\Form\TrickType;
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

    public function __construct(UserRepository $userRepository, EntityManagerInterface $em)
    {
        $this->userRepository = $userRepository;
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
            foreach($images as $image) {
                $imageName = md5(uniqid()) . '.' . $image->getFile()->guessExtension();
                $image->getFile()->move($this->getParameter('media_directory'), $imageName);

                $image->setName($imageName)
                    ->setTrick($trick);
                $trick->addImage($image);
            }

            $this->em->persist($trick);
            $this->em->flush();
            $this->addFlash('success', 'Your trick is posted !');
            return $this->redirectToRoute('trick.index');
        }

        return $this->render('user/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView()
        ]);
    }
}
