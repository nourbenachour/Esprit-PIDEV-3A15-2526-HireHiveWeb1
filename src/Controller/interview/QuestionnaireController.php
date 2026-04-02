<?php

namespace App\Controller\interview;

use App\Entity\Questionnaire;
use App\Form\interview\QuestionnaireType;
use App\Repository\interview\QuestionnaireRepository;
use App\Service\interview\QuestionnaireService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/questionnaire', name: 'questionnaire_')]
class QuestionnaireController extends AbstractController
{
    public function __construct(
        private QuestionnaireRepository $repository,
        private QuestionnaireService $service,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Liste tous les questionnaires (groupés par jobTitle).
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $jobTitles = $this->repository->findUniqueJobTitles();
        $questionnairesByJobTitle = [];

        foreach ($jobTitles as $jobTitle) {
            $questionnaires = $this->repository->findByJobTitle($jobTitle);
            $questionnairesByJobTitle[$jobTitle] = array_map(
                fn (Questionnaire $questionnaire) => $this->buildSafeQuestionnaireRow($questionnaire),
                $questionnaires
            );
        }

        return $this->render('interview/questionnaire/index.html.twig', [
            'questionnairesByJobTitle' => $questionnairesByJobTitle,
        ]);
    }

    /**
     * Build a scalar row to avoid accessing uninitialized typed properties in Twig.
     */
    private function buildSafeQuestionnaireRow(Questionnaire $questionnaire): array
    {
        $id = $questionnaire->getIdQuestionnaire();
        $questionText = $this->safeString(fn () => $questionnaire->getQuestionText());
        $correctAnswer = $this->safeString(fn () => $questionnaire->getCorrectAnswer());

        $createdAt = null;
        try {
            $createdAt = $questionnaire->getCreated_at();
        } catch (\Error) {
            $createdAt = null;
        }

        return [
            'id' => $id,
            'questionText' => $questionText,
            'correctAnswer' => $correctAnswer,
            'createdAt' => $createdAt,
        ];
    }

    private function safeString(callable $callback): ?string
    {
        try {
            $value = $callback();
            if ($value === null) {
                return null;
            }

            $stringValue = trim((string) $value);
            return $stringValue === '' ? null : $stringValue;
        } catch (\Error) {
            return null;
        }
    }

    /**
     * Formulaire de création d'un nouveau questionnaire.
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $questionnaire = new Questionnaire();
        $form = $this->createForm(QuestionnaireType::class, $questionnaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->service->validateJobTitle($questionnaire->getJobTitle());
                $this->service->validateQuestionText($questionnaire->getQuestionText());
                $this->service->validateAnswerOptions($questionnaire->getAnswerOptions());

                $this->em->persist($questionnaire);
                $this->em->flush();

                $this->addFlash('success', 'Question créée avec succès');
                return $this->redirectToRoute('questionnaire_index');
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return $this->render('interview/questionnaire/new.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Formulaire d'édition d'une question.
     */
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $questionnaire = $this->em->getRepository(Questionnaire::class)->find($id);

        if (!$questionnaire) {
            throw $this->createNotFoundException('Question non trouvée');
        }

        $form = $this->createForm(QuestionnaireType::class, $questionnaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->service->validateJobTitle($questionnaire->getJobTitle());
                $this->service->validateQuestionText($questionnaire->getQuestionText());
                $this->service->validateAnswerOptions($questionnaire->getAnswerOptions());

                $this->service->updateTimestamp($questionnaire);
                $this->addFlash('success', 'Question modifiée avec succès');
                return $this->redirectToRoute('questionnaire_index');
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', 'Erreur: ' . $e->getMessage());
            }
        }

        return $this->render('interview/questionnaire/edit.html.twig', [
            'form' => $form,
            'questionnaire' => $questionnaire,
        ]);
    }

    /**
     * Suppression sécurisée (avec CSRF token).
     */
    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        $questionnaire = $this->em->getRepository(Questionnaire::class)->find($id);

        if (!$questionnaire) {
            throw $this->createNotFoundException('Question non trouvée');
        }

        if ($this->isCsrfTokenValid('delete' . $questionnaire->getIdQuestionnaire(), $request->request->get('_token'))) {
            $this->em->remove($questionnaire);
            $this->em->flush();
            $this->addFlash('success', 'Question supprimée avec succès');
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('questionnaire_index');
    }
}
