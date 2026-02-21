<?php
namespace App\Models;

use App\Core\Database;

/**
 * ClientProfile Model
 * Manages client-specific profile data, consent, and demographic information
 */
class ClientProfile {
    private $db;

    public function __construct() {
        try {
            $this->db = Database::getInstance();
        } catch (\Exception $e) {
            // Database not available in test/dev environment
            $this->db = null;
        }
    }

    /**
     * Create new client profile
     */
    public function create($userId, $profileData = []) {
        $data = [
            'user_id' => $userId,
            'preferred_language' => $profileData['preferred_language'] ?? 'English',
            'preferred_name' => $profileData['preferred_name'] ?? null,
            'age_range' => $profileData['age_range'] ?? null,
            'contact_preference' => $profileData['contact_preference'] ?? 'SMS',
            'consent_to_coordinate' => $profileData['consent_to_coordinate'] ?? 'full',
            'connection_method' => $profileData['connection_method'] ?? 'Street outreach',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];

        $clientId = $this->db->insert('client_profiles', $data);

        // Log consent preference
        if (isset($profileData['consent_to_coordinate'])) {
            $this->logConsentChange($clientId, null, $profileData['consent_to_coordinate'], null, "Initial consent during registration");
        }

        return $clientId;
    }

    /**
     * Get client profile by ID
     */
    public function getById($clientId) {
        $this->db->query(
            "SELECT cp.*, u.username, u.email FROM client_profiles cp
             JOIN users u ON cp.user_id = u.user_id
             WHERE cp.client_id = :client_id",
            [':client_id' => $clientId]
        );
        return $this->db->fetch();
    }

    /**
     * Get client profile by user ID
     */
    public function getByUserId($userId) {
        $this->db->query(
            "SELECT * FROM client_profiles WHERE user_id = :user_id",
            [':user_id' => $userId]
        );
        return $this->db->fetch();
    }

    /**
     * Update client profile
     */
    public function update($clientId, $data) {
        $this->db->update(
            'client_profiles',
            $data,
            'client_id = :client_id',
            [':client_id' => $clientId]
        );
    }

    /**
     * Update consent preference
     */
    public function updateConsent($clientId, $newConsent, $changedBy = null, $reason = null) {
        $profile = $this->getById($clientId);
        $previousConsent = $profile['consent_to_coordinate'];

        // Update profile
        $this->update($clientId, ['consent_to_coordinate' => $newConsent]);

        // Log change
        $this->logConsentChange($clientId, $previousConsent, $newConsent, $changedBy, $reason);
    }

    /**
     * Log consent change (for Four-Filter compliance audit)
     */
    public function logConsentChange($clientId, $previousConsent, $newConsent, $changedBy, $reason) {
        $this->db->insert('consent_changes', [
            'client_id' => $clientId,
            'previous_consent' => $previousConsent,
            'new_consent' => $newConsent,
            'changed_by' => $changedBy,
            'reason' => $reason,
            'changed_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Set nickname/preferred name
     */
    public function setPreferredName($clientId, $preferredName) {
        $this->db->update(
            'client_profiles',
            ['preferred_name' => $preferredName],
            'client_id = :client_id',
            [':client_id' => $clientId]
        );
    }

    /**
     * Get display name (preferred name or actual name)
     */
    public function getDisplayName($clientId) {
        $profile = $this->getById($clientId);

        if ($profile['preferred_name']) {
            return $profile['preferred_name'];
        }

        if ($profile['first_name'] && $profile['last_name']) {
            return "{$profile['first_name']} {$profile['last_name']}";
        }

        return $profile['username'];
    }

    /**
     * Mark intake as started
     */
    public function markIntakeStarted($clientId) {
        $this->update($clientId, ['intake_started_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Mark intake as completed
     */
    public function markIntakeCompleted($clientId) {
        $this->update($clientId, [
            'intake_completed_at' => date('Y-m-d H:i:s'),
            'status' => 'completed'
        ]);
    }

    /**
     * Get consent history
     */
    public function getConsentHistory($clientId) {
        $this->db->query(
            "SELECT cc.*, u.username FROM consent_changes cc
             LEFT JOIN users u ON cc.changed_by = u.user_id
             WHERE cc.client_id = :client_id
             ORDER BY cc.changed_at DESC",
            [':client_id' => $clientId]
        );
        return $this->db->fetchAll();
    }

    /**
     * Check if client has intake completed
     */
    public function hasIntakeCompleted($clientId) {
        $profile = $this->getById($clientId);
        return $profile['intake_completed_at'] !== null;
    }

    /**
     * Get all active clients (for staff dashboards)
     */
    public function getAllActive($limit = 50, $offset = 0) {
        $this->db->query(
            "SELECT cp.*, u.username FROM client_profiles cp
             JOIN users u ON cp.user_id = u.user_id
             WHERE cp.status = 'active'
             ORDER BY cp.created_at DESC
             LIMIT :limit OFFSET :offset",
            [':limit' => $limit, ':offset' => $offset]
        );
        return $this->db->fetchAll();
    }

    /**
     * Add tag to client
     */
    public function addTag($clientId, $tagName) {
        // Get tag ID
        $this->db->query(
            "SELECT tag_id FROM tags WHERE tag_name = :tag_name",
            [':tag_name' => $tagName]
        );
        $tag = $this->db->fetch();

        if ($tag) {
            try {
                $this->db->insert('client_tags', [
                    'client_id' => $clientId,
                    'tag_id' => $tag['tag_id']
                ]);
            } catch (\Exception $e) {
                // Tag already exists, ignore
            }
        }
    }

    /**
     * Get client tags
     */
    public function getTags($clientId) {
        $this->db->query(
            "SELECT t.* FROM tags t
             JOIN client_tags ct ON t.tag_id = ct.tag_id
             WHERE ct.client_id = :client_id",
            [':client_id' => $clientId]
        );
        return $this->db->fetchAll();
    }
}
