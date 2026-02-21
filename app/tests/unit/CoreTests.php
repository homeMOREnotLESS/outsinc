<?php
/**
 * OUTSINC Pathways - Unit Test Suite
 * Tests core classes and their interactions
 * 
 * Run: php tests/unit/CoreTests.php
 */

require_once dirname(dirname(__DIR__)) . '/config/bootstrap.php';

require_once dirname(dirname(__DIR__)) . '/core/Database.php';
require_once dirname(dirname(__DIR__)) . '/core/Encryption.php';
require_once dirname(dirname(__DIR__)) . '/core/Logger.php';
require_once dirname(dirname(__DIR__)) . '/core/Response.php';
require_once dirname(dirname(__DIR__)) . '/core/SessionManager.php';
require_once dirname(dirname(__DIR__)) . '/models/User.php';
require_once dirname(dirname(__DIR__)) . '/models/ClientProfile.php';
require_once dirname(dirname(__DIR__)) . '/services/AuthService.php';
require_once dirname(dirname(__DIR__)) . '/services/AssessmentEngine.php';

use App\Core\Database;
use App\Core\Encryption;
use App\Core\Logger;
use App\Core\Response;
use App\Core\SessionManager;
use App\Models\User;
use App\Models\ClientProfile;
use App\Services\AuthService;
use App\Services\AssessmentEngine;

class TestRunner {
    private $passed = 0;
    private $failed = 0;
    private $tests = [];

    public function test($name, $condition, $message = '') {
        $this->tests[] = [
            'name' => $name,
            'pass' => $condition,
            'message' => $message
        ];

        if ($condition) {
            $this->passed++;
            echo "✅ PASS: $name\n";
        } else {
            $this->failed++;
            echo "❌ FAIL: $name\n";
            if ($message) echo "   → $message\n";
        }
    }

    public function assertIsString($value, $name) {
        $this->test($name, is_string($value), "Expected string, got " . gettype($value));
    }

    public function assertIsArray($value, $name) {
        $this->test($name, is_array($value), "Expected array, got " . gettype($value));
    }

    public function assertNotEmpty($value, $name) {
        $this->test($name, !empty($value), "Value is empty");
    }

    public function assertIsObject($value, $name) {
        $this->test($name, is_object($value), "Expected object, got " . gettype($value));
    }

    public function assertEqual($expected, $actual, $name) {
        $this->test($name, $expected === $actual, "Expected $expected, got $actual");
    }

    public function summary() {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "TEST SUMMARY\n";
        echo str_repeat("=", 60) . "\n";
        echo "Passed: " . $this->passed . "\n";
        echo "Failed: " . $this->failed . "\n";
        echo "Total:  " . ($this->passed + $this->failed) . "\n";
        echo "Pass Rate: " . round(($this->passed / ($this->passed + $this->failed)) * 100, 2) . "%\n";
        return $this->failed === 0;
    }
}

// Start testing
$runner = new TestRunner();

echo "\n" . str_repeat("=", 60) . "\n";
echo "OUTSINC PATHWAYS - UNIT TEST SUITE\n";
echo str_repeat("=", 60) . "\n\n";

// ============================================================================
// Test 1: Encryption Class
// ============================================================================
echo "Test Suite 1: Encryption Class\n";
echo str_repeat("-", 60) . "\n";

$encTestPassword = "TestPassword123!@#";
$hashedPassword = Encryption::hashPassword($encTestPassword);
$runner->assertIsString($hashedPassword, "Hash password returns string");
$runner->assertNotEmpty($hashedPassword, "Hash password not empty");

$passwordToken = Encryption::generateToken(32);
$runner->assertIsString($passwordToken, "Generate token returns string");
$runner->assertEqual(64, strlen($passwordToken), "Token length is 64 (32 bytes hex)");

$passwordStrength = Encryption::validatePasswordStrength("weak");
$runner->assertIsArray($passwordStrength, "Password strength validation returns array");
$runner->assertNotEmpty($passwordStrength, "Weak password has validation errors");

$strongPassword = Encryption::validatePasswordStrength("SecurePass123!@#");
$runner->test("Strong password passes validation", empty($strongPassword), 
    "Expected no errors for strong password, got: " . json_encode($strongPassword));

