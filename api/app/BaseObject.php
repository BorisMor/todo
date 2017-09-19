<?php

namespace app;

/**
 * Class BaseObject
 * @package app
 * @property Db $db
 * @property $attributes
 */
abstract class BaseObject {
    use traitDb;

    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        }
    }

    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
    }

    public function __isset($name)
    {
        $getter = 'get' . $name;
        return method_exists($this, $getter);
    }
}