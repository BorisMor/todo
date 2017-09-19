<?php
/**
 * Вспомогательные функции
 */

namespace app;

use phpDocumentor\Reflection\DocBlock\Tags\Param;

class Helper
{
    private static $_db;

    public static function isDate($value)
    {
        if (is_object($value) && $value instanceof \DateTime) {
            return $value;
        }

        if ($value === 'now') {
            return new \DateTime();
        }

        $result = \DateTime::createFromFormat('Y-m-d H:i:s', $value);

        if ($result == false) {
            $result = \DateTime::createFromFormat('Y-m-d', $value);
        }

        if ($result == false) {
            $result = \DateTime::createFromFormat('d.m.Y H:i:s', $value);
        }

        if ($result == false) {
            $result = \DateTime::createFromFormat('d.m.Y', $value);
        }

        if ($result == false) {
            throw new Exception('Не смогли преобразовать в дату: ' . $value);
        }

        return $result;
    }

    /**
     * Доступ до базы
     * @return Db
     */
    public static function getDb()
    {
        if (!empty(static::$_db)) {
            return static::$_db;
        }

        static::$_db = new Db;

        return static::$_db;
    }
}