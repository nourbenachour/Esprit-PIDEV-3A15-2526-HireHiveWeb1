<?php

namespace App\Controller\User;

use App\Entity\Users;
use App\Form\User\UserType;
use App\Repository\User\CondidatRepository;
use App\Repository\User\ExperienceRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/backoffice')]
#[IsGranted('ROLE_ADMIN')]
class BackofficeController extends AbstractController
{
    #[Route('', name: 'app_backoffice_attr', methods: ['GET'])]
    public function index(
        UserRepository $userRepository,
        CondidatRepository $condidatRepository,
        ExperienceRepository $experienceRepository
    ): Response {
        $totalUsers       = 17;
        $totalCandidates  = 10;
        $totalInterviews  = 19;
        $totalOffers      = 48; // valeur maquette

        $formations = [
            ['label' => 'Java', 'value' => 42],
            ['label' => 'Python', 'value' => 28],
            ['label' => 'Oracle', 'value' => 18],
            ['label' => 'Autres', 'value' => 12],
        ];

        $posts = [
            ['title' => 'Dev. Java Senior', 'ago' => 'Il y a 2j'],
            ['title' => 'Formation Python avancée', 'ago' => 'Il y a 5j'],
            ['title' => 'Admin Oracle / DBA', 'ago' => 'Il y a 1 sem.'],
            ['title' => 'Full Stack Java + Angular', 'ago' => 'Il y a 1 sem.'],
        ];

        return $this->render('admin/base_admin.html.twig', [
            'total_users'      => $totalUsers,
            'total_candidates' => $totalCandidates,
            'total_offers'     => $totalOffers,
            'total_interviews' => $totalInterviews,
            'formations'       => $formations,
            'posts'            => $posts,
        ]);
    }

    #[Route('/chatbot/ping', name: 'app_backoffice_chatbot_ping', methods: ['POST'])]
    public function pingWebsite(Request $request, HttpClientInterface $httpClient): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $rawUrl = trim((string)($data['url'] ?? ''));

        if ($rawUrl === '') {
            return $this->json(['ok' => false, 'message' => "Merci d'indiquer une URL ou un nom de domaine."], 400);
        }

        $url = $this->normalizeUrl($rawUrl);
        $start = microtime(true);

