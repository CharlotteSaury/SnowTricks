<?php

namespace App\Controller;

use App\Entity\Group;
use App\Form\GroupType;
use App\Repository\GroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_ADMIN", message="Access denied")
 */
class GroupController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/admin/groups", name="group.index", methods={"GET", "POST"})
     */
    public function index(GroupRepository $groupRepository, Request $request): Response
    {
        $group = new Group();
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($group);
            $this->entityManager->flush();
            $this->addFlash('success', $group->getName().' has been added to group list !');

            return $this->redirectToRoute('group.index');
        }

        return $this->render('group/index.html.twig', [
            'groups' => $groupRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/group{id}/edit", name="group.edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Group $group): Response
    {
        $form = $this->createForm(GroupType::class, $group);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();
            $this->addFlash('success', 'Group name has been updated !');

            return $this->redirectToRoute('group.index');
        }

        return $this->render('group/edit.html.twig', [
            'group' => $group,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/group{id}", name="group.delete", methods={"DELETE"})
     */
    public function delete(Request $request, Group $group): Response
    {
        if ($this->isCsrfTokenValid('delete_group'.$group->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($group);
            $this->entityManager->flush();
            $this->addFlash('success', 'Group has been deleted !');
        }

        return $this->redirectToRoute('group.index');
    }
}
