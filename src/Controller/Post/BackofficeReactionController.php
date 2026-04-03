<?php

namespace App\Controller\Post;

use App\Entity\Post;
use App\Entity\Reaction;
use App\Entity\Users;
use App\Repository\Post\PostRepository;
use App\Repository\Post\ReactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/backoffice/posts-management')]
#[IsGranted('ROLE_ADMIN')]
class BackofficeReactionController extends AbstractController
{
    #[Route('/{postId}/react/{type}', name: 'app_backoffice_reaction_toggle', methods: ['POST'])]
    public function toggle(
        int $postId,
        string $type,
        PostRepository $postRepository,
        ReactionRepository $reactionRepository,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $post = $postRepository->find($postId);
        $user = $this->currentUser();
        if (!$post || !$user) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Post ou utilisateur introuvable.'], Response::HTTP_NOT_FOUND);
            }

            return $this->redirectToRoute('app_backoffice_posts_list');
        }

        $existing = $reactionRepository->findUserReactionOnPost((int) $user->getId(), (int) $post->getIdPost());
        $selectedType = strtoupper($type);

        if ($existing) {
            if ($existing->getReaction_type() === $selectedType) {
                $em->remove($existing);
            } else {
                $existing->setReaction_type($selectedType);
                $existing->setCreated_at(new \DateTime());
            }
        } else {
            $reaction = new Reaction();
            $reaction->setPost($post);
            $reaction->setUser($user);
            $reaction->setReaction_type($selectedType);
            $em->persist($reaction);
        }

        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => true,
                'counts' => $this->buildReactionCounts($post),
                'commentCount' => count($post->getComments()),
            ]);
        }

        return $this->redirectToRoute('app_backoffice_posts_list');
    }

    private function currentUser(): ?Users
    {
        $user = $this->getUser();

        return $user instanceof Users ? $user : null;
    }

    private function buildReactionCounts(Post $post): array
    {
        $counts = ['like' => 0, 'love' => 0, 'dislike' => 0];

        foreach ($post->getReactions() as $reaction) {
            $type = strtoupper((string) $reaction->getReactionType());
            if ($type === 'LIKE') {
                $counts['like']++;
            } elseif ($type === 'LOVE') {
                $counts['love']++;
            } elseif ($type === 'DISLIKE') {
                $counts['dislike']++;
            }
        }

        return $counts;
    }
}
