<?php
namespace App\Core;

/**
 * Audit and application logger
 * Handles different log types: general app logs, audit logs, error logs
 */
class Logger {
    const LOG_APP = 'app.log';
    const LOG_AUDIT = 'audit.log';
    const LOG_ERROR = 'errors.log';

    private $logPath = '/workspaces/outsinc/app/logs/';

    public function __construct() {
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }

    /**
     * Log general application event
     */
    public function log($message, $context = []) {
        $this->write(self::LOG_APP, $message, $context);
    }

    /**
     * Log audit event (Four-Filter compliance)
     * Important: Log when staff view identifiable client data, share data, etc.
     */
    public function audit($actionType, $userId, $clientId = null, $description = null, $filterLevel = null) {
        $message = "[{$actionType}] User: {$userId}, Client: " . ($clientId ?? 'N/A');
        if ($filterLevel) {
            $message .= ", Filter Level: {$filterLevel}";
        }
        if ($description) {
            $message .= " - {$description}";
        }

        $this->write(self::LOG_AUDIT, $message, [
            'action' => $actionType,
            'user_id' => $userId,
            'client_id' => $clientId,
            'filter_level' => $filterLevel,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'CLI'
        ]);
    }

    /**
     * Log error
     */
    public function error($message, $exception = null) {
        $context = [];
        if ($exception) {
            $context = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];
        }
        $this->write(self::LOG_ERROR, $message, $context);
    }

    /**
     * Write to log file
     */
    private function write($logFile, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' | ' . json_encode($context) : '';
        $logEntry = "[{$timestamp}] {$message}{$contextStr}\n";

        $file = $this->logPath . $logFile;
        file_put_contents($file, $logEntry, FILE_APPEND | LOCK_EX);
    }
}
