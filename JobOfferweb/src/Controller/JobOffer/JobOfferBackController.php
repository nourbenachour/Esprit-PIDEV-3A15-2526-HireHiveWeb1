<?php

namespace App\Controller\JobOffer;

use App\Repository\JobOfferRepository;
use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/backoffice/job')]
#[IsGranted('ROLE_ADMIN')]
class JobOfferBackController extends AbstractController
{
    #[Route('/offres', name: 'app_joboffer_back_list', methods: ['GET'])]
    public function list(Request $request, JobOfferRepository $repo): Response
    {
        $search = $request->query->get('q', '');
        $status = $request->query->get('status', '');
        $contractType = $request->query->get('contract_type', '');
        $offers = $repo->findAll($search, $status, $contractType);

        return $this->render('joboffer/backoffice/offres.html.twig', [
            'offers' => $offers,
            'filter_query' => $search,
            'filter_status' => $status,
            'filter_contract_type' => $contractType,
        ]);
    }

    #[Route('/offres/new', name: 'app_joboffer_back_new', methods: ['GET', 'POST'])]
    public function create(Request $request, JobOfferRepository $repo): Response
    {
        if ($request->isMethod('POST')) {
            $data = $request->request;
            $errors = $this->validateOffreData($data);

            if (!empty($errors)) {
                foreach ($errors as $msg) { $this->addFlash('error', $msg); }
                return $this->render('joboffer/backoffice/offre_form.html.twig', [
                    'title' => "Ajouter une offre d'emploi",
                    'offre' => $data->all(),
                    'is_edit' => false,
                ]);
            }

            $user = $this->getUser();
            $recruiterId = $user ? ($user->getId() ?? 1) : 1;

            $repo->insert([
                'title' => trim($data->get('title', '')),
                'description' => trim($data->get('description', '')),
                'location' => trim($data->get('location', '')),
                'contract_type' => $data->get('contract_type', 'CDI'),
                'status' => $data->get('status', 'OPEN'),
                'skills' => trim($data->get('skills', '')),
                'soft_skills' => trim($data->get('soft_skills', '')),
                'recruiter_id' => $recruiterId,
            ]);

            $this->addFlash('success', "Offre d'emploi créée avec succès.");
            return $this->redirectToRoute('app_joboffer_back_list');
        }

        return $this->render('joboffer/backoffice/offre_form.html.twig', [
            'title' => "Ajouter une offre d'emploi",
            'offre' => [],
            'is_edit' => false,
        ]);
    }

    #[Route('/offres/{id}/edit', name: 'app_joboffer_back_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, JobOfferRepository $repo): Response
    {
        $offre = $repo->findById($id);
        if (!$offre) {
            $this->addFlash('error', 'Offre introuvable.');
            return $this->redirectToRoute('app_joboffer_back_list');
        }

        if ($request->isMethod('POST')) {
            $data = $request->request;
            $errors = $this->validateOffreData($data);

            if (!empty($errors)) {
                foreach ($errors as $msg) { $this->addFlash('error', $msg); }
                return $this->render('joboffer/backoffice/offre_form.html.twig', [
                    'title' => "Modifier l'offre d'emploi",
                    'offre' => array_merge($offre, $data->all()),
                    'is_edit' => true,
                ]);
            }

            $repo->update($id, [
                'title' => trim($data->get('title', '')),
                'description' => trim($data->get('description', '')),
                'location' => trim($data->get('location', '')),
                'contract_type' => $data->get('contract_type', 'CDI'),
                'status' => $data->get('status', 'OPEN'),
                'skills' => trim($data->get('skills', '')),
                'soft_skills' => trim($data->get('soft_skills', '')),
            ]);

            $this->addFlash('success', "Offre mise à jour avec succès.");
            return $this->redirectToRoute('app_joboffer_back_list');
        }

        return $this->render('joboffer/backoffice/offre_form.html.twig', [
            'title' => "Modifier l'offre d'emploi",
            'offre' => $offre,
            'is_edit' => true,
        ]);
    }

    #[Route('/offres/{id}/delete', name: 'app_joboffer_back_delete', methods: ['POST'])]
    public function deleteOffre(int $id, Request $request, JobOfferRepository $repo): Response
    {
        if ($this->isCsrfTokenValid('delete_offre_' . $id, $request->request->get('_token'))) {
            $repo->delete($id);
            $this->addFlash('success', "Offre supprimée.");
        }
        return $this->redirectToRoute('app_joboffer_back_list');
    }

    #[Route('/offres/{id}/candidatures', name: 'app_joboffer_back_candidatures', methods: ['GET'])]
    public function candidatures(int $id, JobOfferRepository $offerRepo, ApplicationRepository $appRepo): Response
    {
        $offre = $offerRepo->findById($id);
        if (!$offre) {
            $this->addFlash('error', 'Offre introuvable.');
            return $this->redirectToRoute('app_joboffer_back_list');
        }
        $applications = $appRepo->findByJobOffer($id);

        return $this->render('joboffer/backoffice/candidatures.html.twig', [
            'offre' => $offre,
            'applications' => $applications,
        ]);
    }

    #[Route('/candidatures/{id}/status', name: 'app_joboffer_back_candidature_status', methods: ['POST'])]
    public function updateStatus(int $id, Request $request, ApplicationRepository $appRepo): Response
    {
        $status = $request->request->get('status', '');
        if (!in_array($status, ['ACCEPTED', 'REJECTED', 'PENDING'])) {
            $this->addFlash('error', 'Statut invalide.');
            return $this->redirectToRoute('app_joboffer_back_list');
        }
        $app = $appRepo->findById($id);
        if (!$app) {
            $this->addFlash('error', 'Candidature introuvable.');
            return $this->redirectToRoute('app_joboffer_back_list');
        }
        $appRepo->updateStatus($id, $status);
        $this->addFlash('success', 'Statut de la candidature mis à jour.');
        return $this->redirectToRoute('app_joboffer_back_candidatures', ['id' => $app['job_offer_id']]);
    }

    private function validateOffreData($data): array
    {
        $errors = [];
        $title = trim((string) $data->get('title', ''));
        $desc = trim((string) $data->get('description', ''));
        $loc = trim((string) $data->get('location', ''));
        $skills = trim((string) $data->get('skills', ''));

        if ($title === '' || mb_strlen($title) < 3) { $errors[] = 'Le titre est obligatoire (min 3 caractères).'; }
        if ($title !== '' && !preg_match('/\p{L}/u', $title)) { $errors[] = 'Le titre doit contenir au moins une lettre.'; }
        if ($desc === '' || mb_strlen($desc) < 20) { $errors[] = 'La description est obligatoire (min 20 caractères).'; }
        if ($loc === '') { $errors[] = 'La localisation est obligatoire.'; }
        if ($loc !== '' && !preg_match('/\p{L}/u', $loc)) { $errors[] = 'La localisation doit contenir au moins une lettre.'; }
        if ($skills === '') { $errors[] = 'Les compétences requises sont obligatoires.'; }

        return $errors;
    }
}
