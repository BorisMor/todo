<?php
namespace app\models;

use app\BaseModel;
use app\Helper;

/**
 * Модель для таблицы users
 * Class Users
 * @package app\models
 */
class Users extends BaseModel
{
    protected static $pk = 'id';

    protected static $_attributes = [
        'id', 'login', 'password', 'token'
    ];

    public $id;
    public $login;
    public $password;
    public $token;

    /**
     * Поиск пользователя по логину
     * @param $login
     * @return null|
     */
    public static function findByLogin($login)
    {
        $sql = 'SELECT * FROM users WHERE login = :login';
        $result = Helper::getDb()->queryParamOne($sql, [':login' => $login]);
        return static::createObject($result);
    }

    /**
     * Поиск пользователя по токену
     * @param $token
     * @return Users
     */
    public static function findByToken($token)
    {
        $sql = 'SELECT * FROM users WHERE token = :token';
        $result = Helper::getDb()->queryParamOne($sql, [':token' => $token]);
        return static::createObject($result);
    }

    /**
     * Обнвоить запись
     */
    public function update()
    {
        $sql = '
          UPDATE users SET 
              login = :login, 
              password = :password, 
              token = :token 
          WHERE id = :id;        
        ';

        $this->db->execute($sql, [
            ':login' => $this->login,
            ':password' => $this->password,
            ':token' => $this->token,
            ':id' => $this->id
        ]);
    }

    /**
     * Вставить
     */
    public function insert()
    {
        $sql = '
          INSERT INTO users(login, password, token) 
          VALUES(:login, :password, :token);
        ';

        $this->db->execute($sql, [
            ':login' => $this->login,
            ':password' => $this->password,
            ':token' => $this->token
        ]);

        $this->id = $this->db->getLastInsertId();
    }

    /**
     * Обновляем токен
     */
    public function updateToken()
    {
        $key = $this->login . Helper::isDate('now')->format('Ymdhis') . rand(1,2);
        $this->token = md5($key);
        $this->save();
    }
}