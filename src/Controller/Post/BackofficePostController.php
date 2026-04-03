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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/backoffice/posts-management')]
#[IsGranted('ROLE_ADMIN')]
class BackofficePostController extends AbstractController
{
    #[Route('', name: 'app_backoffice_posts_list', methods: ['GET'])]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAllOrderedByDate();

        $commentForms = [];
        foreach ($posts as $post) {
            $commentForm = $this->createForm(CommentType::class, new Comment(), [
                'action' => $this->generateUrl('app_backoffice_comment_new', ['postId' => $post->getIdPost()]),
            ]);
            $commentForms[$post->getIdPost()] = $commentForm->createView();
        }

        return $this->render('post/backoffice/posts.html.twig', [
            'posts' => $posts,
            'commentForms' => $commentForms,
        ]);
    }

    #[Route('/new', name: 'app_backoffice_posts_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $user = $this->currentUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $post = new Post();
        $post->setId_user($user);

        $form = $this->createForm(PostType::class, $post, ['is_admin' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePostImageUpload($form, $post, $slugger);
            $post->setCreated_at(new \DateTime());
            $post->setUpdated_at(new \DateTime());
            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post créé avec succes.');
            return $this->redirectToRoute('app_backoffice_posts_list');
        }

        return $this->render('post/backoffice/post_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Creer un post',
        ]);
    }

    #[Route('/{id}/edit', name: 'app_backoffice_posts_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, PostRepository $postRepository, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $post = $postRepository->find($id);
        if (!$post) {
            $this->addFlash('danger', 'Post introuvable.');
            return $this->redirectToRoute('app_backoffice_posts_list');
        }

        $form = $this->createForm(PostType::class, $post, ['is_admin' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handlePostImageUpload($form, $post, $slugger);
            $post->setUpdated_at(new \DateTime());
            $em->flush();

            $this->addFlash('success', 'Post mis a jour avec succès.');
            return $this->redirectToRoute('app_backoffice_posts_list');
        }

        return $this->render('post/backoffice/post_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier le post',
        ]);
    }

    #[Route('/{id}/delete', name: 'app_backoffice_posts_delete', methods: ['POST'])]
    public function delete(int $id, PostRepository $postRepository, Request $request, EntityManagerInterface $em): Response
    {
        $post = $postRepository->find($id);
        if (!$post || !$this->isCsrfTokenValid('delete_post_' . $id, $request->request->get('_token'))) {
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Post introuvable ou requete invalide.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->addFlash('danger', 'Post introuvable ou requete invalide.');

            return $this->redirectToRoute('app_backoffice_posts_list');
        }

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

        return $this->redirectToRoute('app_backoffice_posts_list');
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
