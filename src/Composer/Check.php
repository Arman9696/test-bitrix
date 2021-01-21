<?php

namespace IQDEV\Composer;

use Composer\Script\Event;

/**
 * Проверка кода проекта по шаблону
 *
 * @package IQDEV\Composer
 *
 * @phan-file-suppress PhanUndeclaredTypeParameter
 * @phan-file-suppress PhanUndeclaredClassMethod
 */
class Check
{
    /**
     * Исключаемые из проверки пути
     *
     * @var array
     */
    protected static $excluded = [
        '.idea/',
        '.git/',
        'docs/',
        'logs/',
        'public/bitrix/',
        'public/upload/',
        'public/adminer.php',
        'src/bootstrap.php',
        'src/Base/Load.php',
        'vendor/',
        '.gitlab-ci.yml',
        'readme.md'
    ];

    /**
     * Ищем конфликты git
     *
     * @param Event $event
     *
     * @return bool
     */
    public static function conflicts(Event $event)
    {
        $rows = static::grepPatterns([
            '<<<<<\<< HEAD',
            '^====\===$',
            '>>>>\>>>',
        ]);
        if ($rows) {
            $event->getIO()->writeError(implode(PHP_EOL, $rows));
            return false;
        }
        return true;
    }

    /**
     * Ищем TODO-шки
     *
     * @param Event $event
     *
     * @return bool
     */
    public static function todo(Event $event)
    {
        $rows = static::grepPatterns([
            '@T\ODO',
        ], true);
        if ($rows) {
            $event->getIO()->writeError(implode(PHP_EOL, $rows));
            return false;
        }
        return true;
    }

    /**
     * Ищем вызовы отладочных методов
     *
     * @param Event $event
     *
     * @return bool
     */
    public static function debug(Event $event)
    {
        $rows = static::grepPatterns([
            'dum\p(',
            'prin\t_r(',
        ], true);
        if ($rows) {
            $event->getIO()->writeError(implode(PHP_EOL, $rows));
            return false;
        }
        return true;
    }

    /**
     * Вырезаем исключаемые пути
     *
     * @param array $rows
     *
     * @return array
     */
    protected static function sanitizeExcluded(array $rows): array
    {
        $excluded = array_map(function ($v) {
            return realpath(__DIR__ . '/../../') . '/' . $v;
        }, static::$excluded);
        if ($excluded) {
            foreach ($rows as $k => $row) {
                $row = str_replace($excluded, '#EXCLUDED#', $row);
                if (is_numeric(strpos($row, '#EXCLUDED#')) || is_numeric(strpos($row, '.phar')) || empty($row)) {
                    unset($rows[$k]);
                }
            }
        }
        return $rows;
    }

    /**
     * Поиск шаблона в содержимом проекта
     *
     * @param array $patterns
     * @param bool $caseInsensitive
     *
     * @return array
     */
    protected static function grepPatterns(array $patterns, $caseInsensitive = false): array
    {
        $rows = [];
        foreach ($patterns as $pattern) {
            $command = 'grep -rn'.($caseInsensitive ? 'i' : '').' "'.$pattern.'" '. realpath(__DIR__ . '/../../');

            $rows += static::sanitizeExcluded(explode(PHP_EOL, (string) shell_exec($command)));
        }
        return $rows;
    }
}
