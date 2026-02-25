<?php

namespace App\Controller;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminFormationController extends AbstractController
{
    #[Route('/admin/formations', name: 'admin_formations', methods: ['GET'])]
    public function index(FormationRepository $formationRepository): Response
    {
        return $this->render('admin/formations/index.html.twig', [
            'formations' => $formationRepository->findAll(),
        ]);
    }

    #[Route('/admin/formations/add', name: 'admin_formations_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $formation = new Formation();

        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($formation);
            $em->flush();

            $this->addFlash('success', 'Formation ajoutée.');
            return $this->redirectToRoute('admin_formations');
        }

        return $this->render('admin/formations/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/formations/edit/{id}', name: 'admin_formations_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, FormationRepository $formationRepository, Request $request, EntityManagerInterface $em): Response
    {
        $formation = $formationRepository->find($id);

        if (!$formation) {
            throw $this->createNotFoundException('Formation introuvable.');
        }

        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Formation modifiée.');
            return $this->redirectToRoute('admin_formations');
        }

        return $this->render('admin/formations/edit.html.twig', [
            'formation' => $formation,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/formations/delete/{id}', name: 'admin_formations_delete', methods: ['POST'])]
    public function delete(int $id, FormationRepository $formationRepository, Request $request, EntityManagerInterface $em): Response
    {
        $formation = $formationRepository->find($id);

        if (!$formation) {
            throw $this->createNotFoundException('Formation introuvable.');
        }

        $token = $request->request->get('_token');

        if (!$this->isCsrfTokenValid('delete_formation_' . $formation->getId(), $token)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        $em->remove($formation);
        $em->flush();

        $this->addFlash('success', 'Formation supprimée.');
        return $this->redirectToRoute('admin_formations');
    }
}