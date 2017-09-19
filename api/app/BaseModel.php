<?php
/**
 * Базовая модель
 */

namespace app;

/**
 * Class BaseModel
 * @package app
 * @property $pk Значение первичного ключа
 */
abstract class BaseModel extends BaseObject
{

    /** @var null|string Поле отвечающее за первичнй ключ */
    protected static $pk = null;

    /**
     * Добавить запись
     */
    abstract function insert();

    /**
     * Обвноить запись
     */
    abstract function update();

    /**
     * знчаения которые будут доступны через $attributes
     * @var array
     */
    protected static $_attributes = [];

    /**
     * Установить атрибуты
     * @param array $attributes
     */
    public function setAttributes(array $attributes)
    {
        foreach ($attributes as $attr => $value) {
            if (in_array($attr, static::$_attributes)) {
                $this->$attr = $value;
            }
        }
    }

    /**
     * Получить атрибуты
     * @return array
     */
    public function getAttributes()
    {
        $result = [];
        foreach (static::$_attributes as $attr) {
            $result[$attr] = $this->$attr;
        }

        return $result;
    }

    /**
     * Создать модель с атрибутами
     * Если атрибутов нет то вернет null
     *
     * @param $attributes
     * @return static|null;
     */
    public static function createObject($attributes)
    {
        if (empty($attributes)) {
            return null;
        }

        $model = new static();
        $model->attributes = $attributes;

        return $model;
    }

    /**
     * Значение PK
     * @return mixed
     * @throws \Exception
     */
    public function getPK()
    {
        if (empty(static::$pk)) {
            throw new \Exception('Надо установить первичный ключ');
        }

        return $this->{static::$pk};
    }

    public function save()
    {
        if (empty($this->pk)) {
            $this->insert();
        } else {
            $this->update();
        }
    }
}