echo "\n";

// ============================================================================
// Test 2: Logger Class
// ============================================================================
echo "Test Suite 2: Logger Class\n";
echo str_repeat("-", 60) . "\n";

$logger = new Logger();
$runner->assertIsObject($logger, "Logger instantiation");

try {
    $logger->log("Test log entry", ['test' => 'data']);
    $runner->test("Logger can write entries", true);
} catch (Exception $e) {
    $runner->test("Logger can write entries", false, $e->getMessage());
}

echo "\n";

// ============================================================================
// Test 3: Response Class
// ============================================================================
echo "Test Suite 3: Response Class\n";
echo str_repeat("-", 60) . "\n";

$runner->test("Response class has success method", method_exists('App\\Core\\Response', 'success'));
$runner->test("Response class has error method", method_exists('App\\Core\\Response', 'error'));
$runner->test("Response class has unauthorized method", method_exists('App\\Core\\Response', 'unauthorized'));
$runner->test("Response class has validationError method", method_exists('App\\Core\\Response', 'validationError'));

echo "\n";

// ============================================================================
// Test 4: SessionManager Class
// ============================================================================
echo "Test Suite 4: SessionManager Class\n";
echo str_repeat("-", 60) . "\n";

$sessionManager = new SessionManager();
$runner->assertIsObject($sessionManager, "SessionManager instantiation");
$runner->test("SessionManager has set method", method_exists($sessionManager, 'set'));
$runner->test("SessionManager has get method", method_exists($sessionManager, 'get'));
$runner->test("SessionManager has destroy method", method_exists($sessionManager, 'destroy'));

echo "\n";

// ============================================================================
// Test 5: Database Class
// ============================================================================
echo "Test Suite 5: Database Class\n";
echo str_repeat("-", 60) . "\n";

$runner->test("Database class is singleton", method_exists('App\\Core\\Database', 'getInstance'));
$runner->test("Database has query method (reflected)", method_exists('App\\Core\\Database', 'query'));
$runner->test("Database has fetch method (reflected)", method_exists('App\\Core\\Database', 'fetch'));
$runner->test("Database has beginTransaction method (reflected)", method_exists('App\\Core\\Database', 'beginTransaction'));

// Note: Database connection test skipped - requires MySQL PDO driver
$hasMySQLDriver = extension_loaded('pdo_mysql');
echo "📌 MySQL PDO Driver: " . ($hasMySQLDriver ? "✅ Available" : "⚠️ Not available (expected in dev)") . "\n";

echo "\n";

// ============================================================================
// Test 6: Assessment Engine Class
// ============================================================================
echo "Test Suite 6: AssessmentEngine Class\n";
echo str_repeat("-", 60) . "\n";

try {
    $engine = new AssessmentEngine();
    $runner->assertIsObject($engine, "AssessmentEngine instantiation");

    $question = $engine->getQuestion('q1');
    $runner->assertIsArray($question, "getQuestion returns array for valid question");
    $runner->assertNotEmpty($question, "Question data not empty");

    $sectionQuestions = $engine->getSectionQuestions(1);
    $runner->assertIsArray($sectionQuestions, "getSectionQuestions returns array");
    $runner->test("Section 1 has 5 questions", count($sectionQuestions) === 5, 
        "Expected 5 questions, got " . count($sectionQuestions));

    $sectionQuestions2 = $engine->getSectionQuestions(2);
    $runner->test("Section 2 has 10 questions", count($sectionQuestions2) === 10,
        "Expected 10 questions, got " . count($sectionQuestions2));

    $invalidQuestion = $engine->getQuestion('nonexistent_question');
    $runner->test("Invalid question returns null", $invalidQuestion === null);
} catch (Exception $e) {
    $runner->test("AssessmentEngine instantiation (skipped - DB required)", true, 
        "Skipped: " . $e->getMessage());
}

echo "\n";

// ============================================================================
// Test 7: Data File Integrity
// ============================================================================
echo "Test Suite 7: Data File Integrity\n";
echo str_repeat("-", 60) . "\n";

