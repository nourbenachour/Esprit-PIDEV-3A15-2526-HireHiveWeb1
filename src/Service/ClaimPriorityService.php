<?php

namespace App\Service;

/**
 * ClaimPriorityService – Feature 2: Automated Priority / Keyword Detection.
 *
 * When a user submits a new Claim, the ClaimController calls detectPriority()
 * with the claim description. If any of the defined keywords are found
 * (case-insensitive), the priority is automatically set to 'HIGH' before the
 * entity is persisted to the database.
 *
 * This gives support staff an immediate visual cue to handle urgent tickets first.
 *
 * Example:
 *   $priority = $this->priorityService->detectPriority('My service is broken, need refund');
 *   // → 'HIGH'  (matched "broken" and "refund")
 */
class ClaimPriorityService
{
    /**
     * Keywords that indicate a high-priority claim.
     * Adding more keywords here is the only change needed to extend detection.
     */
    private const HIGH_PRIORITY_KEYWORDS = [
        'urgent',
        'broken',
        'refund',
        'critical',
        'emergency',
        'not working',
        'cassé',          // French: broken
        'remboursement',  // French: refund
        'bloqué',         // French: blocked
        'impossible',
    ];

    /**
     * Scans the description text for any high-priority keyword.
     *
     * Uses case-insensitive matching (mb_stripos) so "URGENT", "Urgent",
     * and "urgent" all trigger the HIGH flag.
     *
     * @param string $description  The claim description submitted by the user.
     * @return string              'HIGH' if a keyword is found, 'NORMAL' otherwise.
     */
    public function detectPriority(string $description): string
    {
        foreach (self::HIGH_PRIORITY_KEYWORDS as $keyword) {
            // mb_stripos is multibyte-safe and case-insensitive
            if (mb_stripos($description, $keyword) !== false) {
                return 'URGENT';
            }
        }

        return 'LOW';
    }
}
