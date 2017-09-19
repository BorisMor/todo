<?php

namespace app;

use PDO;

/**
 * Объект для работы с базой
 *
 * Class Db
 * @package app
 */
class Db
{
    private $_active = false;
    private static $_pdo = null;

    private $_config;

    /**
     * Настройки
     * @return mixed
     */
    public function getConfig()
    {
        if (!empty($this->_config)) {
            return $this->_config;
        };

        $this->_config = require_once('config_db.php');

        return $this->_config;
    }

    public function open()
    {
        if (self::$_pdo === null) {
            $config = $this->getConfig();
            self::$_pdo = new PDO(
                $config['connectionString'],
                $config['username'],
                $config['password'],
                array(
                    PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                )
            );

            self::$_pdo->exec('SET NAMES ' . self::$_pdo->quote($this->_config['charset']));
            $this->_active = true;
        }
    }

    public function close()
    {
        self::$_pdo = null;
        $this->_active = false;
    }

    /**
     * Run query with result
     * @param $sql
     * @param array $params
     * @return array
     */
    public function queryParam($sql, $params = array())
    {
        if ($this->_active == false) {
            $this->open();
        }

        $stmt = self::$_pdo->prepare($sql);
        $stmt->execute($params);
        $result = array();
        foreach ($stmt as $row) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Run query with result. Return one row
     * @param $sql
     * @param array $params
     * @return array|null
     */
    public function queryParamOne($sql, $params = array())
    {
        if ($this->_active == false) {
            $this->open();
        }

        $stmt = self::$_pdo->prepare($sql);
        $stmt->execute($params);
        foreach ($stmt as $row) {
            return $row;
        }

        return null;
    }

    /**
     * Run query without data INSERT \ UPDATE
     * @param $sql
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function execute($sql, $params = array())
    {
        $this->setActive(true);
        $stmt = self::$_pdo->prepare($sql);
        $res = $stmt->execute($params);

        if (!$res) {
            $msg = $stmt->errorInfo()[2] ?? 'error db';
            throw new \Exception($msg);
        }

        return $res;
    }

    public function setActive($value)
    {
        if ($value != $this->_active) {
            if ($value) {
                $this->open();
            } else {
                $this->close();
            }
        }
    }

    /**
     * ID of the last inserted record
     * @return mixed
     */
    public function getLastInsertId()
    {
        $this->setActive(true);

        return self::$_pdo->lastInsertId();
    }

    public function transactionStart()
    {
        $this->execute('BEGIN ISOLATION LEVEL SERIALIZABLE');
    }

    public function transactionCommit()
    {
        $this->execute('COMMIT');
    }

    public function transactionRollback()
    {
        $this->execute('ROLLBACK');
    }
}