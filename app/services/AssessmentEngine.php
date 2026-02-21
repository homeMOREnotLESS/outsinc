<?php
namespace App\Services;

use App\Core\Database;
use App\Core\Logger;

/**
 * Assessment Engine
 * Handles smart 60-question intake assessment with conditional branching logic
 */
class AssessmentEngine {
    private $db;
    private $logger;
    private $questions = [];
    private $branchingRules = [];

    public function __construct() {
        try {
            $this->db = Database::getInstance();
        } catch (\Exception $e) {
            // Database not available - can still load questions from JSON
            $this->db = null;
        }
        $this->logger = new Logger();
        $this->loadQuestions();
        $this->loadBranchingRules();
    }

    /**
     * Load all 60 questions from questions.json
     */
    private function loadQuestions() {
        $questionsFile = dirname(__DIR__) . '/data/questions.json';
        if (file_exists($questionsFile)) {
            $this->questions = json_decode(file_get_contents($questionsFile), true);
        }
    }

    /**
     * Load branching rules from branching-rules.json
     */
    private function loadBranchingRules() {
        $rulesFile = dirname(__DIR__) . '/data/branching-rules.json';
        if (file_exists($rulesFile)) {
            $this->branchingRules = json_decode(file_get_contents($rulesFile), true);
        }
    }

    /**
     * Get question definition
     */
    public function getQuestion($questionId) {
        if (!isset($this->questions[$questionId])) {
            return null;
        }

        return $this->questions[$questionId];
    }

    /**
     * Get all questions for a section
     */
    public function getSectionQuestions($sectionNumber) {
        $sectionQuestions = [];

        foreach ($this->questions as $qId => $qData) {
            if (isset($qData['section']) && $qData['section'] == $sectionNumber) {
                $sectionQuestions[$qId] = $qData;
            }
        }

        return $sectionQuestions;
    }

