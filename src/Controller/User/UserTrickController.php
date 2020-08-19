<?php

namespace App\Controller\User;

use DateTime;
use App\Entity\Trick;
use App\Form\TrickType;
use App\Entity\ReportedTrick;
use App\Form\ReportedTrickType;
use App\Repository\UserRepository;
use App\Repository\TrickRepository;
use Symfony\Component\Mime\Address;
use App\Helper\ImageFileDeletor;
use App\Helper\UploaderHelper;
use App\Helper\VideoLinkFormatter;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReportedTrickRepository;
use App\Helper\ReportedTrickGenerator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
     * @var ReportedTrickRepository
     */
    private $reportedTrickRepository;

    /**
     * @var FileSystem
     */
    private $fileSystem;

    public function __construct(UserRepository $userRepository, TrickRepository $trickRepository, EntityManagerInterface $entityManager, Filesystem $fileSystem, ReportedTrickRepository $reportedTrickRepository)
    {
        $this->userRepository = $userRepository;
        $this->trickRepository = $trickRepository;
        $this->reportedTrickRepository = $reportedTrickRepository;
        $this->entityManager = $entityManager;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @Route("/user/trick/new", name="user.trick.new")
     */
    public function new(Request $request, UploaderHelper $uploaderHelper, VideoLinkFormatter $videoLinkFormatter)
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $author = $this->userRepository->findOneByUsername($this->getUser()->getUserName());
            $trick->setAuthor($author);

            $mainImage = $form->get('mainImage')->getData();
            if (!empty($mainImage)) {
                $mainImageName = $uploaderHelper->uploadFile($mainImage, 'tricks', 'trick_');
                $trick->setMainImage($mainImageName);
            }

            $images = $form->get('images')->getData();
            foreach ($images as $image) {
                $imageName = $uploaderHelper->uploadFile($image->getFile(), 'tricks', 'trick_');

                $image->setName($imageName)
                    ->setTrick($trick);
                $trick->addImage($image);
            }

            $videos = $form->get('videos')->getData();
            foreach ($videos as $video) {
                $formattedName = $videoLinkFormatter->format($video->getLink());
                $video->setName($formattedName);
                $trick->addVideo($video);
            }

            $this->entityManager->persist($trick);
            $this->entityManager->flush();

            $this->fileSystem->rename($this->getParameter('trick_media_directory'), $this->getParameter('trick_media_directory') . $trick->getId());
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
    public function edit(Trick $trick, Request $request, UploaderHelper $uploaderHelper, ImageFileDeletor $imageFileDeletor, VideoLinkFormatter $videoLinkFormatter)
    {
        $author = $trick->getAuthor();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mainImage = $form->get('mainImage')->getData();
            if (!empty($mainImage)) {
                $mainImageName = $uploaderHelper->uploadFile($mainImage, 'tricks', 'trick_' . $trick->getId());
                $trick->setMainImage($mainImageName);
            }

            $images = $form->get('images')->getData();

            foreach ($images as $image) {
                if ($image->getFile() != null) {
                    $imageName = $uploaderHelper->uploadFile($image->getFile(), 'tricks', 'trick_' . $trick->getId());

                    $image->setName($imageName)
                        ->setTrick($trick);
                    $trick->addImage($image);
                }
            }

            $videos = $form->get('videos')->getData();
            foreach ($videos as $video) {
                $formattedName = $videoLinkFormatter->format($video->getLink());
                $video->setName($formattedName);
                $trick->addVideo($video);
            }

            $trick->setUpdatedAt(new \DateTime())
                ->setAuthor($author);

            $this->entityManager->persist($trick);
            $this->entityManager->flush();

            $trickImages = [$trick->getMainImage()];
            foreach ($trick->getImages() as $image) {
                array_push($trickImages, $image->getName());
            }
            $imageFileDeletor->deleteFile('trick', $trick->getId(), $trickImages);


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
     * @Route("/user/trick/deleteMainImage{id}", name="user.trick.delete.mainImage", methods="DELETE")
     * @IsGranted("edit", subject="trick", message="Access denied")
     */
    public function deleteMainImage(Trick $trick, Request $request, ImageFileDeletor $imageFileDeletor)
    {
        if ($this->isCsrfTokenValid('mainImage_deletion_' . $trick->getId(), $request->get('_token'))) {
            $imageFileDeletor->deleteFile('trick', $trick->getId(), [$trick->getMainImage()], true);

            $trick->setMainImage(null);
            $this->entityManager->persist($trick);
            $this->entityManager->flush();
        }

        $this->addFlash('success', 'Main image has been deleted !');
        return $this->redirectToRoute('user.trick.edit', [
            'id' => $trick->getId()
        ]);
    }

    /**
     * @Route("/user/trick/delete{id}", name="user.trick.delete", methods="DELETE")
     * @IsGranted("edit", subject="trick", message="Access denied")
     */
    public function delete(Request $request, Trick $trick)
    {
        if ($this->isCsrfTokenValid('trick_deletion_' . $trick->getId(), $request->get('_token'))) {
            if ($directory = $this->getParameter('trick_media_directory') . $trick->getId()) {
                $this->fileSystem->remove($directory);
            }
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

    /**
     * @Route("/user/trick/report{id}", name="user.trick.report")
     */
    public function report(Trick $trick, Request $request, UploaderHelper $uploaderHelper, MailerInterface $mailer, ReportedTrickGenerator $reportedTrickGenerator)
    {
        $reportedTrick = $reportedTrickGenerator->transform($trick, $this->getUser());

        $form = $this->createForm(ReportedTrickType::class, $reportedTrick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $mainImage = $form->get('mainImage')->getData();
            if (!empty($mainImage)) {
                $mainImageName = $uploaderHelper->uploadFile($mainImage, 'tricks', 'reportedtrick_' . $reportedTrick->getId());
                $reportedTrick->setMainImage($mainImageName);
            }

            $images = $form->get('images')->getData();

            foreach ($images as $image) {
                if ($image->getFile() != null) {
                    $imageName = $uploaderHelper->uploadFile($image->getFile(), 'tricks', 'reportedtrick_' . $reportedTrick->getId());

                    $image->setName($imageName)
                        ->setReportedTrick($reportedTrick);
                    $reportedTrick->addImage($image);
                }
            }

            $videos = $form->get('videos')->getData();
            foreach ($videos as $video) {
                $reportedTrick->addVideo($video);
            }

            $this->entityManager->persist($reportedTrick);
            $this->entityManager->flush();

            if ($this->fileSystem->exists($this->getParameter('reportedtrick_media_directory'))) {
                $this->fileSystem->rename($this->getParameter('reportedtrick_media_directory'), $this->getParameter('reportedtrick_media_directory') . $reportedTrick->getId());
            }


            $url = $this->generateUrl('user.trick.reportView', ['id' => $reportedTrick->getId()]);
            $message = (new TemplatedEmail())
                ->from(new Address('mailer@snowtricks.com', 'No-reply Snowtricks'))
                ->to(new Address($trick->getAuthor()->getEmail(), $trick->getAuthor()->getUsername()))
                ->subject('Trick report')
                ->context([
                    'url' => $url,
                    'user' => $reportedTrick->getUser()->getUsername(),
                    'trick_name' => $trick->getName()
                ])
                ->htmlTemplate('email/trick_report.html.twig');

            $mailer->send($message);

            $this->addFlash('success', 'A notification has been sent to ' . $trick->getAuthor()->getUsername() . 'for modification request');

            return $this->redirectToRoute('trick.show', [
                'id' => $trick->getId(),
                'slug' => $trick->getSlug()
            ]);
        }

        return $this->render('trick/edit.html.twig', [
            'type' => 'reportedTrick',
            'trick' => $trick,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/reportView{id}", name="user.trick.reportView")
     */
    public function trickReportView(ReportedTrick $reportedTrick, Request $request, ImageFileDeletor $imageFileDeletor)
    {
        $trick = $reportedTrick->getTrick();

        if ($request->isMethod('POST')) {
            dump($request->request);
            if ($request->request->get('reported_name')) {
                $trick->setName($reportedTrick->getName());
            }
            if ($request->request->get('reported_description')) {
                $trick->setDescription($reportedTrick->getDescription());
            }
            if ($request->request->get('reported_mainImage')) {
                $trick->setMainImage($reportedTrick->getMainImage());
                $fileSystem = new Filesystem();
                $fileSystem->copy($this->getParameter('reportedtrick_media_directory') . $reportedTrick->getId() . '/' . $reportedTrick->getMainImage(), $this->getParameter('trick_media_directory') . $reportedTrick->getTrick()->getId() . '/' . $reportedTrick->getMainImage(), true);
            }
            foreach ($trick->getImages() as $image) {
                if ($request->request->get('image_' . $image->getId())) {
                    $trick->removeImage($image);
                }
            }
            foreach ($reportedTrick->getImages() as $image) {
                if ($request->request->get('reported_image_' . $image->getId())) {
                    $trick->addImage($image);
                    $fileSystem = new Filesystem();
                    $fileSystem->copy($this->getParameter('reportedtrick_media_directory') . $reportedTrick->getId() . '/' . $image->getName(), $this->getParameter('trick_media_directory') . $reportedTrick->getTrick()->getId() . '/' . $image->getName(), true);
                }
            }
            foreach ($trick->getVideos() as $video) {
                if ($request->request->get('video_' . $video->getId())) {
                    $trick->removeVideo($video);
                }
            }
            foreach ($reportedTrick->getVideos() as $video) {
                if ($request->request->get('reported_video_' . $video->getId())) {
                    $trick->addVideo($video);
                }
            }
            foreach ($trick->getGroups() as $group) {
                if ($request->request->get('group_' . $group->getId())) {
                    $trick->removeGroup($group);
                }
            }
            foreach ($reportedTrick->getGroups() as $group) {
                if ($request->request->get('reported_group_' . $group->getId())) {
                    $trick->addGroup($group);
                }
            }

            $trick->setUpdatedAt(new DateTime());

            $this->entityManager->persist($trick);
            $this->entityManager->flush();

            if ($directory = $this->getParameter('reportedtrick_media_directory') . $reportedTrick->getId()) {
                $this->fileSystem->remove($directory);
            }

            $trickImages = [$trick->getMainImage()];
            foreach ($trick->getImages() as $image) {
                array_push($trickImages, $image->getName());
            }
            $imageFileDeletor->deleteFile('trick', $trick->getId(), $trickImages);

            $this->addFlash('success', 'Your trick has been updated !');

            return $this->redirectToRoute('trick.show', [
                'id' => $trick->getId(),
                'slug' => $trick->getSlug()
            ]);
        }

        return $this->render('trick/reportView.html.twig', [
            'trick' => $trick,
            'reportedTrick' => $reportedTrick
        ]);
    }

}
