<?php

namespace app;

/**
 * Trait traitDb
 * Что бы до базы был доступ из любого объекта
 *
 * @package app
 */
trait traitDb
{
    /**
     * Доступ до базы
     * @return Db
     */
    public function getDb()
    {
        return Helper::getDb();
    }
}

