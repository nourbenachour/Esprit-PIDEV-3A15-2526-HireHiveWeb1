<?php

namespace App\Controller\User;

use App\Service\User\PiwebProfileProvider;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FrontofficeController extends AbstractController
{
    #[Route('/frontoffice', name: 'app_frontoffice')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_frontoffice_home');
    }

    #[Route('/frontoffice/home', name: 'app_frontoffice_home')]
    public function home(): Response
    {
        $data = [
            'total_offers' => 48,
            'total_trainings' => 12,
            'scheduled_interviews' => 5,
            'user_certifications' => 3,
            'recent_offers' => [
                ['title' => 'Développeur Java Senior', 'company' => 'Tech Solutions • Tunis', 'date' => 'Il y a 2j'],
                ['title' => 'Data Analyst Python', 'company' => 'DataCorp • Sousse', 'date' => 'Il y a 3j'],
                ['title' => 'Full Stack Angular + Spring', 'company' => 'InnoTech • Sfax', 'date' => 'Il y a 5j'],
                ['title' => 'DevOps Engineer', 'company' => 'CloudFirst • Remote', 'date' => 'Il y a 1 sem.'],
            ],
            'recommended_formations' => [
                ['title' => 'Java EE / Spring Boot', 'meta' => '156 inscrits • 78% complétion', 'badge' => 'Certifiant'],
                ['title' => 'Python Data Science', 'meta' => '98 inscrits • 82% complétion', 'badge' => 'Certifiant'],
                ['title' => 'Oracle DBA Fundamentals', 'meta' => '64 inscrits • 71% complétion', 'badge' => 'Certifiant'],
            ],
        ];

        $welcomeMessage = 'Bienvenue sur HireHive !';
        $user = $this->getUser();
        if ($user !== null && method_exists($user, 'getFirstName')) {
            $fn = $user->getFirstName();
            if (\is_string($fn) && $fn !== '') {
                $welcomeMessage = 'Bienvenue, '.$fn.' !';
            }
        }

        return $this->render('user/frontoffice/home.html.twig', array_merge($data, [
            'welcomeMessage' => $welcomeMessage,
        ]));
    }


    #[Route('/frontoffice/profile', name: 'app_frontoffice_profile')]
    public function profile(\Symfony\Component\HttpFoundation\Request $request, PiwebProfileProvider $profileProvider, Connection $connection, ValidatorInterface $validator): Response
    {
        $idFromQuery = $request->query->getInt('id', 0) ?: null;
        $user = $this->getUser();
        $skillsChoices = [
            'Java', 'Symfony', 'PHP', 'Laravel', 'Spring Boot', 'SQL', 'NoSQL', 'Docker', 'Kubernetes',
            'AWS', 'Azure', 'GCP', 'React', 'Angular', 'Vue', 'HTML/CSS', 'JavaScript', 'TypeScript',
            'CI/CD', 'Git', 'Scrum', 'Agile', 'Communication', 'Leadership', 'Problem Solving'
        ];

        // If no authenticated user and no explicit id in query, return an empty profile (all fields blank).
        if ($user === null && $idFromQuery === null) {
            // try to resolve the first user id to allow demo editing; if none, fallback profile
            $firstId = $profileProvider->firstUserId();
            if ($firstId === null) {
                return $this->render('user/frontoffice/profile.html.twig', $profileProvider->fallbackProfile());
            }
            $idFromQuery = $firstId;
        }

        // Try to resolve the authenticated user's id when not provided via query.
        $resolvedId = $idFromQuery;
        if ($resolvedId === null && $user !== null) {
            if (method_exists($user, 'getId') && \is_numeric($user->getId())) {
                $resolvedId = (int) $user->getId();
            } elseif (method_exists($user, 'getIdUser') && \is_numeric($user->getIdUser())) {
                $resolvedId = (int) $user->getIdUser();
            }
        }
        if ($resolvedId === null) {
            $firstId = $profileProvider->firstUserId();
            $resolvedId = $firstId;
        }

        // Preload profile and role
        $profileSnapshot = $profileProvider->getProfile($resolvedId, true);
        $roleUpper = strtoupper((string) ($profileSnapshot['role'] ?? 'CANDIDATE'));
        if ($roleUpper === 'RECRUITER') {
            $logo = $profileSnapshot['recruiter']['company_logo'] ?? '';
            if (\is_string($logo) && $logo !== '') {
                $profileSnapshot['avatar_url'] = $logo;
            }
        }
        $selectedSkills = array_filter(array_map('trim', explode(',', (string) ($profileSnapshot['competences_raw'] ?? ''))));
        $profileSnapshot['skills_choices'] = $skillsChoices;
        $profileSnapshot['competences_selected'] = $selectedSkills;

        if ($request->isMethod('POST') && $resolvedId !== null) {
            $data = $request->request;
            $action = (string) $data->get('action', '');
            $shouldUpdate = true;

            // Recruiter branch
            if ($roleUpper === 'RECRUITER' && $action === 'save_recruiter') {
                $validationErrors = [];

                $companyName   = trim((string) $data->get('company_name', ''));
                $companyBio    = trim((string) $data->get('company_bio', ''));
                $companySite   = trim((string) $data->get('company_website', ''));
                $sector        = trim((string) $data->get('secteur_activite', ''));
                $emailContact  = trim((string) $data->get('email_contact', ''));
                $telService    = trim((string) $data->get('telephone_service', ''));
                $salairesInput = (string) $data->get('salaires', '');
                $logoUrl       = trim((string) $data->get('company_logo', ''));
                $logoFile      = $request->files->get('company_logo_file');
                $adresse       = trim((string) $data->get('adresse', ''));

                $validationErrors = array_merge($validationErrors, $this->validateTextWithLetter($validator, $companyName, 'Nom de l’entreprise', true));
                $validationErrors = array_merge($validationErrors, $this->validateTextWithLetter($validator, $companyBio, 'Bio', true));
                $validationErrors = array_merge($validationErrors, $this->validateTextWithLetter($validator, $sector, 'Secteur', true));

                $validationErrors = array_merge(
                    $validationErrors,
                    $this->collectViolations($validator, $companySite, [
                        new Assert\NotBlank(message: 'Le site web est obligatoire.'),
                        new Assert\Url(message: 'Le site web doit être une URL valide.'),
                    ], 'Site web')
                );

                $validationErrors = array_merge(
                    $validationErrors,
                    $this->collectViolations($validator, $emailContact, [
                        new Assert\NotBlank(message: 'L’email de contact est obligatoire.'),
                        new Assert\Email(message: 'Email de contact invalide.'),
                    ], 'Email contact')
                );

                $validationErrors = array_merge(
                    $validationErrors,
                    $this->collectViolations($validator, $telService, [
                        new Assert\NotBlank(message: 'Le téléphone service client est obligatoire.'),
                        new Assert\Regex(pattern: '/[0-9]/', message: 'Le téléphone doit contenir au moins un chiffre.'),
                        new Assert\Regex(pattern: '/^[0-9 +().-]{3,}$/', message: 'Format du téléphone invalide.'),
                    ], 'Téléphone')
                );

                $validationErrors = array_merge(
                    $validationErrors,
                    $this->collectViolations($validator, $salairesInput, [
                        new Assert\NotBlank(message: 'Les salaires sont obligatoires.'),
                        new Assert\Regex(pattern: '/^\\d+$/', message: 'Les salaires doivent être un entier.'),
                        new Assert\PositiveOrZero(message: 'Les salaires doivent être ≥ 0.'),
                    ], 'Salaires')
                );

                // Adresse obligatoire (au moins une lettre)
                $validationErrors = array_merge(
                    $validationErrors,
                    $this->validateTextWithLetter($validator, $adresse, 'Adresse', true)
                );

                if (!$logoFile && $logoUrl === '') {
                    $validationErrors[] = 'Le logo est obligatoire (PNG).';
                }

                // Logo PNG seulement
                if ($logoFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    $validationErrors = array_merge(
                        $validationErrors,
                        $this->collectViolations($validator, $logoFile, [
                            new Assert\File(mimeTypes: ['image/png'], mimeTypesMessage: 'Le logo doit être un PNG.'),
                        ], 'Logo')
                    );
                } elseif ($logoUrl !== '' && !preg_match('/\\.png(\\?.*)?$/i', $logoUrl)) {
                    $validationErrors[] = 'Le logo doit être au format .png (URL ou chemin).';
                }

                if (!empty($validationErrors)) {
                    foreach ($validationErrors as $msg) {
                        $this->addFlash('error', $msg);
                    }
                    return $this->redirectToRoute('app_frontoffice_profile', ['id' => $resolvedId]);
                }

                // Ensure recruiter row exists
                $recId = $connection->fetchOne('SELECT id_recruiter FROM recruiter WHERE user_id = :id LIMIT 1', ['id' => $resolvedId]);
                if ($recId === false) {
                    $connection->executeStatement('INSERT INTO recruiter (user_id, secteur_activite, email_contact_entreprise, telephone_service_client, salaires) VALUES (:id, :sect, :email, :tel, :sal)', [
                        'id' => $resolvedId,
                        'sect' => '',
                        'email' => '',
                        'tel' => '',
                        'sal' => 0,
                    ]);
                    $recId = $connection->lastInsertId();
                }
                $params = [
                    'id' => $resolvedId,
                    'name' => $companyName,
                    'logo' => $logoUrl,
                    'bio' => $companyBio,
                    'site' => $companySite,
                    'sect' => $sector,
                    'email' => $emailContact,
                    'tel' => $telService,
                    'sal' => $salairesInput === '' ? 0 : (int) $salairesInput,
                    'addr' => $adresse,
                ];

                // Handle uploaded file: highest priority
                $uploaded = $request->files->get('company_logo_file');
                if ($uploaded instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    $uploadsDir = $this->getParameter('kernel.project_dir').'/public/uploads/logos';
                    if (!is_dir($uploadsDir)) {
                        @mkdir($uploadsDir, 0775, true);
                    }
                    $filename = uniqid('logo_', true).'.'.$uploaded->guessExtension();
                    $uploaded->move($uploadsDir, $filename);
                    $params['logo'] = '/uploads/logos/'.$filename;
                } elseif ($params['logo'] !== '' && preg_match('/^[A-Za-z]:\\\\|^\\\\\\\\|\\//', $params['logo'])) {
                    // Handle local file path for logo: copy to public/uploads/logos
                    $path = $params['logo'];
                    if (is_file($path)) {
                        $uploadsDir = $this->getParameter('kernel.project_dir').'/public/uploads/logos';
                        if (!is_dir($uploadsDir)) {
                            @mkdir($uploadsDir, 0775, true);
                        }
                        $dest = $uploadsDir.'/'.basename($path);
                        if (@copy($path, $dest)) {
                            $params['logo'] = '/uploads/logos/'.basename($dest);
                        }
                    }
                }
                $connection->executeStatement(
                    'UPDATE recruiter SET company_name = :name, company_logo = :logo, company_bio = :bio, company_website = :site, secteur_activite = :sect, email_contact_entreprise = :email, telephone_service_client = :tel, salaires = :sal, adresse = :addr WHERE user_id = :id',
                    $params
                );
                $this->addFlash('success', 'Profil recruteur mis à jour.');
                // Optionally mirror contact email/phone to users if present
                $userUpdates = [];
                $userParams = ['id' => $resolvedId];
                if ($data->has('account_email')) {
                    $userUpdates[] = 'email = :uemail';
                    $userParams['uemail'] = (string) $data->get('account_email', '');
                }
                if ($data->has('account_phone')) {
                    $userUpdates[] = 'phone = :uphone';
                    $userParams['uphone'] = (string) $data->get('account_phone', '');
                }
                if (!empty($userUpdates)) {
                    $connection->executeStatement(
                        sprintf('UPDATE users SET %s WHERE id_user = :id', implode(', ', $userUpdates)),
                        array_merge($userParams, ['id' => $resolvedId])
                    );
                }
                $this->addFlash('success', 'Profil recruteur mis à jour.');
                return $this->redirectToRoute('app_frontoffice_profile', ['id' => $resolvedId]);
            }

            $params = ['id' => $resolvedId];

            // Ensure condidat row exists and grab its id
            $condidatId = $connection->fetchOne('SELECT id_condidat FROM condidat WHERE user_id = :id LIMIT 1', ['id' => $resolvedId]);
            if ($condidatId === false) {
                $connection->executeStatement('INSERT INTO condidat (user_id) VALUES (:id)', ['id' => $resolvedId]);
                $condidatId = $connection->lastInsertId();
            }
            $condidatId = (int) $condidatId;
            $currentCondidat = $connection->fetchAssociative(
                'SELECT bio, competances, formations FROM condidat WHERE user_id = :id LIMIT 1',
                ['id' => $resolvedId]
            ) ?: [];

            // Experience CRUD (table experience)
            if ($action === 'add_experience') {
                $newTitle = (string) $data->get('exp_new_title', '');
                $newCompany = (string) $data->get('exp_new_company', '');
                $newPeriod = (string) $data->get('exp_new_period', '');
                $newDescription = (string) $data->get('exp_new_description', '');

                $errors = [];
                $errors = array_merge($errors, $this->validateTextWithLetter($validator, $newTitle, 'Intitulé', true));
                $errors = array_merge($errors, $this->validateTextWithLetter($validator, $newCompany, 'Entreprise', true));
                $errors = array_merge($errors, $this->validateTextWithLetter($validator, $newDescription, 'Description', true));
                $errors = array_merge($errors, $this->validatePeriod($newPeriod));

                if (!empty($errors)) {
                    foreach ($errors as $msg) {
                        $this->addFlash('error', $msg);
                    }
                    return $this->redirectToRoute('app_frontoffice_profile', ['id' => $resolvedId]);
                }

                $connection->executeStatement(
                    'INSERT INTO experience (condidat_id, title, company, period, description) VALUES (:cid, :title, :company, :period, :description)',
                    [
                        'cid' => $condidatId,
                        'title' => $newTitle,
                        'company' => $newCompany,
                        'period' => $newPeriod,
                        'description' => $newDescription,
                    ]
                );
                $this->addFlash('success', 'Expérience ajoutée.');
                return $this->redirectToRoute('app_frontoffice_profile', ['id' => $resolvedId]);
            }
            if ($action === 'delete_experience') {
                $delId = $data->getInt('exp_delete_id', 0);
                if ($delId > 0) {
                    $connection->executeStatement('DELETE FROM experience WHERE id = :id AND condidat_id = :cid', ['id' => $delId, 'cid' => $condidatId]);
                    $this->addFlash('success', 'Expérience supprimée.');
                }
                return $this->redirectToRoute('app_frontoffice_profile', ['id' => $resolvedId]);
            }
            if ($action === 'save_experiences') {
                $titles = $data->all('exp_title');
                $companies = $data->all('exp_company');
                $periods = $data->all('exp_period');
                $descs = $data->all('exp_description');
                $errors = [];

                if (is_array($titles)) {
                    foreach ($titles as $expId => $titleVal) {
                        $companyVal = (string) ($companies[$expId] ?? '');
                        $periodVal  = (string) ($periods[$expId] ?? '');
                        $descVal    = (string) ($descs[$expId] ?? '');

                        $errors = array_merge($errors, $this->validateTextWithLetter($validator, (string) $titleVal, 'Intitulé', true));
                        $errors = array_merge($errors, $this->validateTextWithLetter($validator, $companyVal, 'Entreprise', true));
                        $errors = array_merge($errors, $this->validateTextWithLetter($validator, $descVal, 'Description', true));
                        $errors = array_merge($errors, $this->validatePeriod($periodVal));
                    }
                }

                if (!empty($errors)) {
                    foreach ($errors as $msg) {
                        $this->addFlash('error', $msg);
                    }
                    return $this->redirectToRoute('app_frontoffice_profile', ['id' => $resolvedId]);
                }

                if (is_array($titles)) {
                    foreach ($titles as $expId => $titleVal) {
                        $eid = (int) $expId;
                        if ($eid <= 0) { continue; }
                        $connection->executeStatement(
                            'UPDATE experience SET title = :t, company = :c, period = :p, description = :d WHERE id = :id AND condidat_id = :cid',
                            [
                                't' => (string) ($titleVal ?? ''),
                                'c' => (string) ($companies[$expId] ?? ''),
                                'p' => (string) ($periods[$expId] ?? ''),
                                'd' => (string) ($descs[$expId] ?? ''),
                                'id' => $eid,
                                'cid' => $condidatId,
                            ]
                        );
                    }
                }
                $this->addFlash('success', 'Expériences mises à jour.');
                return $this->redirectToRoute('app_frontoffice_profile', ['id' => $resolvedId]);
            }

            if ($action === 'save_about') {
                $aboutVal = (string) $data->get('about', '');
                $errors = $this->validateTextWithLetter($validator, $aboutVal, 'À propos', true);
                if (!empty($errors)) {
                    foreach ($errors as $msg) {
                        $this->addFlash('error', $msg);
                    }
                    return $this->redirectToRoute('app_frontoffice_profile', ['id' => $resolvedId]);
                }
            }

            if ($action === 'save_formations') {
                $competencesVal = (string) $data->get('competences', '');
                $formationsVal  = (string) $data->get('formations', '');
                $errors = [];
                $errors = array_merge($errors, $this->validateTextWithLetter($validator, $competencesVal, 'Compétences', true));
                $errors = array_merge($errors, $this->validateTextWithLetter($validator, $formationsVal, 'Formations', true));
                if (!empty($errors)) {
                    foreach ($errors as $msg) {
                        $this->addFlash('error', $msg);
                    }
                    return $this->redirectToRoute('app_frontoffice_profile', ['id' => $resolvedId]);
                }
            }

            if ($action === 'save_account') {
                $errors = [];
                $emailVal = (string) $data->get('email', '');
                $profilePicVal = (string) $data->get('profilePicture', '');
                $profilePicFile = $request->files->get('profilePictureFile');

                $errors = array_merge(
                    $errors,
                    $this->collectViolations($validator, $emailVal, [
                        new Assert\NotBlank(message: 'Email obligatoire.'),
                        new Assert\Email(message: 'Format email invalide.'),
                    ], 'Email')
                );

                if ($profilePicFile) {
                    $errors = array_merge(
                        $errors,
                        $this->collectViolations($validator, $profilePicFile, [
                            new Assert\File(mimeTypes: ['image/png'], mimeTypesMessage: 'La photo de profil doit être un PNG.'),
                        ], 'Photo de profil')
                    );
                } elseif ($profilePicVal !== '' && !preg_match('/\\.png(\\?.*)?$/i', $profilePicVal)) {
                    $errors[] = 'La photo de profil doit être un PNG (.png).';
                }

                if (!empty($errors)) {
                    foreach ($errors as $msg) {
                        $this->addFlash('error', $msg);
                    }
                    return $this->redirectToRoute('app_frontoffice_profile', ['id' => $resolvedId]);
                }

                // Traiter l'upload ou le chemin local pour la photo de profil
                if ($profilePicFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                    $uploadsDir = $this->getParameter('kernel.project_dir').'/public/uploads/avatars';
                    if (!is_dir($uploadsDir)) {
                        @mkdir($uploadsDir, 0775, true);
                    }
                    $filename = uniqid('avatar_', true).'.'.$profilePicFile->guessExtension();
                    $profilePicFile->move($uploadsDir, $filename);
                    $profilePicVal = '/uploads/avatars/'.$filename;
                    $request->request->set('profilePicture', $profilePicVal);
                } elseif ($profilePicVal !== '') {
                    // Si l'utilisateur a saisi un chemin local absolu, on le copie vers uploads/avatars
                    if (!str_starts_with($profilePicVal, '/uploads/avatars/') && preg_match('/^[A-Za-z]:\\\\|^\\\\\\\\|^\\//', $profilePicVal) && is_file($profilePicVal)) {
                        $uploadsDir = $this->getParameter('kernel.project_dir').'/public/uploads/avatars';
                        if (!is_dir($uploadsDir)) {
                            @mkdir($uploadsDir, 0775, true);
                        }
                        $dest = $uploadsDir.'/'.basename($profilePicVal);
                        if (@copy($profilePicVal, $dest)) {
                            $profilePicVal = '/uploads/avatars/'.basename($dest);
                            $request->request->set('profilePicture', $profilePicVal);
                        }
                    }
                }
                // Mettre à jour l'avatar affiché immédiatement
                $profileSnapshot['avatar_url'] = $profilePicVal;

                // Forcer la sauvegarde de l'avatar dans la base même si aucune colonne détectée auparavant
                $picColumnsUsers = ['profilePicture','profile_picture','avatar','photo','image'];
                $picColumnsCnd   = ['profile_picture','avatar','photo','image'];
                $picUpdated = false;
                foreach ($picColumnsUsers as $col) {
                    if ($this->columnExists($connection, 'users', $col)) {
                        $updates['users'][] = $col.' = :pic';
                        $picUpdated = true;
                        break;
                    }
                }
                if (!$picUpdated) {
                    foreach ($picColumnsCnd as $col) {
                        if ($this->columnExists($connection, 'condidat', $col)) {
                            $updates['condidat'][] = $col.' = :pic';
                            break;
                        }
                    }
                }
                if ($profilePicVal !== '') {
                    $params['pic'] = $profilePicVal;
                }
            }

            // Build updates for users and condidat according to available columns
            $updates = [
                'users' => [],
                'condidat' => [],
            ];

            $aboutTarget       = $this->resolveColumnMulti($connection, ['users','condidat'], ['bio','description','about']);
            $experienceTarget  = null; // experience stored in dedicated table, not in users/condidat
            $competencesTarget = $this->resolveColumnMulti($connection, ['users','condidat'], ['competances','competences','skills']);
            // Stocker les formations dans la colonne education si elle existe (priorité), sinon formations
            $formationsTarget  = $this->resolveColumnMulti($connection, ['users','condidat'], ['education','formations','formation','diplomes']);
            $firstNameTarget   = $this->resolveColumnMulti($connection, ['users'], ['firstName','first_name','firstname']);
            $lastNameTarget    = $this->resolveColumnMulti($connection, ['users'], ['lastName','last_name','lastname']);
            $phoneTarget       = $this->resolveColumnMulti($connection, ['users'], ['phone','telephone','tel']);
            $profilePicTarget  = $this->resolveColumnMulti($connection, ['users'], ['profilePicture','profile_picture','avatar']);

            if ($aboutTarget) {
                $updates[$aboutTarget['table']][] = $aboutTarget['column'].' = :bio';
            }
            // experience is handled separately in table experience
            if ($competencesTarget) {
                $updates[$competencesTarget['table']][] = $competencesTarget['column'].' = :comp';
            }
            if ($formationsTarget) {
                $updates[$formationsTarget['table']][] = $formationsTarget['column'].' = :form';
            }

            // account fields stay in users
            // Only update if the field is present in POST (disabled fields are omitted)
            if ($data->has('email')) {
                $params['email'] = (string) $data->get('email', '');
                $updates['users'][] = 'email = :email';
            }
            if ($firstNameTarget && $data->has('accountFirstName'))  {
                $params['fn'] = (string) $data->get('accountFirstName', '');
                $updates[$firstNameTarget['table']][]  = $firstNameTarget['column'].' = :fn';
            }
            if ($lastNameTarget && $data->has('accountLastName'))   {
                $params['ln'] = (string) $data->get('accountLastName', '');
                $updates[$lastNameTarget['table']][]   = $lastNameTarget['column'].' = :ln';
            }
            if ($phoneTarget && $data->has('phone'))      {
                $params['phone'] = (string) $data->get('phone', '');
                $updates[$phoneTarget['table']][]      = $phoneTarget['column'].' = :phone';
            }
            if ($profilePicTarget && $data->has('profilePicture')) {
                $params['pic'] = (string) $data->get('profilePicture', '');
                $updates[$profilePicTarget['table']][] = $profilePicTarget['column'].' = :pic';
            }

            try {
                // Ensure condidat row exists
                $exists = $connection->fetchOne('SELECT 1 FROM condidat WHERE user_id = :id LIMIT 1', ['id' => $resolvedId]);
                if ($exists === false) {
                    $connection->executeStatement('INSERT INTO condidat (user_id) VALUES (:id)', ['id' => $resolvedId]);
                }

                // Preserve existing candidate fields when the form does not send them (disabled inputs)
                $params['bio']  = $data->has('about') ? (string) $data->get('about', '') : (string) ($currentCondidat['bio'] ?? '');
                $params['exp']  = (string) $data->get('experienceArea', '');
                $params['comp'] = $data->has('competences') ? (string) $data->get('competences', '') : (string) ($currentCondidat['competances'] ?? '');
                $params['form'] = $data->has('formations') ? (string) $data->get('formations', '') : (string) ($currentCondidat['formations'] ?? '');

                if ($shouldUpdate) {
                    // Always write candidate sections explicitly (except experience, now separate)
                    $formationCols = [];
                    foreach (['formations','education','formation','diplomes'] as $col) {
                        if ($this->columnExists($connection, 'condidat', $col)) {
                            $formationCols[] = $col.' = :form';
                        }
                    }
                    $formationClause = !empty($formationCols) ? implode(', ', $formationCols) : 'formations = :form';
                    $connection->executeStatement(
                        sprintf('UPDATE condidat SET bio = :bio, competances = :comp, %s WHERE user_id = :id', $formationClause),
                        $params
                    );

                    // Users fields if any
                    $userCols = array_filter($updates['users']);
                    if (!empty($userCols)) {
                        $connection->executeStatement(
                            sprintf('UPDATE users SET %s WHERE id_user = :id', implode(', ', $userCols)),
                            $params
                        );
                    }
                    $this->addFlash('success', 'Profil mis à jour.');
                }
            } catch (\Throwable $e) {
                $this->addFlash('error', 'Erreur enregistrement : '.$e->getMessage());
            }

            return $this->redirectToRoute('app_frontoffice_profile', ['id' => $resolvedId]);
        }

        // For recruiters, render dedicated profile page
        if ($roleUpper === 'RECRUITER') {
        return $this->render('user/frontoffice/profile_recruiter.html.twig', $profileSnapshot);
    }

        return $this->render('user/frontoffice/profile.html.twig', $profileSnapshot);
    }

    /**
     * Validate that text is not blank (if required) and contains at least one letter.
     *
     * @return string[] list of error messages
     */
    private function validateTextWithLetter(ValidatorInterface $validator, string $value, string $label, bool $required = false): array
    {
        $constraints = [];
        if ($required) {
            $constraints[] = new Assert\NotBlank(message: sprintf('%s est obligatoire.', $label));
        }
        $constraints[] = new Assert\Regex(pattern: '/\\p{L}/u', message: sprintf('%s doit contenir au moins une lettre.', $label));

        return $this->collectViolations($validator, $value, $constraints, $label);
    }

    /**
     * Validate experience period format YYYY-YYYY and start <= end.
     *
     * @return string[] list of error messages
     */
    private function validatePeriod(string $period): array
    {
        if ($period === '') {
            return ['La période est obligatoire.'];
        }
        if (!preg_match('/^(\\d{4})-(\\d{4})$/', $period, $m)) {
            return ['La période doit suivre le format YYYY-YYYY.'];
        }
        if ((int) $m[1] > (int) $m[2]) {
            return ['L’année de début doit être inférieure ou égale à l’année de fin.'];
        }
        return [];
    }

    /**
     * Collect violations for a single value.
     *
     * @param array<int,Assert\Constraint> $constraints
     * @return string[]
     */
    private function collectViolations(ValidatorInterface $validator, mixed $value, array $constraints, string $label): array
    {
        $violations = $validator->validate($value, $constraints);
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = $violation->getMessage();
        }
        return $messages;
    }

    private function resolveColumn(Connection $connection, array $candidates): ?string
    {
        foreach ($candidates as $col) {
            try {
                $connection->executeQuery(sprintf('SELECT %s FROM users LIMIT 1', $col));
                return $col;
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    /**
     * Retourne la première colonne trouvée parmi $columns dans les tables listées.
     *
     * @return array{table:string,column:string}|null
     */
    private function resolveColumnMulti(Connection $connection, array $tables, array $columns): ?array
    {
        foreach ($tables as $table) {
            foreach ($columns as $col) {
                if ($this->columnExists($connection, $table, $col)) {
                    return ['table' => $table, 'column' => $col];
                }
            }
        }

        return null;
    }

    private function columnExists(Connection $connection, string $table, string $column): bool
    {
        try {
            $exists = $connection->fetchOne(
                'SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = :t AND column_name = :c LIMIT 1',
                ['t' => $table, 'c' => $column]
            );

            return $exists !== false;
        } catch (\Throwable) {
            return false;
        }
    }

    #[Route('/frontoffice/entretiens', name: 'app_frontoffice_interviews_alias')]
    public function interviews(): Response
    {
        return $this->redirectToRoute('app_frontoffice_interviews');
    }

    #[Route('/frontoffice/reclamations', name: 'app_frontoffice_reclamations')]
    public function reclamations(): Response
    {
        return $this->render('user/frontoffice/placeholder.html.twig', [
            'page_title' => 'Réclamations',
        ]);
    }

    #[Route('/frontoffice/offers', name: 'app_frontoffice_offers')]
    public function offers(): Response
    {
        return $this->render('user/frontoffice/placeholder.html.twig', [
            'page_title' => "Offres d'emploi",
        ]);
    }

    #[Route('/frontoffice/trainings', name: 'app_frontoffice_trainings')]
    public function trainings(): Response
    {
        return $this->render('user/frontoffice/placeholder.html.twig', [
            'page_title' => 'Formations',
        ]);
    }

    #[Route('/frontoffice/reviews', name: 'app_frontoffice_reviews')]
    public function reviews(): Response
    {
        return $this->render('user/frontoffice/placeholder.html.twig', [
            'page_title' => 'Avis',
        ]);
    }

    #[Route('/frontoffice/posts', name: 'app_frontoffice_posts')]
    public function posts(): Response
    {
        return $this->render('user/frontoffice/placeholder.html.twig', [
            'page_title' => 'Posts',
        ]);
    }

    #[Route('/frontoffice/messages', name: 'app_frontoffice_messages')]
    public function messages(): Response
    {
        return $this->render('user/frontoffice/placeholder.html.twig', [
            'page_title' => 'Messagerie',
        ]);
    }
}
