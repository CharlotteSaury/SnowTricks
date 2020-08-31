<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Helper\MailSenderHelper;
use App\Helper\TrickGenerator;
use App\Repository\TrickRepository;
use App\Service\CommentService;
use App\Service\TrickService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    /**
     * @var TrickRepository
     */
    private $trickRepository;

    /**
     * @var TrickService
     */
    private $trickService;

    /**
     * @var CommentService
     */
    private $commentService;

    public function __construct(TrickRepository $trickRepository, TrickService $trickService, CommentService $commentService)
    {
        $this->trickRepository = $trickRepository;
        $this->trickService = $trickService;
        $this->commentService = $commentService;
    }

    /**
     * Return trick list.
     *
     * @Route("/", name="trick.index")
     */
    public function index(): Response
    {
        $tricks = $this->trickRepository->findBy(['parentTrick' => null], ['createdAt' => 'DESC']);

        return $this->render('trick/index.html.twig', [
            'tricks' => $tricks,
        ]);
    }

    /**
     * Handle trick page and new comment creation.
     *
     * @Route("/trick{id}/{slug}", name="trick.show")
     */
    public function show(Trick $trick, Request $request): Response
    {
        $trick = $this->trickRepository->find($trick->getId());
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commentService->handleNewComment($comment, $trick, $this->getUser());
            $this->addFlash('successComment', 'Your comment is posted !');

            return $this->redirect($this->generateUrl('trick.show', [
                '_fragment' => 'trickCommentForm',
                'id' => $trick->getId(),
                'slug' => $trick->getName(),
                ]));
        }

        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Handle new trick creation.
     *
     * @Route("/user/trick/new", name="user.trick.new")
     */
    public function new(Request $request): Response
    {
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $this->trickService->handleCreateOrUpdate($trick, $form, $this->getUser());
            $this->addFlash('success', 'Your trick is posted !');

            return $this->redirectToRoute('trick.show', [
                'id' => $trick->getId(),
                'slug' => $trick->getSlug(),
            ]);
        }

        return $this->render('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Handle trick edition.
     *
     * @Route("/user/trick/edit{id}", name="user.trick.edit")
     * @IsGranted("edit", subject="trick", message="Access denied")
     */
    public function edit(Trick $trick, Request $request): Response
    {
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $this->trickService->handleCreateOrUpdate($trick, $form, $trick->getAuthor());
            if ($this->getUser() === $trick->getAuthor()) {
                $this->addFlash('success', 'Your trick has been updated !');
            } else {
                $this->addFlash('success', $trick->getAuthor()->getUsername().'\'s trick has been updated !');
            }

            return $this->redirectToRoute('trick.show', [
                'id' => $trick->getId(),
                'slug' => $trick->getSlug(),
            ]);
        }

        return $this->render('trick/edit.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Handle trick main image deletion.
     *
     * @Route("/user/trick/deleteMainImage{id}", name="user.trick.delete.mainImage", methods="DELETE")
     * @IsGranted("edit", subject="trick", message="Access denied")
     *
     * @return Response
     */
    public function deleteMainImage(Trick $trick, Request $request)
    {
        if ($this->isCsrfTokenValid('mainImage_deletion_'.$trick->getId(), $request->get('_token'))) {
            $this->trickService->handleMainImageDeletion($trick);
            $this->addFlash('success', 'Main image has been deleted !');

            return $this->redirectToRoute('user.trick.edit', [
                'id' => $trick->getId(),
            ]);
        }
        $this->addFlash('error', 'An error has occured.');

        return $this->redirectToRoute('trick.index');
    }

    /**
     * Handle Trick deletion.
     *
     * @Route("/user/trick/delete{id}", name="user.trick.delete", methods="DELETE")
     * @IsGranted("edit", subject="trick", message="Access denied")
     *
     * @return Response
     */
    public function delete(Request $request, Trick $trick)
    {
        if ($this->isCsrfTokenValid('trick_deletion_'.$trick->getId(), $request->get('_token'))) {
            $reportedTricks = $this->trickRepository->findBy(['parentTrick' => $trick]);
            foreach ($reportedTricks as $reportedTrick) {
                $this->trickService->handleTrickDeletion($reportedTrick);
            }
            $this->trickService->handleTrickDeletion($trick);

            if ($trick->getAuthor() === $this->getUser()) {
                $this->addFlash('success', 'Your trick has been deleted !');

                return $this->redirectToRoute('user.tricks');
            }
            $this->addFlash('success', $trick->getAuthor()->getUsername().'\'s trick has been deleted !');

            return $this->redirectToRoute('trick.index');
        }
        $this->addFlash('error', 'An error has occured.');

        return $this->redirectToRoute('trick.index');
    }

    /**
     * Display loggued user tricks.
     *
     * @Route("user/tricks", name="user.tricks")
     */
    public function tricks(): Response
    {
        $tricks = $this->trickRepository->findBy(['author' => $this->getUser()->getId(), 'parentTrick' => null]);

        return $this->render('user/tricks.html.twig', [
            'tricks' => $tricks,
            'nav' => 'myTricks',
        ]);
    }

    /**
     * Handle trick report.
     *
     * @Route("/user/trick/report{id}", name="user.trick.report")
     */
    public function report(Trick $trick, Request $request, TrickGenerator $trickGenerator, MailSenderHelper $mailSenderHelper): Response
    {
        $reportedTrick = $trickGenerator->clone($trick, $this->getUser());
        $form = $this->createForm(TrickType::class, $reportedTrick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reportedTrick = $this->trickService->handleCreateOrUpdate($reportedTrick, $form, $this->getUser());

            $url = $this->generateUrl('user.trick.reportView', ['id' => $reportedTrick->getId()]);
            $mailSenderHelper->sendMail('trick_report', $trick->getAuthor(), [
                'url' => $url,
                'user' => $reportedTrick->getAuthor()->getUsername(),
                'trick_name' => $trick->getName(),
            ]);
            $this->addFlash('success', 'A notification has been sent to '.$trick->getAuthor()->getUsername().'for modification request');

            return $this->redirectToRoute('trick.show', [
                'id' => $trick->getId(),
                'slug' => $trick->getSlug(),
            ]);
        }

        return $this->render('trick/edit.html.twig', [
            'type' => 'reportedTrick',
            'trick' => $trick,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Handle trick suggested modifications.
     *
     * @Route("/user/reportView{id}", name="user.trick.reportView")
     *
     * @return Response
     */
    public function trickReportView(Trick $reportedTrick, Request $request)
    {
        $trick = $reportedTrick->getParentTrick();
        if (!$this->isGranted('report', $trick)) {
            throw $this->createAccessDeniedException();
        }
        if ($request->isMethod('POST')) {
            if ($this->isCsrfTokenValid('save_report_'.$reportedTrick->getId(), $request->get('_token'))) {
                $this->trickService->handleReport($reportedTrick, $request);

                $this->addFlash('success', 'Your trick has been updated !');

                return $this->redirectToRoute('trick.show', [
                    'id' => $trick->getId(),
                    'slug' => $trick->getSlug(),
                ]);
            }
            $this->addFlash('error', 'An error has occured');

            return $this->redirectToRoute('trick.index');
        }

        return $this->render('trick/reportView.html.twig', [
            'trick' => $trick,
            'reportedTrick' => $reportedTrick,
        ]);
    }
}
