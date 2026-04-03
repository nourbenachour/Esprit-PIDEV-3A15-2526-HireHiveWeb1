<?php

namespace App\Controller\Post;

use App\Entity\Comment;
use App\Entity\Users;
use App\Form\Post\CommentType;
use App\Repository\Post\CommentRepository;
use App\Repository\Post\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/backoffice/posts-management')]
#[IsGranted('ROLE_ADMIN')]
class BackofficeCommentController extends AbstractController
{
    #[Route('/{postId}/comment/new', name: 'app_backoffice_comment_new', methods: ['POST'])]
    public function new(
        int $postId,
        PostRepository $postRepository,
        Request $request,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response {
        $post = $postRepository->find($postId);
        if (!$post) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Post introuvable.'], Response::HTTP_NOT_FOUND);
            }

            $this->addFlash('danger', 'Post introuvable.');
            return $this->redirectToRoute('app_backoffice_posts_list');
        }

        $user = $this->currentUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPost($post);
            $comment->setUser($user);
            $comment->setCreated_at(new \DateTime());
            $comment->setUpdated_at(new \DateTime());
            $em->persist($comment);
            $em->flush();

            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => true,
                    'message' => 'Commentaire ajoute.',
                    'commentCount' => count($post->getComments()),
                    'comment' => [
                        'id' => $comment->getIdComment(),
                        'content' => $comment->getContent(),
                        'createdAt' => $comment->getCreatedAt()->format('d/m/Y H:i'),
                        'authorName' => trim((string) (($user->getFirstName() ?? '') . ' ' . ($user->getLastName() ?? ''))),
                        'authorInitials' => strtoupper(($user->getFirstName() ? substr($user->getFirstName(), 0, 1) : '?') . ($user->getLastName() ? substr($user->getLastName(), 0, 1) : '?')),
                        'isAdmin' => true,
                        'isEdited' => false,
                        'editUrl' => $this->generateUrl('app_backoffice_comment_edit', ['id' => $comment->getIdComment()]),
                        'deleteUrl' => $this->generateUrl('app_backoffice_comment_delete', ['id' => $comment->getIdComment()]),
                        'deleteToken' => $csrfTokenManager->getToken('delete_comment_' . $comment->getIdComment())->getValue(),
                    ],
                ]);
            }

            $this->addFlash('success', 'Commentaire ajoute.');
        } else {
            if ($request->isXmlHttpRequest()) {
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }

                return $this->json([
                    'success' => false,
                    'message' => $errors[0] ?? 'Impossible d\'ajouter le commentaire.',
                ], Response::HTTP_BAD_REQUEST);
            }

            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
        }

        return $this->redirectToRoute('app_backoffice_posts_list');
    }

    #[Route('/comment/{id}/edit', name: 'app_backoffice_comment_edit', methods: ['POST'])]
    public function edit(int $id, CommentRepository $commentRepository, Request $request, EntityManagerInterface $em): Response
    {
        $comment = $commentRepository->find($id);
        if (!$comment) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Commentaire introuvable.'], Response::HTTP_NOT_FOUND);
            }

            $this->addFlash('danger', 'Commentaire introuvable.');
            return $this->redirectToRoute('app_backoffice_posts_list');
        }

        $newContent = trim((string) $request->request->get('comment_content', ''));
        if ($newContent === '') {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Le commentaire ne peut pas etre vide.'], Response::HTTP_BAD_REQUEST);
            }

            $this->addFlash('danger', 'Le commentaire ne peut pas etre vide.');
            return $this->redirectToRoute('app_backoffice_posts_list');
        }

        $comment->setContent($newContent);
        $comment->setIs_edited(true);
        $comment->setUpdated_at(new \DateTime());
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'message' => 'Commentaire mis a jour.',
                'comment' => [
                    'id' => $comment->getIdComment(),
                    'content' => $comment->getContent(),
                    'updatedAt' => $comment->getUpdated_at()->format('d/m/Y H:i'),
                    'isEdited' => true,
                ],
            ]);
        }

        $this->addFlash('success', 'Commentaire mis a jour.');
        return $this->redirectToRoute('app_backoffice_posts_list');
    }

    #[Route('/comment/{id}/delete', name: 'app_backoffice_comment_delete', methods: ['POST'])]
    public function delete(int $id, CommentRepository $commentRepository, Request $request, EntityManagerInterface $em): Response
    {
        $comment = $commentRepository->find($id);
        if (!$comment || !$this->isCsrfTokenValid('delete_comment_' . $id, $request->request->get('_token'))) {
            if ($request->isXmlHttpRequest()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Commentaire introuvable ou requete invalide.',
                ], Response::HTTP_BAD_REQUEST);
            }

            $this->addFlash('danger', 'Commentaire introuvable ou requete invalide.');

            return $this->redirectToRoute('app_backoffice_posts_list');
        }

        $postId = $comment->getPost()?->getIdPost();

        $em->remove($comment);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'message' => 'Commentaire supprime.',
                'commentId' => $id,
                'postId' => $postId,
            ]);
        }

        $this->addFlash('success', 'Commentaire supprime.');

        return $this->redirectToRoute('app_backoffice_posts_list');
    }

    private function currentUser(): ?Users
    {
        $user = $this->getUser();

        return $user instanceof Users ? $user : null;
    }
}
