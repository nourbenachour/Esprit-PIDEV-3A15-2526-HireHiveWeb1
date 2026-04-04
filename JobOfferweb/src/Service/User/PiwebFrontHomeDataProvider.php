<?php

namespace App\Service\User;

use Doctrine\DBAL\Connection;

/**
 * Lit les compteurs et listes de l'accueil front office depuis la base MySQL piweb3a
 * (tables job_offer, course, inscription, interview, certificate, users), comme le projet Java.
 */
final class PiwebFrontHomeDataProvider
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function getHomeData(): array
    {
        try {
            return [
                'total_offers' => $this->countJobOffers(),
                'total_trainings' => $this->countCourses(),
                'scheduled_interviews' => $this->countUpcomingInterviews(),
                'user_certifications' => $this->countCertificates(),
                'recent_offers' => $this->fetchRecentOffers(),
                'recommended_formations' => $this->fetchRecommendedFormations(),
            ];
        } catch (\Throwable) {
            return $this->fallbackData();
        }
    }

    private function countJobOffers(): int
    {
        return (int) $this->connection->fetchOne('SELECT COUNT(*) FROM job_offer');
    }

    private function countCourses(): int
    {
        return (int) $this->connection->fetchOne('SELECT COUNT(*) FROM course');
    }

    private function countUpcomingInterviews(): int
    {
        $sql = <<<'SQL'
            SELECT COUNT(*) FROM interview
            WHERE interviewDate IS NOT NULL AND interviewDate >= CURDATE()
            SQL;

        return (int) $this->connection->fetchOne($sql);
    }

    private function countCertificates(): int
    {
        return (int) $this->connection->fetchOne('SELECT COUNT(*) FROM certificate');
    }

    /**
     * @return list<array{title: string, company: string, date: string}>
     */
    private function fetchRecentOffers(): array
    {
        $sql = <<<'SQL'
            SELECT j.title, j.location, j.created_at, u.email AS recruiter_name
            FROM job_offer j
            LEFT JOIN users u ON j.recruiter_id = u.id_user
            ORDER BY j.created_at DESC
            LIMIT 4
            SQL;

        $rows = $this->connection->fetchAllAssociative($sql);
        $out = [];
        foreach ($rows as $row) {
            $recruiter = $row['recruiter_name'] ?? '';
            $loc = $row['location'] ?? '';
            $company = $recruiter && $loc ? $recruiter.' • '.$loc : ($recruiter ?: $loc ?: '—');
            $out[] = [
                'title' => (string) ($row['title'] ?? ''),
                'company' => $company,
                'date' => $this->formatRelativeDate($row['created_at'] ?? null),
            ];
        }

        return $out !== [] ? $out : $this->fallbackData()['recent_offers'];
    }

    /**
     * @return list<array{title: string, meta: string, badge: string}>
     */
    private function fetchRecommendedFormations(): array
    {
        $sql = <<<'SQL'
            SELECT c.id_course, c.title,
                (SELECT COUNT(*) FROM inscription i WHERE i.course_id = c.id_course) AS nb_inscrits,
                (SELECT COUNT(*) FROM inscription i WHERE i.course_id = c.id_course AND i.completed_at IS NOT NULL) AS nb_done
            FROM course c
            ORDER BY nb_inscrits DESC, c.id_course DESC
            LIMIT 3
            SQL;

        $rows = $this->connection->fetchAllAssociative($sql);
        $out = [];
        foreach ($rows as $row) {
            $nb = (int) ($row['nb_inscrits'] ?? 0);
            $done = (int) ($row['nb_done'] ?? 0);
            $pct = $nb > 0 ? (int) round(100 * $done / $nb) : 0;
            $out[] = [
                'title' => (string) ($row['title'] ?? ''),
                'meta' => sprintf('%d inscrits • %d%% complétion', $nb, $pct),
                'badge' => 'Certifiant',
            ];
        }

        return $out !== [] ? $out : $this->fallbackData()['recommended_formations'];
    }

    private function formatRelativeDate(null|string $sqlDate): string
    {
        if ($sqlDate === null || $sqlDate === '') {
            return '—';
        }
        try {
            $dt = new \DateTimeImmutable($sqlDate);
        } catch (\Exception) {
            return '—';
        }
        $now = new \DateTimeImmutable();
        $diff = $now->getTimestamp() - $dt->getTimestamp();
        if ($diff < 0) {
            return 'À venir';
        }
        $days = (int) floor($diff / 86400);
        if ($days === 0) {
            $h = (int) floor($diff / 3600);

            return $h < 1 ? "À l'instant" : sprintf('Il y a %dh', $h);
        }
        if ($days === 1) {
            return 'Hier';
        }
        if ($days < 7) {
            return sprintf('Il y a %dj', $days);
        }
        if ($days < 30) {
            return sprintf('Il y a %d sem.', max(1, (int) round($days / 7)));
        }

        return $dt->format('d/m/Y');
    }

    /**
     * Données statiques identiques au FXML Java (FrontHome.fxml) si la base est vide ou inaccessible.
     *
     * @return array{
     *   total_offers: int,
     *   total_trainings: int,
     *   scheduled_interviews: int,
     *   user_certifications: int,
     *   recent_offers: list<array{title: string, company: string, date: string}>,
     *   recommended_formations: list<array{title: string, meta: string, badge: string}>
     * }
     */
    private function fallbackData(): array
    {
        return [
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
    }
}