    /**
     * Start assessment for client
     */
    public function startAssessment($clientId) {
        // Create intake_responses record
        $responseId = $this->db->insert('intake_responses', [
            'client_id' => $clientId,
            'assessment_status' => 'in_progress',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $responseId;
    }

    /**
     * Save single response
     */
    public function saveResponse($clientId, $responseId, $questionId, $response) {
        $question = $this->getQuestion($questionId);

        if (!$question) {
            throw new \Exception("Question not found: $questionId");
        }

        // Validate response against allowed options
        if ($question['response_type'] === 'checkbox') {
            // If array, validate each option
            if (is_array($response)) {
                foreach ($response as $opt) {
                    if (!in_array($opt, $question['options'])) {
                        throw new \Exception("Invalid option for $questionId: $opt");
                    }
                }
                $response = json_encode($response);
            }
        } else {
            // For single-select, validate
            if (!in_array($response, $question['options'])) {
                throw new \Exception("Invalid option for $questionId: $response");
            }
        }

        // Map question ID to database column name
        $columnName = strtr($questionId, ['' => '']); // q1 -> q1, etc.

        // Update intake_responses
        $this->db->update(
            'intake_responses',
            [$columnName => $response],
            'response_id = :response_id AND client_id = :client_id',
            [':response_id' => $responseId, ':client_id' => $clientId]
        );

        return true;
    }

    /**
     * Get next question based on branching logic
     */
    public function getNextQuestion($clientId, $currentQuestionId, $responses) {
        // Get current question number
        preg_match('/q(\d+)/', $currentQuestionId, $matches);
        $currentNum = intval($matches[1]);

        // Get next question number
        $nextNum = $currentNum + 1;
        $nextQuestionId = 'q' . $nextNum;

        // Check if next question exists
        if (!isset($this->questions[$nextQuestionId])) {
            return null; // End of assessment
        }

        // Check if question should be skipped based on branching logic
        while ($this->shouldSkipQuestion($nextQuestionId, $responses)) {
            $nextNum++;
            $nextQuestionId = 'q' . $nextNum;

            if (!isset($this->questions[$nextQuestionId])) {
                return null; // End of assessment
            }
        }

        return $this->getQuestion($nextQuestionId);
    }

    /**
     * Check if question should be skipped based on responses
     */
    private function shouldSkipQuestion($questionId, $responses) {
        $question = $this->getQuestion($questionId);

        if (!$question) {
            return false;
        }

        // Check if question has parent_question requirement
        if (isset($question['parent_question'])) {
            $parentAnswer = $responses[$question['parent_question']] ?? null;

            // For example, senior questions only show if q2 == '60 or older (senior)'
            if ($question['parent_question'] === 'q2_senior') {
                return $responses['q2'] !== '60 or older (senior)';
            }
        }

        // Check branching rules for skip logic
        foreach ($this->branchingRules as $ruleName => $rule) {
            if (isset($rule['skip_to_question']) && $rule['skip_to_question'] === $questionId) {
                // This rule determines if we should skip TO this question
                if ($this->evaluateCondition($rule['condition'], $responses)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Evaluate condition against responses
     */
    private function evaluateCondition($condition, $responses) {
        // Simple condition parser for patterns like "q2 == 'value'" or "q36 == 'No'"

        // Handle 'None' conditions
        if ($condition === 'None') {
            return false;
        }

        // Replace question references with actual values
        $eval = $condition;

        foreach ($responses as $qId => $value) {
            $quotedValue = json_encode($value);
            $eval = str_replace($qId, $quotedValue, $eval);
        }

        // Evaluate condition safely (limited eval for security)
        // This is a simplified version; in production, use expression parser

        try {
            return eval("return {$eval};");
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Complete assessment and get responses
     */
    public function completeAssessment($clientId, $responseId) {
        // Mark assessment as complete
        $this->db->update(
            'intake_responses',
            [
                'assessment_status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s')
            ],
            'response_id = :response_id AND client_id = :client_id',
            [':response_id' => $responseId, ':client_id' => $clientId]
        );

        // Get all responses
        $this->db->query(
            "SELECT * FROM intake_responses WHERE response_id = :response_id",
            [':response_id' => $responseId]
        );
        $responses = $this->db->fetch();

        // Mark client profile intake as completed
        $this->db->update(
            'client_profiles',
            ['intake_completed_at' => date('Y-m-d H:i:s')],
            'client_id = :client_id',
            [':client_id' => $clientId]
        );

        $this->logger->log("Assessment completed", [
            'client_id' => $clientId,
            'response_id' => $responseId
        ]);

        return $responses;
    }

    /**
     * Get assessment progress (how many questions answered)
     */
    public function getProgress($responseId) {
        $this->db->query(
            "SELECT * FROM intake_responses WHERE response_id = :response_id",
            [':response_id' => $responseId]
        );
        $responses = $this->db->fetch();

        $answeredCount = 0;

        // Count non-null response fields
        for ($i = 1; $i <= 60; $i++) {
            $colName = 'q' . $i;
            if ($responses[$colName] !== null) {
                $answeredCount++;
            }
        }

        return [
            'total_questions' => 60,
            'answered' => $answeredCount,
            'remaining' => 60 - $answeredCount,
            'progress_percent' => round(($answeredCount / 60) * 100)
        ];
    }

    /**
     * Get all responses for a client
     */
    public function getResponses($clientId) {
        $this->db->query(
            "SELECT * FROM intake_responses WHERE client_id = :client_id ORDER BY completed_at DESC LIMIT 1",
            [':client_id' => $clientId]
        );
        return $this->db->fetch();
    }

    /**
     * Render question as HTML (for frontend)
     */
    public function renderQuestion($questionId, $responses = []) {
        $question = $this->getQuestion($questionId);

        if (!$question) {
            return '';
        }

        $html = '<div class="assessment-question" data-question-id="' . htmlspecialchars($questionId) . '">';
        $html .= '<h3 class="question-title">' . htmlspecialchars($question['question_text']) . '</h3>';

        if (isset($question['help_text'])) {
            $html .= '<p class="help-text">' . htmlspecialchars($question['help_text']) . '</p>';
        }

        switch ($question['response_type']) {
            case 'radio':
                $html .= $this->renderRadioOptions($questionId, $question);
                break;

            case 'checkbox':
                $html .= $this->renderCheckboxOptions($questionId, $question);
                break;

            case 'select':
                $html .= $this->renderSelectOptions($questionId, $question);
                break;

            case 'text':
                $html .= $this->renderTextInput($questionId, $question);
                break;

            case 'textarea':
                $html .= $this->renderTextArea($questionId, $question);
                break;
        }

        $html .= '</div>';

        return $html;
    }

    private function renderRadioOptions($questionId, $question) {
        $html = '<div class="radio-group">';

        foreach ($question['options'] as $option) {
            $id = $questionId . '_' . str_replace(' ', '_', $option);
            $html .= '<label class="radio-label">';
            $html .= '<input type="radio" name="' . htmlspecialchars($questionId) . '" value="' . htmlspecialchars($option) . '">';
            $html .= htmlspecialchars($option);
            $html .= '</label>';
        }

        $html .= '</div>';
        return $html;
    }

    private function renderCheckboxOptions($questionId, $question) {
        $html = '<div class="checkbox-group">';

        foreach ($question['options'] as $option) {
            $id = $questionId . '_' . str_replace(' ', '_', $option);
            $html .= '<label class="checkbox-label">';
            $html .= '<input type="checkbox" name="' . htmlspecialchars($questionId) . '[]" value="' . htmlspecialchars($option) . '">';
            $html .= htmlspecialchars($option);
            $html .= '</label>';
        }

        $html .= '</div>';
        return $html;
    }

    private function renderSelectOptions($questionId, $question) {
        $html = '<select name="' . htmlspecialchars($questionId) . '" class="form-select">';
        $html .= '<option value="">Please select...</option>';

        foreach ($question['options'] as $option) {
            $html .= '<option value="' . htmlspecialchars($option) . '">' . htmlspecialchars($option) . '</option>';
        }

        $html .= '</select>';
        return $html;
    }

    private function renderTextInput($questionId, $question) {
        $placeholder = isset($question['placeholder']) ? htmlspecialchars($question['placeholder']) : '';
        return '<input type="text" name="' . htmlspecialchars($questionId) . '" placeholder="' . $placeholder . '" class="form-input">';
    }

    private function renderTextArea($questionId, $question) {
        $placeholder = isset($question['placeholder']) ? htmlspecialchars($question['placeholder']) : '';
        return '<textarea name="' . htmlspecialchars($questionId) . '" placeholder="' . $placeholder . '" rows="4" class="form-textarea"></textarea>';
    }
}
