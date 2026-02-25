<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminCategorieController extends AbstractController
{
    #[Route('/admin/categories', name: 'admin_categories', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository): Response
    {
        return $this->render('admin/categories/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/admin/categories/add', name: 'admin_categories_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $categorie = new Categorie();

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($categorie);
            $em->flush();

            $this->addFlash('success', 'Catégorie ajoutée.');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/categories/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/categories/edit/{id}', name: 'admin_categories_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, CategorieRepository $categorieRepository, Request $request, EntityManagerInterface $em): Response
    {
        $categorie = $categorieRepository->find($id);

        if (!$categorie) {
            throw $this->createNotFoundException('Catégorie introuvable.');
        }

        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Catégorie modifiée.');
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/categories/edit.html.twig', [
            'categorie' => $categorie,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/categories/delete/{id}', name: 'admin_categories_delete', methods: ['POST'])]
    public function delete(int $id, CategorieRepository $categorieRepository, Request $request, EntityManagerInterface $em): Response
    {
        $categorie = $categorieRepository->find($id);

        if (!$categorie) {
            throw $this->createNotFoundException('Catégorie introuvable.');
        }

        $token = $request->request->get('_token');

        if (!$this->isCsrfTokenValid('delete_categorie_' . $categorie->getId(), $token)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        if (method_exists($categorie, 'getFormations') && count($categorie->getFormations()) > 0) {
            $this->addFlash('danger', 'Suppression impossible : des formations sont rattachées à cette catégorie.');
            return $this->redirectToRoute('admin_categories');
        }

        $em->remove($categorie);
        $em->flush();

        $this->addFlash('success', 'Catégorie supprimée.');
        return $this->redirectToRoute('admin_categories');
    }
}