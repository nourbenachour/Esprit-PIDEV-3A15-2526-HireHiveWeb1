<?php

namespace App\Controller\interview;

use App\Entity\Questionnaire;
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
        $postedJobTitle = '';
        $postedQuestions = [''];

        if ($request->isMethod('POST')) {
            $postedJobTitle = trim((string) $request->request->get('jobTitle', ''));
            $questionRows = $request->request->all('questions');
            $rawQuestions = [];

            foreach ($questionRows as $row) {
                if (!is_array($row)) {
                    continue;
                }

                $rawQuestions[] = trim((string) ($row['text'] ?? ''));
            }

            $validQuestions = array_values(array_filter($rawQuestions, static fn (string $q): bool => $q !== ''));

            try {
                $this->service->validateJobTitle($postedJobTitle);

                if ($validQuestions === []) {
                    throw new \InvalidArgumentException('Ajoutez au moins une question.');
                }

                $nextId = $this->getNextQuestionnaireId();
                foreach ($validQuestions as $questionText) {
                    $this->service->validateQuestionText($questionText);

                    $questionnaire = $this->service->createQuestionnaire(
                        $postedJobTitle,
                        $questionText,
                        '',
                        ''
                    );
                    $questionnaire->setIdQuestionnaire($nextId++);
                    $this->em->persist($questionnaire);
                }

                $this->em->flush();

                $this->addFlash('success', 'Questionnaire créé avec succès');
                return $this->redirectToRoute('questionnaire_index');
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', 'Erreur: ' . $e->getMessage());
                $postedQuestions = $rawQuestions !== [] ? $rawQuestions : [''];
            }
        }

        return $this->render('interview/questionnaire/new.html.twig', [
            'jobTitle' => $postedJobTitle,
            'questions' => $postedQuestions,
        ]);
    }

    private function getNextQuestionnaireId(): int
    {
        $maxId = (int) $this->em->createQueryBuilder()
            ->select('COALESCE(MAX(q.idQuestionnaire), 0)')
            ->from(Questionnaire::class, 'q')
            ->getQuery()
            ->getSingleScalarResult();

        return $maxId + 1;
    }

    /**
     * Formulaire d'édition d'un questionnaire (plusieurs questions).
     */
    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request): Response
    {
        $questionnaire = $this->repository->find($id);

        if (!$questionnaire) {
            throw $this->createNotFoundException('Question non trouvée');
        }

        $existingQuestions = $this->repository->findByJobTitle((string) $questionnaire->getJobTitle());
        $postedJobTitle = (string) $questionnaire->getJobTitle();
        $rowsForView = array_map(static function (Questionnaire $item): array {
            return [
                'id' => (int) $item->getIdQuestionnaire(),
                'text' => (string) $item->getQuestionText(),
            ];
        }, $existingQuestions);

        if ($rowsForView === []) {
            $rowsForView = [['id' => null, 'text' => '']];
        }

        if ($request->isMethod('POST')) {
            $postedJobTitle = trim((string) $request->request->get('jobTitle', ''));
            $questionRows = $request->request->all('questions');

            $existingById = [];
            foreach ($existingQuestions as $existing) {
                $existingById[(int) $existing->getIdQuestionnaire()] = $existing;
            }

            $nextId = $this->getNextQuestionnaireId();
            $keptIds = [];
            $rowsForView = [];

            try {
                $this->service->validateJobTitle($postedJobTitle);

                foreach ($questionRows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }

                    $rawId = trim((string) ($row['id'] ?? ''));
                    $text = trim((string) ($row['text'] ?? ''));
                    $rowsForView[] = [
                        'id' => $rawId !== '' ? (int) $rawId : null,
                        'text' => $text,
                    ];

                    if ($text === '') {
                        continue;
                    }

                    $this->service->validateQuestionText($text);

                    if ($rawId !== '' && isset($existingById[(int) $rawId])) {
                        $entity = $existingById[(int) $rawId];
                        $entity->setJobTitle($postedJobTitle);
                        $entity->setQuestionText($text);
                        $entity->setUpdated_at(new \DateTime());
                        $keptIds[(int) $rawId] = true;
                        continue;
                    }

                    $newQuestion = $this->service->createQuestionnaire(
                        $postedJobTitle,
                        $text,
                        '',
                        ''
                    );
                    $newQuestion->setIdQuestionnaire($nextId++);
                    $this->em->persist($newQuestion);
                }

                if ($keptIds === [] && $this->hasNoPostedNonEmptyQuestion($questionRows)) {
                    throw new \InvalidArgumentException('Ajoutez au moins une question.');
                }

                foreach ($existingQuestions as $existing) {
                    $existingId = (int) $existing->getIdQuestionnaire();
                    if (!isset($keptIds[$existingId])) {
                        $this->em->remove($existing);
                    }
                }

                $this->em->flush();

                $this->addFlash('success', 'Questionnaire modifié avec succès');
                return $this->redirectToRoute('questionnaire_index');
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('error', 'Erreur: ' . $e->getMessage());
                if ($rowsForView === []) {
                    $rowsForView = [['id' => null, 'text' => '']];
                }
            }
        }

        return $this->render('interview/questionnaire/edit.html.twig', [
            'questionnaire' => $questionnaire,
            'jobTitle' => $postedJobTitle,
            'questions' => $rowsForView,
        ]);
    }

    private function hasNoPostedNonEmptyQuestion(array $questionRows): bool
    {
        foreach ($questionRows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $text = trim((string) ($row['text'] ?? ''));
            if ($text !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Suppression sécurisée (avec CSRF token).
     */
    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        $questionnaire = $this->repository->find($id);

        if (!$questionnaire) {
            throw $this->createNotFoundException('Question non trouvée');
        }

        if ($this->isCsrfTokenValid('delete' . $questionnaire->getIdQuestionnaire(), $request->request->get('_token'))) {
            $allQuestions = $this->repository->findByJobTitle((string) $questionnaire->getJobTitle());
            foreach ($allQuestions as $question) {
                $this->em->remove($question);
            }
            $this->em->flush();
            $this->addFlash('success', 'Questionnaire supprimé avec succès');
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('questionnaire_index');
    }
}
