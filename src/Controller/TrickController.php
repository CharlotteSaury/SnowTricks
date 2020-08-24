<?php

namespace App\Controller;

use DateTime;
use App\Entity\Trick;
use App\Form\TrickType;
use App\Service\TrickService;
use App\Helper\ImageFileDeletor;
use Symfony\Component\Form\Form;
use App\Repository\TrickRepository;
use Symfony\Component\Mime\Address;
use App\Helper\TrickGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TrickRepository
     */
    private $trickRepository;

    /**
     * @var FileSystem
     */
    private $fileSystem;

    /**
     *
     * @var TrickService
     */
    private $trickService;

    public function __construct(TrickRepository $trickRepository, EntityManagerInterface $entityManager, Filesystem $fileSystem, TrickService $trickService)
    {
        $this->trickRepository = $trickRepository;
        $this->entityManager = $entityManager;
        $this->fileSystem = $fileSystem;
        $this->trickService = $trickService;
    }

    /**
     * @Route("/", name="trick.index")
     * @return Response
     */
    public function index(): Response
    {
        $tricks = $this->trickRepository->findBy(['parentTrick' => null]);
        return $this->render('trick/index.html.twig', [
            'tricks' => $tricks
        ]);
    }

    /**
     * @Route("/trick{id}/{slug}", name="trick.show")
     * @return Response
     */
    public function show(Trick $trick, Request $request, CommentController $commentController): Response
    {
        $trick = $this->trickRepository->find($trick->getId());
        $view = $commentController->new($request, $trick);

        if (!$view instanceof Form) {
            return $view;
        }
        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'form' => $view->createView()
        ]);
    }

    /**
     * @Route("/user/trick/new", name="user.trick.new")
     * @return Response
     */
    public function new(Request $request) : Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $this->trickService->handleCreateOrUpdate($trick, $form, $this->getUser());

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
     * @return Response
     */
    public function edit(Trick $trick, Request $request) : Response
    {
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $this->trickService->handleCreateOrUpdate($trick, $form, $trick->getAuthor());

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
            $this->addFlash('success', 'Main image has been deleted !');
            return $this->redirectToRoute('user.trick.edit', [
                'id' => $trick->getId()
            ]);
        }
        $this->addFlash('error', 'An error has occured.');
        return $this->redirectToRoute('trick.index');
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
            if ($trick->getAuthor() == $this->getUser()) {
                $this->addFlash('success', 'Your trick has been deleted !');
                return $this->redirectToRoute('user.tricks');
            }
            $this->addFlash('success', $trick->getAuthor()->getUsername() . '\'s trick has been deleted !');
            return $this->redirectToRoute('trick.index');
        }
        $this->addFlash('error', 'An error has occured.');
        return $this->redirectToRoute('trick.index');
    }

    /**
     * @Route("user/tricks", name="user.tricks")
     * @return Response
     */
    public function tricks(): Response
    {
        $tricks = $this->trickRepository->findBy(['author' => $this->getUser()->getId(), 'parentTrick' => null]);

        return $this->render('user/tricks.html.twig', [
            'tricks' => $tricks,
            'nav' => 'myTricks'
        ]);
    }

    /**
     * @Route("/user/trick/report{id}", name="user.trick.report")
     */
    public function report(Trick $trick, Request $request, MailerInterface $mailer, TrickGenerator $trickGenerator)
    {
        $reportedTrick = $trickGenerator->transform($trick, $this->getUser());
        $form = $this->createForm(TrickType::class, $reportedTrick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reportedTrick = $this->trickService->handleCreateOrUpdate($reportedTrick, $form, $this->getUser());
            
            $url = $this->generateUrl('user.trick.reportView', ['id' => $reportedTrick->getId()]);
            $message = (new TemplatedEmail())
                ->from(new Address('mailer@snowtricks.com', 'No-reply Snowtricks'))
                ->to(new Address($trick->getAuthor()->getEmail(), $trick->getAuthor()->getUsername()))
                ->subject('Trick report')
                ->context([
                    'url' => $url,
                    'user' => $reportedTrick->getAuthor()->getUsername(),
                    'trick_name' => $trick->getName()
                ])
                ->htmlTemplate('email/trick_report.html.twig');

            $mailer->send($message);

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
    public function trickReportView(Trick $reportedTrick, Request $request)
    {
        $trick = $reportedTrick->getParentTrick();
        if (!$this->isGranted('report', $trick)) {
            throw $this->createAccessDeniedException();
        }
        if ($request->isMethod('POST')) {
            if ($this->isCsrfTokenValid('save_report_' . $reportedTrick->getId(), $request->get('_token'))) {
                $this->trickService->handleReport($reportedTrick, $request);
                
                $this->addFlash('success', 'Your trick has been updated !');
                return $this->redirectToRoute('trick.show', [
                    'id' => $trick->getId(),
                    'slug' => $trick->getSlug()
                ]);
            }
            $this->addFlash('error', 'An error has occured');
            return $this->redirectToRoute('trick.index');
        } 

        return $this->render('trick/reportView.html.twig', [
            'trick' => $trick,
            'reportedTrick' => $reportedTrick
        ]);
    }
}
