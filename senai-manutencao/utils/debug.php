<?php

/**
 * Utilitário simples de debug/log
 */
class Debug
{
    private const LOG_DIR = __DIR__ . '/../storage/logs';
    private const LOG_FILE = self::LOG_DIR . '/debug.log';

    /**
     * Registra mensagens no arquivo de debug
     * @param string $message
     * @param array $context
     */
    public static function log(string $message, array $context = []): void
    {
        try {
            if (!is_dir(self::LOG_DIR)) {
                mkdir(self::LOG_DIR, 0755, true);
            }

            $entry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'message' => $message,
            ];

            if (!empty($context)) {
                $entry['context'] = self::sanitizeContext($context);
            }

            $line = json_encode($entry, JSON_UNESCAPED_UNICODE) ?: ($message . ' ' . print_r($context, true));
            file_put_contents(self::LOG_FILE, $line . PHP_EOL, FILE_APPEND);
        } catch (Throwable $e) {
            error_log('Debug::log failure: ' . $e->getMessage());
        }
    }

    private static function sanitizeContext(array $context): array
    {
        return array_map(function ($value) {
            if (is_array($value) || is_object($value)) {
                return json_decode(json_encode($value, JSON_UNESCAPED_UNICODE), true);
            }
            return $value;
        }, $context);
    }
}