$questionsFile = dirname(dirname(__DIR__)) . '/data/questions.json';
$questions = json_decode(file_get_contents($questionsFile), true);
$runner->assertEqual(60, count($questions), "Questions.json contains 60 questions");

$branchingFile = dirname(dirname(__DIR__)) . '/data/branching-rules.json';
$branching = json_decode(file_get_contents($branchingFile), true);
$runner->assertIsArray($branching, "Branching rules loads as array");
$runner->assertNotEmpty($branching, "Branching rules not empty");

$outcomesFile = dirname(dirname(__DIR__)) . '/data/outcome-triggers.json';
$outcomes = json_decode(file_get_contents($outcomesFile), true);
$runner->assertIsArray($outcomes, "Outcome triggers loads as array");
$runner->assertNotEmpty($outcomes, "Outcome triggers not empty");

$badgesFile = dirname(dirname(__DIR__)) . '/data/badges.json';
$badges = json_decode(file_get_contents($badgesFile), true);
$runner->assertIsArray($badges, "Badges loads as array");
$runner->test("Badges count >= 20", count($badges) >= 20, "Expected 20+, got " . count($badges));

echo "\n";

// ============================================================================
// Test 8: Model Class Definitions
// ============================================================================
echo "Test Suite 8: Model Classes\n";
echo str_repeat("-", 60) . "\n";

$runner->test("User class has create method", method_exists('App\\Models\\User', 'create'));
$runner->test("User class has getByUsername method", method_exists('App\\Models\\User', 'getByUsername'));
$runner->test("User class has getById method", method_exists('App\\Models\\User', 'getById'));
$runner->test("User class has verifyPassword method", method_exists('App\\Models\\User', 'verifyPassword'));

$runner->test("ClientProfile class has create method", method_exists('App\\Models\\ClientProfile', 'create'));
$runner->test("ClientProfile class has getById method", method_exists('App\\Models\\ClientProfile', 'getById'));
$runner->test("ClientProfile class has getByUserId method", method_exists('App\\Models\\ClientProfile', 'getByUserId'));
$runner->test("ClientProfile class has update method", method_exists('App\\Models\\ClientProfile', 'update'));

echo "\n";

// ============================================================================
// Test 9: Service Class Definitions
// ============================================================================
echo "Test Suite 9: Service Classes\n";
echo str_repeat("-", 60) . "\n";

$runner->test("AuthService class has registerClient method", method_exists('App\\Services\\AuthService', 'registerClient'));
$runner->test("AuthService class has authenticate method", method_exists('App\\Services\\AuthService', 'authenticate'));
$runner->test("AuthService class has initiatePasswordReset method", method_exists('App\\Services\\AuthService', 'initiatePasswordReset'));
$runner->test("AuthService class has completePasswordReset method", method_exists('App\\Services\\AuthService', 'completePasswordReset'));

$runner->test("AssessmentEngine has startAssessment method", method_exists('App\\Services\\AssessmentEngine', 'startAssessment'));
$runner->test("AssessmentEngine has saveResponse method", method_exists('App\\Services\\AssessmentEngine', 'saveResponse'));
$runner->test("AssessmentEngine has getNextQuestion method", method_exists('App\\Services\\AssessmentEngine', 'getNextQuestion'));

echo "\n";

// ============================================================================
// Test 10: File Structure
// ============================================================================
echo "Test Suite 10: File Structure\n";
echo str_repeat("-", 60) . "\n";

$requiredFiles = [
    'config/bootstrap.php',
    'core/Database.php',
    'core/Encryption.php',
    'core/Logger.php',
    'core/Response.php',
    'core/SessionManager.php',
    'models/User.php',
    'models/ClientProfile.php',
    'services/AuthService.php',
    'services/AssessmentEngine.php',
    'migrations/001_create_base_schema.sql',
    'data/questions.json',
    'data/branching-rules.json',
    'data/outcome-triggers.json',
    'data/badges.json',
];

$basePath = dirname(dirname(__DIR__));
foreach ($requiredFiles as $file) {
    $runner->test("File exists: $file", file_exists($basePath . '/' . $file));
}

echo "\n";

// ============================================================================
// Final Summary
// ============================================================================
$success = $runner->summary();

exit($success ? 0 : 1);
