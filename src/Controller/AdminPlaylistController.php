<?php

namespace App\Controller;

use App\Entity\Playlist;
use App\Form\PlaylistType;
use App\Repository\PlaylistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminPlaylistController extends AbstractController
{
    #[Route('/admin/playlists', name: 'admin_playlists', methods: ['GET'])]
    public function index(PlaylistRepository $playlistRepository): Response
    {
        return $this->render('admin/playlists/index.html.twig', [
            'playlists' => $playlistRepository->findAll(),
        ]);
    }

    #[Route('/admin/playlists/add', name: 'admin_playlists_add', methods: ['GET', 'POST'])]
    public function add(Request $request, EntityManagerInterface $em): Response
    {
        $playlist = new Playlist();

        $form = $this->createForm(PlaylistType::class, $playlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($playlist);
            $em->flush();

            $this->addFlash('success', 'Playlist ajoutée avec succès.');
            return $this->redirectToRoute('admin_playlists');
        }

        return $this->render('admin/playlists/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/playlists/edit/{id}', name: 'admin_playlists_edit', methods: ['GET', 'POST'])]
    public function edit(Playlist $playlist, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PlaylistType::class, $playlist);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Playlist modifiée avec succès.');
            return $this->redirectToRoute('admin_playlists');
        }

        return $this->render('admin/playlists/edit.html.twig', [
            'playlist' => $playlist,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/playlists/delete/{id}', name: 'admin_playlists_delete', methods: ['POST'])]
    public function delete(Playlist $playlist, Request $request, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');

        if (!$this->isCsrfTokenValid('delete_playlist_' . $playlist->getId(), $token)) {
            throw $this->createAccessDeniedException('Token CSRF invalide.');
        }

        // Si des formations sont liées à la playlist, on bloque (si relation présente)
        if (method_exists($playlist, 'getFormations') && count($playlist->getFormations()) > 0) {
            $this->addFlash('danger', 'Suppression impossible : des formations sont rattachées à cette playlist.');
            return $this->redirectToRoute('admin_playlists');
        }

        $em->remove($playlist);
        $em->flush();

        $this->addFlash('success', 'Playlist supprimée avec succès.');
        return $this->redirectToRoute('admin_playlists');
    }
}