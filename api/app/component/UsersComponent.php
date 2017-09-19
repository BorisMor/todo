<?php

namespace app\component;

use app\AppTodo;
use app\BaseObject;
use app\models\Users;

class UsersComponent extends BaseObject
{
    /**
     * Обновит токен и вернет строку нового token
     *
     * @param $login
     * @param $password
     * @return Users
     * @throws \Exception
     */
    public static function login($login, $password)
    {
        /** @var Users $user */
        $user = Users::findByLogin($login);
        if (empty($user)) {
            throw new \Exception('Отказанно в доступе');
        }

        if ($user->password !== $password) {
            throw new \Exception('Отказанно в доступе');
        }


        $user->updateToken();
        return $user->token;
    }

    /**
     * Обработка событий
     * @param AppTodo $app
     * @throws \Exception
     */
    public static function actionLogin(AppTodo $app)
    {
        $params     = $app->getBodyJson();
        $login      = $params['login']??null;
        $password   = $params['password']??null;

        if (empty($login)) {
            throw new \Exception('Логин не указан');
        }

        if (empty($password)) {
            throw new \Exception('Пароль не указан');
        }

        $token = UsersComponent::login($login, $password);
        $app->answer($token);
    }

    /**
     * Разлогинется
     * @param $token
     */
    public static function logout($token)
    {
        /** @var Users $user */
        $user = Users::findByToken($token);
        if (empty($user)) {
            return;
        }

        $user->token = null;
        $user->save();
    }

    public static function actionLogout(AppTodo $app)
    {
        static::logout($app->token);
        $app->answer(true);
    }
}