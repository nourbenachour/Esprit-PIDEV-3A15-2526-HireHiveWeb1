<?php

namespace App\Controller\Post;

use App\Entity\Reaction;
use App\Entity\Users;
use App\Repository\Post\PostRepository;
use App\Repository\Post\ReactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('/frontoffice/feed')]
class FrontofficeReactionController extends AbstractController
{
    #[Route('/{postId}/react/{type}', name: 'app_frontoffice_reaction_toggle', methods: ['POST'])]
    public function toggle(
        int $postId,
        string $type,
        PostRepository $postRepository,
        ReactionRepository $reactionRepository,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->currentUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $post = $postRepository->find($postId);
        if (!$post) {
            return $this->redirectToRoute('app_frontoffice_feed');
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
            $likes = 0;
            $loves = 0;
            $dislikes = 0;

            foreach ($post->getReactions() as $reactionItem) {
                $reactionType = strtoupper((string) $reactionItem->getReaction_type());

                if ($reactionType === 'LIKE') {
                    ++$likes;
                } elseif ($reactionType === 'LOVE') {
                    ++$loves;
                } elseif ($reactionType === 'DISLIKE') {
                    ++$dislikes;
                }
            }

            return $this->json([
                'success' => true,
                'counts' => [
                    'like' => $likes,
                    'love' => $loves,
                    'dislike' => $dislikes,
                ],
                'activeReaction' => $existing && $existing->getReaction_type() === $selectedType ? null : $selectedType,
            ]);
        }

        return $this->redirectToRoute('app_frontoffice_feed');
    }

    private function currentUser(): ?Users
    {
        $user = $this->getUser();

        return $user instanceof Users ? $user : null;
    }
}
