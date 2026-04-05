<?php

namespace App\Service\User;

use Doctrine\DBAL\Connection;

/**
 * Fournit les données du profil utilisateur depuis la base piweb3a (table users et éventuellement experience).
 * Tolérant aux variations de noms de colonnes (camelCase / snake_case) et retourne des valeurs de secours si la BD est vide.
 */
final class PiwebProfileProvider
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function getProfile(?int $userId = null, bool $fallbackToFirstWhenNoId = true): array
    {
        try {
            if ($userId === null && $fallbackToFirstWhenNoId === false) {
                return $this->fallbackProfile();
            }

            $row = $this->fetchUser($userId);
            if ($row === null) {
                return $this->fallbackProfile();
            }

            $isRecruiter = $this->equals($row, 'role', 'RECRUITER');

            $profile = [
                'full_name' => $this->fullName($row) ?: 'Membre HireHive',
                'first_name' => $this->val($row, ['firstName', 'first_name', 'firstname']),
                'last_name' => $this->val($row, ['lastName', 'last_name', 'lastname']),
                'email' => $this->val($row, ['email'], 'Email non renseigné'),
                'phone' => $this->val($row, ['phone', 'telephone', 'tel'], 'Non renseigné'),
                'role' => $this->val($row, ['role'], 'CANDIDATE'),
                'is_active' => $this->boolVal($row, ['is_active', 'active'], true),
                'status' => $this->val($row, ['status'], 'USER_VERIFIED'),
                'bio' => $this->firstNonEmpty(
                    $this->val($row, ['bio', 'description']),
                    $this->val($row, ['recruiterCompanyBio', 'recruiter_company_bio']),
                    'Aucune description pour le moment.'
                ),
                'avatar_url' => $this->avatarUrl($row),
                'skills' => $this->skillsList($this->val($row, ['competances', 'competences', 'skills'])),
                'competences_raw' => $this->val($row, ['competances', 'competences', 'skills']),
                'formations' => $this->listFromCsv($this->val($row, ['formations', 'education', 'diplomes', 'formation'])),
                'formations_text' => $this->val($row, ['formations', 'education', 'diplomes', 'formation']),
                'experiences' => $this->experiences($row),
                'about' => $this->firstNonEmpty($this->val($row, ['bio', 'description']), ''),
                'experience_text' => $this->val($row, ['experience', 'experiences_text', 'description']), // legacy fallback
            ];

            $profile['recruiter'] = [
                'enabled' => $isRecruiter,
                'company_name' => $this->val($row, ['recruiterCompanyName', 'recruiter_company_name']),
                'company_logo' => $this->val($row, ['recruiterCompanyLogo', 'recruiter_company_logo']),
                'company_sector' => $this->val($row, ['recruiterSecteurActivite', 'recruiter_secteur_activite']),
                'company_website' => $this->val($row, ['recruiterCompanyWebsite', 'recruiter_company_website']),
                'company_address' => $this->val($row, ['recruiterAdresse', 'recruiter_adresse']),
                'company_contact_email' => $this->val($row, ['recruiterEmailContactEntreprise', 'recruiter_email_contact_entreprise']),
                'company_contact_phone' => $this->val($row, ['recruiterTelephoneServiceClient', 'recruiter_telephone_service_client']),
                'company_salaries' => $this->val($row, ['recruiterSalaires', 'recruiter_salaires']),
                'company_bio' => $this->val($row, ['recruiterCompanyBio', 'recruiter_company_bio']),
            ];

            return $profile;
        } catch (\Throwable) {
            return $this->fallbackProfile();
        }
    }

    private function fetchUser(?int $userId): ?array
    {
        $sql = <<<'SQL'
            SELECT 
                u.*,
                c.id_condidat,
                c.bio         AS bio,
                c.experience  AS experience,
                c.competances AS competances,
                c.formations  AS formations,
                c.education   AS education,
                r.company_name                AS recruiterCompanyName,
                r.company_logo                AS recruiterCompanyLogo,
                r.company_bio                 AS recruiterCompanyBio,
                r.company_website             AS recruiterCompanyWebsite,
                r.secteur_activite            AS recruiterSecteurActivite,
                r.email_contact_entreprise    AS recruiterEmailContactEntreprise,
                r.telephone_service_client    AS recruiterTelephoneServiceClient,
                r.salaires                    AS recruiterSalaires,
                r.adresse                     AS recruiterAdresse
            FROM users u
            LEFT JOIN condidat c ON c.user_id = u.id_user
            LEFT JOIN recruiter r ON r.user_id = u.id_user
            SQL;
        $params = [];
        if ($userId !== null) {
            $sql .= ' WHERE u.id_user = :id LIMIT 1';
            $params['id'] = $userId;
        } else {
            $sql .= ' ORDER BY u.id_user ASC LIMIT 1';
        }

        $row = $this->connection->fetchAssociative($sql, $params);

        return $row === false ? null : $row;
    }

    public function firstUserId(): ?int
    {
        $row = $this->connection->fetchOne('SELECT id_user FROM users ORDER BY id_user ASC LIMIT 1');
        return $row !== false ? (int) $row : null;
    }

    /**
     * @param array<string,mixed> $row
     */
    private function fullName(array $row): string
    {
        $fn = $this->val($row, ['firstName', 'first_name', 'firstname']);
        $ln = $this->val($row, ['lastName', 'last_name', 'lastname']);

        return trim(trim((string) $fn) . ' ' . trim((string) $ln));
    }

    /**
     * @param array<string,mixed> $row
     * @param list<string>        $keys
     */
    private function val(array $row, array $keys, string $default = ''): string
    {
        foreach ($keys as $k) {
            if (array_key_exists($k, $row) && $row[$k] !== null && $row[$k] !== '') {
                return (string) $row[$k];
            }
        }

        return $default;
    }

    /**
     * @param array<string,mixed> $row
     * @param list<string>        $keys
     */
    private function boolVal(array $row, array $keys, bool $default): bool
    {
        foreach ($keys as $k) {
            if (!array_key_exists($k, $row)) {
                continue;
            }
            $v = $row[$k];
            if ($v === null) {
                continue;
            }
            if (\is_bool($v)) {
                return $v;
            }
            return \in_array((string) $v, ['1', 'true', 'TRUE', 'yes', 'Y'], true);
        }

        return $default;
    }

    private function avatarUrl(array $row): string
    {
        $picture = $this->val($row, ['profilePicture', 'profile_picture', 'avatar']);
        if ($picture !== '') {
            $picture = trim((string) $picture);
            if (str_starts_with($picture, 'http') || str_starts_with($picture, 'file:')) {
                return $picture;
            }
            if (str_starts_with($picture, '/uploads/avatars/')) {
                return $picture;
            }
            if (str_starts_with($picture, 'uploads/avatars/')) {
                return '/'.$picture;
            }
            // assume filename or relative path
            return '/uploads/avatars/' . ltrim($picture, '/');
        }

        $fallback = urlencode($this->fullName($row) ?: 'HireHive User');

        return 'https://ui-avatars.com/api/?name=' . $fallback . '&background=6D28D9&color=fff';
    }

    private function firstNonEmpty(string ...$values): string
    {
        foreach ($values as $v) {
            if (trim($v) !== '') {
                return $v;
            }
        }

        return '';
    }

    private function skillsList(string $csv): array
    {
        if (trim($csv) === '') {
            return [];
        }

        $items = array_filter(array_map('trim', explode(',', $csv)), static fn ($s) => $s !== '');
        $items = array_map(static fn ($s) => mb_convert_case($s, MB_CASE_TITLE, 'UTF-8'), $items);

        return array_values(array_unique($items));
    }

    private function listFromCsv(string $csv): array
    {
        if (trim($csv) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $csv)), static fn ($s) => $s !== ''));
    }

    private function equals(array $row, string $key, string $expected): bool
    {
        $val = $this->val($row, [$key]);

        return strcasecmp($val, $expected) === 0;
    }

    private function experiences(array $row): array
    {
        $candidateId = $this->val($row, ['idCondidat', 'id_condidat', 'condidat_id']);
        if ($candidateId === '') {
            return $this->fallbackProfile()['experiences'];
        }

        try {
            $sql = <<<'SQL'
                SELECT id, title, company, period, description
                FROM experience
                WHERE condidat_id = :cid
                ORDER BY id DESC
                LIMIT 5
                SQL;
            $rows = $this->connection->fetchAllAssociative($sql, ['cid' => (int) $candidateId]);
            if ($rows === []) {
                return $this->fallbackProfile()['experiences'];
            }

            return array_map(static function (array $r): array {
                return [
                    'id' => (int) ($r['id'] ?? 0),
                    'title' => (string) ($r['title'] ?? 'Expérience'),
                    'company' => (string) ($r['company'] ?? ''),
                    'period' => (string) ($r['period'] ?? ''),
                    'description' => (string) ($r['description'] ?? ''),
                ];
            }, $rows);
        } catch (\Throwable) {
            return $this->fallbackProfile()['experiences'];
        }
    }

    /**
     * @return array<string,mixed>
     */
    public function fallbackProfile(): array
    {
        return [
            'full_name' => '',
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'role' => '',
            'is_active' => false,
            'status' => '',
            'bio' => '',
            'experience_text' => '',
            'competences_raw' => '',
            'formations_text' => '',
            'avatar_url' => 'https://ui-avatars.com/api/?name=H+H&background=6D28D9&color=fff',
            'skills' => [],
            'formations' => [],
            'experiences' => [],
            'about' => '',
            'recruiter' => [
                'enabled' => false,
                'company_name' => '',
                'company_sector' => '',
                'company_website' => '',
                'company_address' => '',
                'company_contact_email' => '',
                'company_contact_phone' => '',
                'company_salaries' => '',
                'company_bio' => '',
            ],
        ];
    }
}
