<?php

namespace App\Controller\Post;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Users;
use App\Form\Post\CommentType;
use App\Form\Post\PostType;
use App\Repository\Post\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/frontoffice/feed')]
class FrontofficePostController extends AbstractController
{
    #[Route('', name: 'app_frontoffice_feed', methods: ['GET'])]
    public function feed(PostRepository $postRepository): Response
    {
        $user = $this->currentUser();
        $userRole = $user?->getRole();
        $posts = $postRepository->findFeedPosts($userRole);

        $commentForms = [];
        foreach ($posts as $post) {
            $commentForm = $this->createForm(CommentType::class, new Comment(), [
                'action' => $this->generateUrl('app_frontoffice_comment_new', ['postId' => $post->getIdPost()]),
            ]);
            $commentForms[$post->getIdPost()] = $commentForm->createView();
        }

        return $this->render('post/frontoffice/feed.html.twig', [
            'posts' => $posts,
            'commentForms' => $commentForms,
        ]);
    }

    #[Route('/new', name: 'app_frontoffice_post_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->currentUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $post = new Post();
        $post->setId_user($user);

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePostImageUpload($form, $post, $slugger);
            $post->setCreated_at(new \DateTime());
            $post->setUpdated_at(new \DateTime());
            $post->setIs_published(true);
            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post publie avec succes !');
            return $this->redirectToRoute('app_frontoffice_feed');
        }

        return $this->render('post/frontoffice/post_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Creer un post',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_frontoffice_post_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, PostRepository $postRepository, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->currentUser();
        $post = $postRepository->find($id);
        if (!$post || !$user) {
            $this->addFlash('danger', 'Post introuvable.');
            return $this->redirectToRoute('app_frontoffice_feed');
        }

        if ($post->getId_user() && $post->getId_user()->getId() !== $user->getId()) {
            $this->addFlash('danger', 'Vous ne pouvez modifier que vos propres posts.');
            return $this->redirectToRoute('app_frontoffice_feed');
        }

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePostImageUpload($form, $post, $slugger);
            $post->setUpdated_at(new \DateTime());
            $em->flush();
            $this->addFlash('success', 'Post mis a jour.');

            return $this->redirectToRoute('app_frontoffice_feed');
        }

        return $this->render('post/frontoffice/post_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier mon post',
        ]);
    }

    #[Route('/{id}/delete', name: 'app_frontoffice_post_delete', methods: ['POST'])]
    public function delete(int $id, PostRepository $postRepository, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->currentUser();
        $post = $postRepository->find($id);
        if (!$post || !$user) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Post introuvable.'], Response::HTTP_NOT_FOUND);
            }

            return $this->redirectToRoute('app_frontoffice_feed');
        }

        if ($post->getId_user() && $post->getId_user()->getId() !== $user->getId()) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Vous ne pouvez supprimer que vos propres posts.'], Response::HTTP_FORBIDDEN);
            }

            $this->addFlash('danger', 'Vous ne pouvez supprimer que vos propres posts.');
            return $this->redirectToRoute('app_frontoffice_feed');
        }

        if ($this->isCsrfTokenValid('delete_post_' . $id, $request->request->get('_token'))) {
            $em->remove($post);
            $em->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'message' => 'Post supprime.',
                    'postId' => $id,
                ]);
            }

            $this->addFlash('success', 'Post supprime.');
        } elseif ($request->isXmlHttpRequest()) {
            return $this->json(['success' => false, 'message' => 'Requete invalide.'], Response::HTTP_BAD_REQUEST);
        }

        return $this->redirectToRoute('app_frontoffice_feed');
    }

    private function currentUser(): ?Users
    {
        $user = $this->getUser();

        return $user instanceof Users ? $user : null;
    }

    private function handlePostImageUpload(FormInterface $form, Post $post, SluggerInterface $slugger): void
    {
        /** @var UploadedFile|null $imageFile */
        $imageFile = $form->get('imageFile')->getData();
        if (!$imageFile) {
            return;
        }

        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename ?: 'post-image');
        $extension = $imageFile->guessExtension() ?: 'bin';
        $newFilename = $safeFilename . '-' . uniqid('', true) . '.' . $extension;
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/posts';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        try {
            $imageFile->move($uploadDir, $newFilename);
            $post->setImageUrl('/uploads/posts/' . $newFilename);
        } catch (FileException) {
            $this->addFlash('danger', 'L\'image n\'a pas pu etre enregistree.');
        }
    }
}