        try {
            $response = $httpClient->request('HEAD', $url, [
                'max_redirects' => 3,
                'timeout'       => 5,
                'verify_peer'   => false,
                'verify_host'   => false,
            ]);

            $status = $response->getStatusCode();
            $ms = (int) round((microtime(true) - $start) * 1000);

            $ok = $status >= 200 && $status < 400;
            $message = $ok
                ? "Site accessible (HTTP {$status}, {$ms} ms)."
                : "Réponse inattendue (HTTP {$status}, {$ms} ms).";

            return $this->json([
                'ok'      => $ok,
                'status'  => $status,
                'time_ms' => $ms,
                'message' => $message,
                'url'     => $url,
            ]);
        } catch (TransportExceptionInterface $e) {
            return $this->json([
                'ok'      => false,
                'message' => 'Site injoignable : ' . $e->getMessage(),
                'url'     => $url,
            ], 502);
        }
    }

    #[Route('/chatbot/insee', name: 'app_backoffice_chatbot_insee', methods: ['POST'])]
    public function searchInsee(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $query = trim((string)($data['query'] ?? ''));

        if ($query === '') {
            return $this->json(['ok' => false, 'message' => "Merci d'indiquer le nom de l'entreprise."], 400);
        }

        $client = HttpClient::create(['timeout' => 6]);
        $url = 'https://recherche-entreprises.api.gouv.fr/search?q=' . urlencode($query) . '&page=1&per_page=1';

        try {
            $resp = $client->request('GET', $url, [
                'headers' => [
                    'Accept'     => 'application/json',
                    'User-Agent' => 'HireHive/1.0 (contact: admin@hirehive.com)',
                ],
            ]);

            $status = $resp->getStatusCode();
            if ($status !== 200) {
                return $this->json([
                    'ok'      => false,
                    'message' => "API a répondu HTTP {$status}",
                ], $status);
            }

            $json = $resp->toArray(false);
            $results = $json['results'] ?? [];
            if (count($results) === 0) {
                return $this->json(['ok' => false, 'message' => 'Aucun résultat trouvé.']);
            }

            $c = $results[0];
            $payload = [
                'denomination' => $c['nom_complet'] ?? $c['denomination'] ?? '',
                'siren'        => $c['siren'] ?? '',
                'etat'         => ($c['etat_administratif'] ?? '') === 'A' ? 'Active' : ($c['etat_administratif'] ?? ''),
                'naf'          => $c['activite_principale'] ?? '',
                'naf_label'    => $c['libelle_activite_principale'] ?? '',
                'date'         => $c['date_creation'] ?? '',
                'commune'      => $c['adresse']['libelle_commune'] ?? '',
            ];

            return $this->json(['ok' => true, 'result' => $payload]);
        } catch (\Throwable $e) {
            return $this->json([
                'ok'      => false,
                'message' => 'Erreur API INSEE: ' . $e->getMessage(),
            ], 502);
        }
    }

    private function normalizeUrl(string $raw): string
    {
        $s = trim($raw);
        $lower = strtolower($s);
        if (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://')) {
            return $s;
        }

        // slug simple si juste un nom
        $slug = preg_replace('/[^a-z0-9]/i', '', $s);
        if ($slug === '') {
            $slug = 'example';
        }

        return 'https://www.' . $slug . '.com';
    }

    #[Route('/users', name: 'app_backoffice_users', methods: ['GET'])]
    public function users(Request $request, UserRepository $userRepository): Response
    {
        $q         = $request->query->get('q', '');
        $role      = $request->query->get('role', '');
        $status    = $request->query->get('status', '');
        $sort      = $request->query->get('sort', 'created_at');
        $direction = strtoupper((string) $request->query->get('direction', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $users = $userRepository->searchUsers($q, $role, $status, $sort, $direction);

        return $this->render('user/backoffice/users.html.twig', [
            'users'             => $users,
            'filter_query'      => $q,
            'filter_role'       => $role,
            'filter_status'     => $status,
            'filter_sort'       => $sort,
            'filter_direction'  => $direction,
            'roles_available'   => $userRepository->findDistinctRoles(),
            'statuses_available'=> $userRepository->findDistinctStatuses(),
        ]);
    }

    #[Route('/formations', name: 'app_backoffice_formations', methods: ['GET'])]
    public function formations(): Response
    {
        return $this->render('user/backoffice/section.html.twig', [
            'section_title' => 'Formations',
            'section_description' => 'Gestion des formations (placeholder).',
        ]);
    }

    #[Route('/posts', name: 'app_backoffice_posts', methods: ['GET'])]
    public function postsSection(): Response
    {
        return $this->render('user/backoffice/section.html.twig', [
            'section_title' => 'Postes',
            'section_description' => 'Gestion des postes (placeholder).',
        ]);
    }

    #[Route('/reclamations', name: 'app_backoffice_reclamations', methods: ['GET'])]
    public function reclamations(): Response
    {
        return $this->render('user/backoffice/section.html.twig', [
            'section_title' => 'Réclamations',
            'section_description' => 'Gestion des réclamations (placeholder).',
        ]);
    }

    #[Route('/interviews', name: 'app_backoffice_interviews', methods: ['GET'])]
    public function interviews(): Response
    {
        return $this->render('user/backoffice/section.html.twig', [
            'section_title' => 'Interviews',
            'section_description' => 'Gestion des interviews (placeholder).',
        ]);
    }

    #[Route('/offres', name: 'app_backoffice_offres', methods: ['GET'])]
    public function offres(): Response
    {
        return $this->render('user/backoffice/section.html.twig', [
            'section_title' => "Offres d'emploi",
            'section_description' => "Gestion des offres d'emploi (placeholder).",
        ]);
    }

    #[Route('/parametres', name: 'app_backoffice_parametres', methods: ['GET'])]
    public function parametres(): Response
    {
        return $this->render('user/backoffice/section.html.twig', [
            'section_title' => 'Paramètres',
            'section_description' => 'Paramètres backoffice (placeholder).',
        ]);
    }

    #[Route('/stats', name: 'app_backoffice_stats', methods: ['GET'])]
    public function stats(): Response
    {
        return $this->render('user/backoffice/section.html.twig', [
            'section_title' => 'Statistiques',
            'section_description' => 'Section statistiques (placeholder).',
        ]);
    }

    #[Route('/users/new', name: 'app_backoffice_users_new', methods: ['GET', 'POST'])]
    public function newUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher, UserRepository $userRepository): Response
    {
        $user = new Users();
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTime());

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existing = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($existing) {
                $this->addFlash('danger', 'Email déjà utilisé');
            } else {
                $plain = $form->get('plainPassword')->getData();
                $user->setPassword($hasher->hashPassword($user, $plain ?: 'Temp123!'));
                $em->persist($user);
                try {
                    $em->flush();
                    $this->addFlash('success', 'Utilisateur créé');
                    return $this->redirectToRoute('app_backoffice_users');
                } catch (UniqueConstraintViolationException $e) {
                    $this->addFlash('danger', 'Email déjà utilisé');
                }
            }
        }

        return $this->render('user/backoffice/user_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Ajouter un utilisateur',
        ]);
    }

    #[Route('/users/{id}/edit', name: 'app_backoffice_users_edit', methods: ['GET', 'POST'])]
    public function editUser(int $id, UserRepository $userRepository, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            $this->addFlash('danger', 'Utilisateur introuvable');
            return $this->redirectToRoute('app_backoffice_users');
        }

        $form = $this->createForm(UserType::class, $user, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existing = $userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($existing && $existing->getId() !== $user->getId()) {
                $this->addFlash('danger', 'Email déjà utilisé');
            } else {
                $plain = $form->get('plainPassword')->getData();
                if ($plain) {
                    $user->setPassword($hasher->hashPassword($user, $plain));
                }
                $user->setUpdatedAt(new \DateTime());
                try {
                    $em->flush();
                    $this->addFlash('success', 'Utilisateur mis à jour');
                    return $this->redirectToRoute('app_backoffice_users');
                } catch (UniqueConstraintViolationException $e) {
                    $this->addFlash('danger', 'Email déjà utilisé');
                }
            }
        }

        return $this->render('user/backoffice/user_form.html.twig', [
            'form' => $form->createView(),
            'title' => 'Modifier l’utilisateur',
        ]);
    }

    #[Route('/users/{id}', name: 'app_backoffice_users_delete', methods: ['POST'])]
    public function deleteUser(int $id, UserRepository $userRepository, Request $request, EntityManagerInterface $em): Response
    {
        $user = $userRepository->find($id);
        if ($user && $this->isCsrfTokenValid('delete_user_'.$user->getId(), $request->request->get('_token'))) {
            // Clean dependent notifications to satisfy FK constraints
            $em->createQuery('DELETE FROM App\Entity\Job_notification j WHERE j.candidate_id = :user')
                ->setParameter('user', $user)
                ->execute();
            $em->createQuery('DELETE FROM App\Entity\Saved_job_offer s WHERE s.candidate_id = :user')
                ->setParameter('user', $user)
                ->execute();

            $em->remove($user);
            $em->flush();
            $this->addFlash('success', 'Utilisateur supprimé');
        }
        return $this->redirectToRoute('app_backoffice_users');
    }
}
