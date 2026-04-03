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

#[Route('/frontoffice/feed/comment')]
class FrontofficeCommentController extends AbstractController
{
    #[Route('/{postId}', name: 'app_frontoffice_comment_new', methods: ['POST'])]
    public function new(
        int $postId,
        PostRepository $postRepository,
        Request $request,
        EntityManagerInterface $em,
        CsrfTokenManagerInterface $csrfTokenManager
    ): Response
    {
        $user = $this->currentUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $post = $postRepository->find($postId);
        if (!$post) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Post introuvable.'], Response::HTTP_NOT_FOUND);
            }

            $this->addFlash('danger', 'Post introuvable.');
            return $this->redirectToRoute('app_frontoffice_feed');
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
                        'isAdmin' => in_array(strtoupper((string) $user->getRole()), ['ADMIN', 'ROLE_ADMIN'], true),
                        'roleLabel' => strtoupper((string) $user->getRole()),
                        'isEdited' => false,
                        'editUrl' => $this->generateUrl('app_frontoffice_comment_edit', ['id' => $comment->getIdComment()]),
                        'deleteUrl' => $this->generateUrl('app_frontoffice_comment_delete', ['id' => $comment->getIdComment()]),
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

        return $this->redirectToRoute('app_frontoffice_feed');
    }

    #[Route('/{id}/edit', name: 'app_frontoffice_comment_edit', methods: ['POST'])]
    public function edit(int $id, CommentRepository $commentRepository, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->currentUser();
        $comment = $commentRepository->find($id);
        if (!$comment || !$user) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Commentaire introuvable.'], Response::HTTP_NOT_FOUND);
            }

            return $this->redirectToRoute('app_frontoffice_feed');
        }

        if ($comment->getUser() && $comment->getUser()->getId() !== $user->getId()) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Vous ne pouvez modifier que vos propres commentaires.'], Response::HTTP_FORBIDDEN);
            }

            $this->addFlash('danger', 'Vous ne pouvez modifier que vos propres commentaires.');
            return $this->redirectToRoute('app_frontoffice_feed');
        }

        $newContent = trim((string) $request->request->get('comment_content', ''));
        if ($newContent === '') {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Le commentaire ne peut pas etre vide.'], Response::HTTP_BAD_REQUEST);
            }

            $this->addFlash('danger', 'Le commentaire ne peut pas etre vide.');
            return $this->redirectToRoute('app_frontoffice_feed');
        }

        $comment->setContent($newContent);
        $comment->setIs_edited(true);
        $comment->setUpdated_at(new \DateTime());
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'message' => 'Commentaire modifie.',
                'comment' => [
                    'id' => $comment->getIdComment(),
                    'content' => $comment->getContent(),
                    'updatedAt' => $comment->getUpdated_at()->format('d/m/Y H:i'),
                    'isEdited' => true,
                ],
            ]);
        }

        $this->addFlash('success', 'Commentaire modifie.');

        return $this->redirectToRoute('app_frontoffice_feed');
    }

    #[Route('/{id}/delete', name: 'app_frontoffice_comment_delete', methods: ['POST'])]
    public function delete(int $id, CommentRepository $commentRepository, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->currentUser();
        $comment = $commentRepository->find($id);
        if (!$comment || !$user) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Commentaire introuvable.'], Response::HTTP_NOT_FOUND);
            }

            return $this->redirectToRoute('app_frontoffice_feed');
        }

        if ($comment->getUser() && $comment->getUser()->getId() !== $user->getId()) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Vous ne pouvez supprimer que vos propres commentaires.'], Response::HTTP_FORBIDDEN);
            }

            $this->addFlash('danger', 'Vous ne pouvez supprimer que vos propres commentaires.');
            return $this->redirectToRoute('app_frontoffice_feed');
        }

        if ($this->isCsrfTokenValid('delete_comment_' . $id, $request->request->get('_token'))) {
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
}
